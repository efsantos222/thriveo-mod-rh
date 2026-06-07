import os
import json
from openai import OpenAI

class DISCAIAnalyzer:
    def __init__(self):
        """Initialize the OpenAI client with API key"""
        self.client = OpenAI(
            api_key=os.environ.get("OPENAI_API_KEY"),
            timeout=30.0  # 30 second timeout
        )
        # The newest OpenAI model is "gpt-4o" which was released May 13, 2024.
        # Do not change this unless explicitly requested by the user
        self.model = "gpt-4o"
        
    def analyze_individual_profile(self, person_data):
        """
        Analyze an individual's DISC profile using OpenAI
        Returns personalized insights including strengths, improvements, and development areas
        """
        try:
            # Prepare the profile data
            name = person_data.get('name', 'Candidato')
            scores = person_data.get('scores', {})
            dominant_profile = person_data.get('dominant_profile', 'N/A')
            
            # Create detailed prompt for analysis
            prompt = self._create_analysis_prompt(name, scores, dominant_profile)
            
            response = self.client.chat.completions.create(
                model=self.model,
                messages=[
                    {
                        "role": "system",
                        "content": """Você é um especialista em análise comportamental DISC com mais de 20 anos de experiência. 
                        Forneça análises precisas, profissionais e construtivas baseadas nos perfis DISC.
                        Responda sempre em português brasileiro de forma clara e objetiva.
                        Retorne a resposta em formato JSON válido."""
                    },
                    {
                        "role": "user", 
                        "content": prompt
                    }
                ],
                response_format={"type": "json_object"},
                temperature=0.7,
                max_tokens=1500,
                timeout=25.0
            )
            
            # Parse the JSON response
            content = response.choices[0].message.content
            if content:
                analysis = json.loads(content)
                return analysis
            else:
                return self._create_fallback_analysis(person_data)
            
        except Exception as e:
            # Log the specific error for debugging
            print(f"OpenAI API error for {person_data.get('name', 'Unknown')}: {str(e)}")
            # Return fallback analysis if API fails
            return self._create_fallback_analysis(person_data)
    
    def _create_analysis_prompt(self, name, scores, dominant_profile):
        """Create a detailed prompt for OpenAI analysis"""
        scores_text = ", ".join([f"{profile}: {score:.1f}%" for profile, score in scores.items()])
        
        prompt = f"""
        Analise o perfil DISC do candidato com os seguintes dados:
        
        Nome: {name}
        Perfil Dominante: {dominant_profile}
        Pontuações: {scores_text}
        
        Contexto dos perfis DISC:
        - D (Dominância): Foco em resultados, decisões rápidas, liderança, competitividade
        - I (Influência): Comunicação, relacionamentos, otimismo, persuasão
        - S (Estabilidade): Paciência, trabalho em equipe, consistência, apoio
        - C (Conformidade): Análise, precisão, qualidade, seguimento de regras
        
        Forneça uma análise completa em formato JSON com a seguinte estrutura:
        {{
            "pontos_fortes": [
                "Lista de 4-5 pontos fortes específicos baseados no perfil"
            ],
            "oportunidades_melhoria": [
                "Lista de 3-4 áreas que podem ser desenvolvidas"
            ],
            "areas_desenvolvimento": [
                "Lista de 3-4 competências específicas para desenvolver"
            ],
            "estilo_comunicacao": "Descrição do estilo de comunicação preferido",
            "ambiente_trabalho_ideal": "Descrição do ambiente de trabalho mais adequado",
            "dicas_gestao": "Dicas específicas para gestores sobre como trabalhar com este perfil",
            "resumo_executivo": "Resumo em 2-3 frases do perfil comportamental"
        }}
        
        Base a análise nas pontuações específicas fornecidas, considerando não apenas o perfil dominante, 
        mas também os outros perfis que podem influenciar o comportamento.
        """
        
        return prompt
    
    def _create_fallback_analysis(self, person_data):
        """Create a basic fallback analysis if OpenAI API fails"""
        dominant = person_data.get('dominant_profile', 'D')
        
        fallback_analyses = {
            'D': {
                "pontos_fortes": [
                    "Orientação forte para resultados",
                    "Capacidade de tomada de decisões rápidas",
                    "Liderança natural e iniciativa",
                    "Foco em objetivos e metas",
                    "Coragem para enfrentar desafios"
                ],
                "oportunidades_melhoria": [
                    "Desenvolver paciência em processos longos",
                    "Melhorar escuta ativa com a equipe",
                    "Considerar mais opiniões antes de decidir"
                ],
                "areas_desenvolvimento": [
                    "Habilidades de delegação efetiva",
                    "Comunicação empática",
                    "Gestão de conflitos colaborativa"
                ],
                "estilo_comunicacao": "Direto, objetivo e focado em resultados",
                "ambiente_trabalho_ideal": "Ambiente dinâmico com autonomia e desafios constantes",
                "dicas_gestao": "Forneça objetivos claros, autonomia e reconheça conquistas",
                "resumo_executivo": "Perfil orientado para resultados com forte capacidade de liderança e tomada de decisões."
            },
            'I': {
                "pontos_fortes": [
                    "Excelentes habilidades de comunicação",
                    "Capacidade de influenciar e motivar",
                    "Otimismo e energia contagiantes",
                    "Facilidade para networking",
                    "Criatividade e inovação"
                ],
                "oportunidades_melhoria": [
                    "Melhorar foco em detalhes",
                    "Desenvolver organização pessoal",
                    "Controlar impulsos em decisões"
                ],
                "areas_desenvolvimento": [
                    "Gestão de tempo e prioridades",
                    "Análise crítica e planejamento",
                    "Persistência em tarefas rotineiras"
                ],
                "estilo_comunicacao": "Entusiástico, expressivo e orientado para pessoas",
                "ambiente_trabalho_ideal": "Ambiente colaborativo com interação social e variedade",
                "dicas_gestao": "Reconheça publicamente, forneça variedade e interação social",
                "resumo_executivo": "Perfil comunicativo e influente com grande capacidade de motivação e relacionamento."
            },
            'S': {
                "pontos_fortes": [
                    "Excelente trabalho em equipe",
                    "Paciência e estabilidade emocional",
                    "Confiabilidade e consistência",
                    "Capacidade de apoio e suporte",
                    "Harmonia em relacionamentos"
                ],
                "oportunidades_melhoria": [
                    "Desenvolver assertividade",
                    "Melhorar adaptação a mudanças",
                    "Expressar opiniões com mais frequência"
                ],
                "areas_desenvolvimento": [
                    "Liderança de iniciativas",
                    "Gestão de mudanças",
                    "Comunicação assertiva"
                ],
                "estilo_comunicacao": "Calmo, paciente e focado em relacionamentos",
                "ambiente_trabalho_ideal": "Ambiente estável com equipe colaborativa e processos definidos",
                "dicas_gestao": "Forneça segurança, tempo para adaptações e reconhecimento pessoal",
                "resumo_executivo": "Perfil estável e colaborativo com forte capacidade de apoio e trabalho em equipe."
            },
            'C': {
                "pontos_fortes": [
                    "Alta precisão e atenção aos detalhes",
                    "Capacidade analítica excepcional",
                    "Foco em qualidade e excelência",
                    "Organização e planejamento",
                    "Seguimento rigoroso de procedimentos"
                ],
                "oportunidades_melhoria": [
                    "Acelerar processo de tomada de decisão",
                    "Desenvolver flexibilidade",
                    "Melhorar comunicação interpessoal"
                ],
                "areas_desenvolvimento": [
                    "Liderança de pessoas",
                    "Adaptabilidade a mudanças",
                    "Comunicação persuasiva"
                ],
                "estilo_comunicacao": "Formal, preciso e baseado em dados",
                "ambiente_trabalho_ideal": "Ambiente estruturado com padrões claros e tempo para análise",
                "dicas_gestao": "Forneça informações detalhadas, tempo para análise e padrões claros",
                "resumo_executivo": "Perfil analítico e meticuloso com foco em qualidade e precisão."
            }
        }
        
        return fallback_analyses.get(dominant, fallback_analyses['D'])
    
    def analyze_team_dynamics(self, individuals_data):
        """Analyze team dynamics based on DISC profiles"""
        try:
            # Prepare team data
            team_composition = {}
            for person in individuals_data:
                profile = person.get('dominant_profile', 'D')
                team_composition[profile] = team_composition.get(profile, 0) + 1
            
            total_people = len(individuals_data)
            composition_text = ", ".join([f"{profile}: {count} pessoas ({count/total_people*100:.1f}%)" 
                                        for profile, count in team_composition.items()])
            
            prompt = f"""
            Analise a dinâmica de equipe baseada na composição DISC:
            
            Total de pessoas: {total_people}
            Composição: {composition_text}
            
            Forneça uma análise em formato JSON:
            {{
                "pontos_fortes_equipe": ["Lista de pontos fortes da equipe"],
                "desafios_potenciais": ["Lista de desafios que podem surgir"],
                "recomendacoes_gestao": ["Recomendações para gestão da equipe"],
                "dinamica_geral": "Descrição da dinâmica geral esperada"
            }}
            """
            
            response = self.client.chat.completions.create(
                model=self.model,
                messages=[
                    {
                        "role": "system",
                        "content": "Você é um especialista em dinâmica de equipes e análise DISC. Responda em português brasileiro em formato JSON."
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ],
                response_format={"type": "json_object"},
                temperature=0.7,
                max_tokens=1000,
                timeout=20.0
            )
            
            content = response.choices[0].message.content
            if content:
                return json.loads(content)
            else:
                return {
                    "pontos_fortes_equipe": ["Diversidade de perfis comportamentais"],
                    "desafios_potenciais": ["Necessidade de alinhamento de estilos de trabalho"],
                    "recomendacoes_gestao": ["Promover comunicação aberta entre perfis diferentes"],
                    "dinamica_geral": "Equipe com potencial para resultados diversos dependendo do alinhamento dos perfis."
                }
            
        except Exception as e:
            return {
                "pontos_fortes_equipe": ["Diversidade de perfis comportamentais"],
                "desafios_potenciais": ["Necessidade de alinhamento de estilos de trabalho"],
                "recomendacoes_gestao": ["Promover comunicação aberta entre perfis diferentes"],
                "dinamica_geral": "Equipe com potencial para resultados diversos dependendo do alinhamento dos perfis."
            }
    
    def _create_team_analysis_offline(self, individuals_data):
        """Create team analysis without using OpenAI API"""
        team_composition = {}
        total_people = len(individuals_data)
        
        for person in individuals_data:
            profile = person.get('dominant_profile', 'D')
            team_composition[profile] = team_composition.get(profile, 0) + 1
        
        # Calculate percentages
        composition_analysis = []
        for profile, count in team_composition.items():
            percentage = (count / total_people) * 100
            composition_analysis.append(f"{profile}: {count} pessoas ({percentage:.1f}%)")
        
        # Determine team characteristics based on composition
        dominant_profiles = sorted(team_composition.items(), key=lambda x: x[1], reverse=True)
        main_profile = dominant_profiles[0][0] if dominant_profiles else 'D'
        main_count = dominant_profiles[0][1] if dominant_profiles else 0
        main_percentage = (main_count / total_people) * 100
        
        # Generate analysis based on team composition
        if main_percentage > 50:
            # Homogeneous team
            team_analysis = self._get_homogeneous_team_analysis(main_profile, total_people)
        else:
            # Diverse team
            team_analysis = self._get_diverse_team_analysis(team_composition, total_people)
        
        team_analysis['composicao'] = composition_analysis
        return team_analysis
    
    def _get_homogeneous_team_analysis(self, main_profile, total_people):
        """Analysis for teams with a dominant profile type"""
        analyses = {
            'D': {
                "pontos_fortes_equipe": [
                    "Forte orientação para resultados e metas",
                    "Capacidade de tomar decisões rápidas",
                    "Ambiente competitivo e produtivo",
                    "Execução eficiente de projetos"
                ],
                "desafios_potenciais": [
                    "Pode faltar paciência em processos longos",
                    "Risco de conflitos por competitividade excessiva",
                    "Necessidade de melhor comunicação interpessoal"
                ],
                "recomendacoes_gestao": [
                    "Estabeleça metas claras e mensuráveis",
                    "Permita autonomia na execução",
                    "Promova colaboração ao invés de competição interna",
                    "Reconheça conquistas e resultados"
                ],
                "dinamica_geral": f"Equipe altamente orientada a resultados com {total_people} pessoas focadas em objetivos e execução rápida."
            },
            'I': {
                "pontos_fortes_equipe": [
                    "Excelente comunicação e relacionamento",
                    "Ambiente motivador e energético",
                    "Capacidade de influenciar e persuadir",
                    "Criatividade e inovação em soluções"
                ],
                "desafios_potenciais": [
                    "Pode faltar foco em detalhes técnicos",
                    "Necessidade de melhor organização",
                    "Risco de perder prazos por excesso de socialização"
                ],
                "recomendacoes_gestao": [
                    "Forneça estrutura e organização",
                    "Estabeleça prazos claros e acompanhamento",
                    "Aproveite a energia para motivar outros times",
                    "Permita interação social e networking"
                ],
                "dinamica_geral": f"Equipe comunicativa e influente com {total_people} pessoas focadas em relacionamentos e persuasão."
            },
            'S': {
                "pontos_fortes_equipe": [
                    "Ambiente harmonioso e colaborativo",
                    "Excelente trabalho em equipe",
                    "Confiabilidade e consistência",
                    "Suporte mútuo entre membros"
                ],
                "desafios_potenciais": [
                    "Pode ter dificuldade com mudanças rápidas",
                    "Necessidade de mais assertividade",
                    "Risco de evitar conflitos necessários"
                ],
                "recomendacoes_gestao": [
                    "Forneça segurança e estabilidade",
                    "Comunique mudanças com antecedência",
                    "Incentive a expressão de opiniões",
                    "Reconheça contribuições individuais"
                ],
                "dinamica_geral": f"Equipe estável e colaborativa com {total_people} pessoas focadas em harmonia e suporte mútuo."
            },
            'C': {
                "pontos_fortes_equipe": [
                    "Alta qualidade e precisão no trabalho",
                    "Análise detalhada e cuidadosa",
                    "Processos organizados e estruturados",
                    "Conformidade com padrões e normas"
                ],
                "desafios_potenciais": [
                    "Pode ser lenta na tomada de decisões",
                    "Necessidade de melhor comunicação interpessoal",
                    "Risco de paralisia por análise excessiva"
                ],
                "recomendacoes_gestao": [
                    "Forneça informações completas e detalhadas",
                    "Estabeleça prazos realistas para análise",
                    "Incentive comunicação e interação",
                    "Reconheça a qualidade do trabalho"
                ],
                "dinamica_geral": f"Equipe analítica e precisa com {total_people} pessoas focadas em qualidade e conformidade."
            }
        }
        
        return analyses.get(main_profile, analyses['D'])
    
    def _get_diverse_team_analysis(self, team_composition, total_people):
        """Analysis for diverse teams with multiple profile types"""
        return {
            "pontos_fortes_equipe": [
                "Diversidade de estilos e abordagens",
                "Equilíbrio entre diferentes competências",
                "Capacidade de adaptação a diversos cenários",
                "Complementaridade entre perfis comportamentais"
            ],
            "desafios_potenciais": [
                "Necessidade de alinhamento entre estilos diferentes",
                "Possíveis conflitos de abordagem",
                "Requer comunicação clara entre perfis",
                "Tempo adicional para consenso em decisões"
            ],
            "recomendacoes_gestao": [
                "Promova entendimento mútuo dos diferentes estilos",
                "Estabeleça canais de comunicação claros",
                "Aproveite as forças de cada perfil",
                "Facilite colaboração e trabalho em equipe",
                "Reconheça e valorize as diferentes contribuições"
            ],
            "dinamica_geral": f"Equipe diversificada com {total_people} pessoas oferecendo diferentes perspectivas e competências comportamentais, proporcionando equilíbrio e adaptabilidade."
        }