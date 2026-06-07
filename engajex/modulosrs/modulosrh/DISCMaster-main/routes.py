import os
import json
import tempfile
from flask import render_template, request, jsonify, send_file, flash
from werkzeug.utils import secure_filename
import pandas as pd
import numpy as np
from reportlab.lib.pagesizes import letter, A4
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib import colors
from reportlab.lib.units import inch
from io import BytesIO
from app import app
from disc_calculator import DISCCalculator
from disc_ai_analyzer import DISCAIAnalyzer

ALLOWED_EXTENSIONS = {'xlsx', 'xls'}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/upload', methods=['POST'])
def upload_file():
    try:
        if 'file' not in request.files:
            return jsonify({'error': 'Nenhum arquivo foi enviado'}), 400
        
        file = request.files['file']
        if file.filename == '':
            return jsonify({'error': 'Nenhum arquivo foi selecionado'}), 400
        
        if not allowed_file(file.filename):
            return jsonify({'error': 'Formato de arquivo não suportado. Use .xlsx ou .xls'}), 400
        
        # Save uploaded file temporarily
        if not file.filename:
            return jsonify({'error': 'Nome do arquivo inválido'}), 400
        filename = secure_filename(file.filename)
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(filepath)
        
        try:
            # Process the Excel file
            calculator = DISCCalculator()
            results = calculator.process_excel_file(filepath)
            
            # Generate behavioral analysis for each individual
            ai_analyzer = DISCAIAnalyzer()
            
            for person in results['individuals']:
                # Always use robust fallback analysis that doesn't depend on external API
                person['ai_analysis'] = ai_analyzer._create_fallback_analysis(person)
            
            # Generate team dynamics analysis using offline method
            results['team_analysis'] = ai_analyzer._create_team_analysis_offline(results['individuals'])
            
            # Clean up the uploaded file
            os.remove(filepath)
            
            return jsonify({
                'success': True,
                'message': f'Arquivo processado com sucesso! {len(results["individuals"])} pessoas analisadas com insights comportamentais.',
                'data': results
            })
            
        except Exception as e:
            # Clean up the uploaded file on error
            if os.path.exists(filepath):
                os.remove(filepath)
            raise e
            
    except Exception as e:
        app.logger.error(f"Error processing file: {str(e)}")
        return jsonify({'error': f'Erro ao processar arquivo: {str(e)}'}), 500

@app.route('/export/excel', methods=['POST'])
def export_excel():
    try:
        data = request.get_json()
        if not data:
            return jsonify({'error': 'Dados não fornecidos'}), 400
        
        return _create_excel_export(data)
        
    except Exception as e:
        app.logger.error(f"Error exporting Excel: {str(e)}")
        return jsonify({'error': f'Erro ao exportar Excel: {str(e)}'}), 500

@app.route('/export/detailed', methods=['POST'])
def export_detailed():
    """Export with detailed analysis and charts"""
    try:
        data = request.get_json()
        if not data:
            return jsonify({'error': 'Dados não fornecidos'}), 400
        
        format_type = data.get('format', 'excel')  # excel, pdf, or csv
        
        if format_type == 'excel':
            return _create_detailed_excel_export(data)
        elif format_type == 'pdf':
            return _create_detailed_pdf_export(data)
        elif format_type == 'csv':
            return _create_csv_export(data)
        else:
            return jsonify({'error': 'Formato não suportado'}), 400
            
    except Exception as e:
        app.logger.error(f"Error exporting detailed report: {str(e)}")
        return jsonify({'error': f'Erro ao exportar relatório: {str(e)}'}), 500

def _create_excel_export(data):
    """Create basic Excel export"""
    # Create temporary file for Excel export
    with tempfile.NamedTemporaryFile(delete=False, suffix='.xlsx') as tmp_file:
        # Create DataFrames
        individuals_data = []
        for person in data['individuals']:
            row = {
                'ID': person['id'],
                'Nome': person['name'],
                'D (%)': f"{person['scores']['D']:.1f}%",
                'I (%)': f"{person['scores']['I']:.1f}%",
                'S (%)': f"{person['scores']['S']:.1f}%",
                'C (%)': f"{person['scores']['C']:.1f}%",
                'Perfil Dominante': person['dominant_profile']
            }
            
            # Add AI analysis data if available
            if person.get('ai_analysis'):
                ai = person['ai_analysis']
                row.update({
                    'Resumo IA': ai.get('resumo_executivo', ''),
                    'Estilo Comunicação': ai.get('estilo_comunicacao', ''),
                    'Ambiente Ideal': ai.get('ambiente_trabalho_ideal', ''),
                    'Dicas Gestão': ai.get('dicas_gestao', '')
                })
            
            individuals_data.append(row)
        
        df_individuals = pd.DataFrame(individuals_data)
        
        # Statistics data
        stats_data = []
        for stat in data['statistics']:
            stats_data.append({
                'Estatística': stat['label'],
                'Valor': stat['value']
            })
        
        df_stats = pd.DataFrame(stats_data)
        
        # Write to Excel
        with pd.ExcelWriter(tmp_file.name, engine='openpyxl') as writer:
            df_individuals.to_excel(writer, sheet_name='Resultados Individuais', index=False)
            df_stats.to_excel(writer, sheet_name='Estatísticas', index=False)
            
            # Auto-adjust column widths
            for sheet_name in writer.sheets:
                worksheet = writer.sheets[sheet_name]
                for column in worksheet.columns:
                    max_length = 0
                    column_letter = column[0].column_letter
                    for cell in column:
                        try:
                            if len(str(cell.value)) > max_length:
                                max_length = len(str(cell.value))
                        except:
                            pass
                    adjusted_width = min(max_length + 2, 50)
                    worksheet.column_dimensions[column_letter].width = adjusted_width
        
        # Send file and clean up
        try:
            return send_file(
                tmp_file.name,
                mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                as_attachment=True,
                download_name='resultados_disc.xlsx'
            )
        finally:
            # Clean up temporary file
            try:
                os.unlink(tmp_file.name)
            except:
                pass

def _create_detailed_excel_export(data):
    """Create detailed Excel export with analysis"""
    # Create temporary file
    with tempfile.NamedTemporaryFile(delete=False, suffix='.xlsx') as tmp_file:
        with pd.ExcelWriter(tmp_file.name, engine='openpyxl') as writer:
            # Individual results sheet
            individuals_data = []
            for person in data['individuals']:
                row = {
                    'ID': person['id'],
                    'Nome': person['name'],
                    'D_Score': person['scores']['D'],
                    'I_Score': person['scores']['I'],
                    'S_Score': person['scores']['S'],
                    'C_Score': person['scores']['C'],
                    'D (%)': f"{person['scores']['D']:.1f}%",
                    'I (%)': f"{person['scores']['I']:.1f}%",
                    'S (%)': f"{person['scores']['S']:.1f}%",
                    'C (%)': f"{person['scores']['C']:.1f}%",
                    'Perfil Dominante': person['dominant_profile'],
                    'Score Dominante': max(person['scores'].values()),
                    'Diferença P1-P2': max(person['scores'].values()) - sorted(person['scores'].values())[-2]
                }
                
                # Add AI analysis data if available
                if person.get('ai_analysis'):
                    ai = person['ai_analysis']
                    row.update({
                        'Resumo IA': ai.get('resumo_executivo', ''),
                        'Estilo Comunicação': ai.get('estilo_comunicacao', ''),
                        'Ambiente Ideal': ai.get('ambiente_trabalho_ideal', ''),
                        'Dicas Gestão': ai.get('dicas_gestao', ''),
                        'Pontos Fortes': '; '.join(ai.get('pontos_fortes', [])),
                        'Oportunidades Melhoria': '; '.join(ai.get('oportunidades_melhoria', [])),
                        'Áreas Desenvolvimento': '; '.join(ai.get('areas_desenvolvimento', []))
                    })
                
                individuals_data.append(row)
            
            df_individuals = pd.DataFrame(individuals_data)
            df_individuals.to_excel(writer, sheet_name='Resultados Detalhados', index=False)
            
            # Statistics sheet
            stats_data = []
            for stat in data['statistics']:
                stats_data.append({
                    'Estatística': stat['label'],
                    'Valor': stat['value']
                })
            
            df_stats = pd.DataFrame(stats_data)
            df_stats.to_excel(writer, sheet_name='Estatísticas', index=False)
            
            # Analysis sheet with averages by profile
            profile_analysis = []
            profiles = ['D', 'I', 'S', 'C']
            for profile in profiles:
                scores = [person['scores'][profile] for person in data['individuals']]
                analysis_row = {
                    'Perfil': profile,
                    'Média': np.mean(scores),
                    'Mediana': np.median(scores),
                    'Desvio Padrão': np.std(scores),
                    'Valor Mínimo': min(scores),
                    'Valor Máximo': max(scores),
                    'Pessoas com Perfil Dominante': len([p for p in data['individuals'] if p['dominant_profile'] == profile])
                }
                profile_analysis.append(analysis_row)
            
            df_analysis = pd.DataFrame(profile_analysis)
            df_analysis.to_excel(writer, sheet_name='Análise por Perfil', index=False)
            
            # Auto-adjust column widths for all sheets
            for sheet_name in writer.sheets:
                worksheet = writer.sheets[sheet_name]
                for column in worksheet.columns:
                    max_length = 0
                    column_letter = column[0].column_letter
                    for cell in column:
                        try:
                            if len(str(cell.value)) > max_length:
                                max_length = len(str(cell.value))
                        except:
                            pass
                    adjusted_width = min(max_length + 2, 50)
                    worksheet.column_dimensions[column_letter].width = adjusted_width
        
        try:
            return send_file(
                tmp_file.name,
                mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                as_attachment=True,
                download_name='resultados_disc_detalhado.xlsx'
            )
        finally:
            try:
                os.unlink(tmp_file.name)
            except:
                pass

def _create_csv_export(data):
    """Create CSV export"""
    output = BytesIO()
    
    # Create CSV data
    individuals_data = []
    for person in data['individuals']:
        row = {
            'ID': person['id'],
            'Nome': person['name'],
            'D_Percentual': person['scores']['D'],
            'I_Percentual': person['scores']['I'],
            'S_Percentual': person['scores']['S'],
            'C_Percentual': person['scores']['C'],
            'Perfil_Dominante': person['dominant_profile']
        }
        individuals_data.append(row)
    
    df = pd.DataFrame(individuals_data)
    csv_content = df.to_csv(index=False, encoding='utf-8-sig')  # UTF-8 with BOM for Excel compatibility
    output.write(csv_content.encode('utf-8-sig'))
    output.seek(0)
    
    return send_file(
        output,
        mimetype='text/csv',
        as_attachment=True,
        download_name='resultados_disc.csv'
    )

def _create_detailed_pdf_export(data):
    """Create detailed PDF export with enhanced analysis"""
    output = BytesIO()
    doc = SimpleDocTemplate(output, pagesize=A4, rightMargin=72, leftMargin=72, topMargin=72, bottomMargin=18)
    
    elements = []
    styles = getSampleStyleSheet()
    
    # Enhanced title
    title_style = ParagraphStyle(
        'CustomTitle',
        parent=styles['Heading1'],
        fontSize=24,
        spaceAfter=30,
        alignment=1  # Center alignment
    )
    elements.append(Paragraph("📊 Relatório DISC Detalhado", title_style))
    elements.append(Spacer(1, 20))
    
    # Summary section
    total_people = len(data['individuals'])
    elements.append(Paragraph(f"<b>Resumo Executivo:</b> Análise de {total_people} pessoas", styles['Heading2']))
    elements.append(Spacer(1, 12))
    
    # Statistics section
    elements.append(Paragraph("📈 Estatísticas Gerais", styles['Heading2']))
    elements.append(Spacer(1, 12))
    
    stats_data = [['Estatística', 'Valor']]
    for stat in data['statistics']:
        stats_data.append([stat['label'], str(stat['value'])])
    
    stats_table = Table(stats_data)
    stats_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
        ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
        ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
        ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
        ('FONTSIZE', (0, 0), (-1, 0), 14),
        ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
        ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
        ('GRID', (0, 0), (-1, -1), 1, colors.black)
    ]))
    
    elements.append(stats_table)
    elements.append(Spacer(1, 30))
    
    # Individual results section
    elements.append(Paragraph("👥 Resultados Individuais", styles['Heading2']))
    elements.append(Spacer(1, 12))
    
    # Create table data with more details
    table_data = [['Nome', 'D (%)', 'I (%)', 'S (%)', 'C (%)', 'Perfil Dominante', 'Score Dominante']]
    
    for person in data['individuals']:
        dominant_score = max(person['scores'].values())
        row = [
            person['name'],
            f"{person['scores']['D']:.1f}%",
            f"{person['scores']['I']:.1f}%",
            f"{person['scores']['S']:.1f}%",
            f"{person['scores']['C']:.1f}%",
            person['dominant_profile'],
            f"{dominant_score:.1f}%"
        ]
        table_data.append(row)
    
    # Create table
    table = Table(table_data)
    table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
        ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
        ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
        ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
        ('FONTSIZE', (0, 0), (-1, 0), 10),
        ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
        ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
        ('GRID', (0, 0), (-1, -1), 1, colors.black),
        ('FONTSIZE', (0, 1), (-1, -1), 8),
    ]))
    
    elements.append(table)
    
    # Build PDF
    doc.build(elements)
    output.seek(0)
    
    return send_file(
        output,
        mimetype='application/pdf',
        as_attachment=True,
        download_name='resultados_disc_detalhado.pdf'
    )

@app.route('/export/pdf', methods=['POST'])
def export_pdf():
    try:
        data = request.get_json()
        if not data:
            return jsonify({'error': 'Dados não fornecidos'}), 400
        
        # Create PDF
        output = BytesIO()
        doc = SimpleDocTemplate(output, pagesize=A4, rightMargin=72, leftMargin=72, topMargin=72, bottomMargin=18)
        
        # Container for PDF elements
        elements = []
        styles = getSampleStyleSheet()
        
        # Title
        title_style = ParagraphStyle(
            'CustomTitle',
            parent=styles['Heading1'],
            fontSize=24,
            spaceAfter=30,
            alignment=1  # Center alignment
        )
        elements.append(Paragraph("📊 Relatório DISC", title_style))
        elements.append(Spacer(1, 20))
        
        # Statistics section
        elements.append(Paragraph("📈 Estatísticas Gerais", styles['Heading2']))
        elements.append(Spacer(1, 12))
        
        stats_data = [['Estatística', 'Valor']]
        for stat in data['statistics']:
            stats_data.append([stat['label'], str(stat['value'])])
        
        stats_table = Table(stats_data)
        stats_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
            ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 14),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
            ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
            ('GRID', (0, 0), (-1, -1), 1, colors.black)
        ]))
        
        elements.append(stats_table)
        elements.append(Spacer(1, 30))
        
        # Individual results section
        elements.append(Paragraph("👥 Resultados Individuais", styles['Heading2']))
        elements.append(Spacer(1, 12))
        
        # Create table data
        table_data = [['Nome', 'D (%)', 'I (%)', 'S (%)', 'C (%)', 'Perfil Dominante']]
        
        for person in data['individuals']:
            row = [
                person['name'],
                f"{person['scores']['D']:.1f}%",
                f"{person['scores']['I']:.1f}%",
                f"{person['scores']['S']:.1f}%",
                f"{person['scores']['C']:.1f}%",
                person['dominant_profile']
            ]
            table_data.append(row)
        
        # Create table
        table = Table(table_data)
        table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
            ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 12),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
            ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
            ('GRID', (0, 0), (-1, -1), 1, colors.black),
            ('FONTSIZE', (0, 1), (-1, -1), 10),
        ]))
        
        elements.append(table)
        
        # Build PDF
        doc.build(elements)
        output.seek(0)
        
        return send_file(
            output,
            mimetype='application/pdf',
            as_attachment=True,
            download_name='resultados_disc.pdf'
        )
        
    except Exception as e:
        app.logger.error(f"Error exporting PDF: {str(e)}")
        return jsonify({'error': f'Erro ao exportar PDF: {str(e)}'}), 500
