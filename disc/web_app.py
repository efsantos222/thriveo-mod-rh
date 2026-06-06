#!/usr/bin/env python3
"""
DISC Assessment Web Application
Aplicação web Flask para o Sistema de Avaliação DISC
"""

from flask import Flask, render_template_string, request, jsonify, send_file
import json
from datetime import datetime
import os
from disc_test import DISCTest
from disc_results import DISCResults
from disc_data import DISC_QUESTIONS

app = Flask(__name__)
app.config['SECRET_KEY'] = 'disc-assessment-secret-key-change-this'

# HTML Template
HTML_TEMPLATE = """
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste DISC - Avaliação de Personalidade</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .profiles {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .profile-card {
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .profile-d { background: #ff6b6b; color: white; }
        .profile-i { background: #ffd93d; color: #333; }
        .profile-s { background: #6bcf7f; color: white; }
        .profile-c { background: #4d96ff; color: white; }

        .question-container {
            display: none;
        }

        .question-container.active {
            display: block;
        }

        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .question {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .option {
            padding: 20px;
            background: #f5f5f5;
            border: 3px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option:hover {
            background: #e8e8e8;
            transform: translateX(5px);
        }

        .option.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        button {
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .results {
            display: none;
        }

        .results.active {
            display: block;
        }

        .result-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .result-card h2 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .chart {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .bar-container {
            margin: 15px 0;
        }

        .bar-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        .bar {
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            transition: width 1s ease;
            display: flex;
            align-items: center;
            padding-left: 10px;
            color: white;
            font-weight: bold;
        }

        .bar-d { background: #ff6b6b; }
        .bar-i { background: #ffd93d; color: #333; }
        .bar-s { background: #6bcf7f; }
        .bar-c { background: #4d96ff; }

        .details {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .details h3 {
            color: #667eea;
            margin-bottom: 15px;
        }

        .details ul {
            list-style-position: inside;
            line-height: 1.8;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 1.8em;
            }

            .profiles {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="welcome">
            <h1>🎯 Teste DISC</h1>
            <p class="subtitle">Descubra seu perfil comportamental</p>

            <div class="profiles">
                <div class="profile-card profile-d">
                    <h3>🔴 Dominância</h3>
                    <p>Executor</p>
                </div>
                <div class="profile-card profile-i">
                    <h3>🟡 Influência</h3>
                    <p>Comunicador</p>
                </div>
                <div class="profile-card profile-s">
                    <h3>🟢 Estabilidade</h3>
                    <p>Apoiador</p>
                </div>
                <div class="profile-card profile-c">
                    <h3>🔵 Conformidade</h3>
                    <p>Analista</p>
                </div>
            </div>

            <p style="text-align: center; margin-bottom: 20px;">
                Responda 20 perguntas e descubra seu perfil!
            </p>

            <button class="btn-primary" onclick="startTest()" style="width: 100%;">
                Começar Teste
            </button>
        </div>

        <div id="test" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill" id="progress">0%</div>
            </div>

            <div id="questions"></div>

            <div class="buttons">
                <button class="btn-secondary" onclick="previousQuestion()" id="prevBtn" style="display: none;">
                    ← Anterior
                </button>
                <button class="btn-primary" onclick="nextQuestion()" id="nextBtn" disabled>
                    Próxima →
                </button>
            </div>
        </div>

        <div id="results" class="results">
            <div class="result-card">
                <h2 id="profileName">Seu Perfil</h2>
                <p id="profileDescription"></p>
            </div>

            <div class="chart">
                <h3 style="text-align: center; color: #333; margin-bottom: 20px;">
                    Distribuição dos Perfis
                </h3>
                <div id="chartContainer"></div>
            </div>

            <div class="details" id="profileDetails"></div>

            <div class="export-buttons">
                <button class="btn-primary" onclick="downloadJSON()">
                    📥 Baixar JSON
                </button>
                <button class="btn-primary" onclick="downloadTXT()">
                    📥 Baixar TXT
                </button>
                <button class="btn-secondary" onclick="restartTest()">
                    🔄 Refazer Teste
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentQuestion = 0;
        let answers = [];
        let questions = [];

        async function loadQuestions() {
            const response = await fetch("{{ url_for('get_questions') }}");
            questions = await response.json();
        }

        async function startTest() {
            await loadQuestions();
            document.getElementById('welcome').style.display = 'none';
            document.getElementById('test').style.display = 'block';
            renderQuestion();
        }

        function renderQuestion() {
            const container = document.getElementById('questions');
            container.innerHTML = '';

            const q = questions[currentQuestion];
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question-container active';

            questionDiv.innerHTML = `
                <div class="question">${currentQuestion + 1}. ${q.question}</div>
                <div class="options">
                    ${Object.entries(q.options).map(([key, value]) => `
                        <div class="option" onclick="selectOption('${key}', this)">
                            ${value}
                        </div>
                    `).join('')}
                </div>
            `;

            container.appendChild(questionDiv);

            updateProgress();
            updateButtons();
        }

        function selectOption(trait, element) {
            document.querySelectorAll('.option').forEach(opt => {
                opt.classList.remove('selected');
            });
            element.classList.add('selected');
            answers[currentQuestion] = trait;
            document.getElementById('nextBtn').disabled = false;
        }

        function nextQuestion() {
            if (currentQuestion < questions.length - 1) {
                currentQuestion++;
                renderQuestion();
            } else {
                submitTest();
            }
        }

        function previousQuestion() {
            if (currentQuestion > 0) {
                currentQuestion--;
                renderQuestion();
                if (answers[currentQuestion]) {
                    document.getElementById('nextBtn').disabled = false;
                }
            }
        }

        function updateProgress() {
            const progress = ((currentQuestion + 1) / questions.length) * 100;
            document.getElementById('progress').style.width = progress + '%';
            document.getElementById('progress').textContent = Math.round(progress) + '%';
        }

        function updateButtons() {
            document.getElementById('prevBtn').style.display = currentQuestion > 0 ? 'block' : 'none';
            document.getElementById('nextBtn').textContent =
                currentQuestion === questions.length - 1 ? 'Finalizar ✓' : 'Próxima →';
            document.getElementById('nextBtn').disabled = !answers[currentQuestion];
        }

        async function submitTest() {
            const response = await fetch("{{ url_for('submit_test') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ answers: answers })
            });

            const results = await response.json();
            displayResults(results);
        }

        function displayResults(results) {
            document.getElementById('test').style.display = 'none';
            document.getElementById('results').classList.add('active');

            document.getElementById('profileName').textContent =
                results.summary.primary_profile + ' - ' + results.summary.primary_name;

            if (results.summary.is_combination) {
                document.getElementById('profileDescription').textContent =
                    `Perfil Combinado: ${results.summary.combination_key}`;
            } else {
                document.getElementById('profileDescription').textContent =
                    'Perfil Dominante';
            }

            const chartContainer = document.getElementById('chartContainer');
            chartContainer.innerHTML = '';

            const traits = ['D', 'I', 'S', 'C'];
            const colors = ['d', 'i', 's', 'c'];
            const names = {
                'D': 'Dominância',
                'I': 'Influência',
                'S': 'Estabilidade',
                'C': 'Conformidade'
            };

            traits.forEach((trait, index) => {
                const percentage = results.summary.percentages[trait];
                chartContainer.innerHTML += `
                    <div class="bar-container">
                        <div class="bar-label">
                            <span>${trait} - ${names[trait]}</span>
                            <span>${percentage.toFixed(1)}%</span>
                        </div>
                        <div class="bar">
                            <div class="bar-fill bar-${colors[index]}"
                                 style="width: ${percentage}%">
                            </div>
                        </div>
                    </div>
                `;
            });

            displayProfileDetails(results);
            window.resultsData = results;
        }

        function displayProfileDetails(results) {
            const profile = results.detailed_profiles[results.summary.primary_profile];
            const detailsDiv = document.getElementById('profileDetails');

            detailsDiv.innerHTML = `
                <h3>📋 Características</h3>
                <ul>${profile.characteristics.map(c => `<li>${c}</li>`).join('')}</ul>

                <h3 style="margin-top: 20px;">💪 Pontos Fortes</h3>
                <ul>${profile.strengths.map(s => `<li>${s}</li>`).join('')}</ul>

                <h3 style="margin-top: 20px;">⚠️ Áreas de Atenção</h3>
                <ul>${profile.weaknesses.map(w => `<li>${w}</li>`).join('')}</ul>
            `;
        }

        function downloadJSON() {
            const data = JSON.stringify(window.resultsData, null, 2);
            const blob = new Blob([data], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'disc_resultado_' + Date.now() + '.json';
            a.click();
        }

        function downloadTXT() {
            fetch("{{ url_for('download_txt') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(window.resultsData)
            })
            .then(response => response.blob())
            .then(blob => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'disc_resultado_' + Date.now() + '.txt';
                a.click();
            });
        }

        function restartTest() {
            currentQuestion = 0;
            answers = [];
            document.getElementById('results').classList.remove('active');
            document.getElementById('welcome').style.display = 'block';
        }
    </script>
</body>
</html>
"""



@app.route('/')
def index():
    """Página inicial"""
    return render_template_string(HTML_TEMPLATE)


@app.route('/api/questions')
def get_questions():
    """Retorna as perguntas do teste"""
    return jsonify(DISC_QUESTIONS)


@app.route('/api/submit', methods=['POST'])
def submit_test():
    """Processa as respostas e retorna resultados"""
    data = request.json
    answers = data.get('answers', [])

    # Criar teste e processar respostas
    test = DISCTest()
    results = test.quick_test(answers)

    # Retornar resultados
    return jsonify(results.get_summary())


@app.route('/api/download-txt', methods=['POST'])
def download_txt():
    """Gera e retorna arquivo TXT com resultados"""
    data = request.json

    # Reconstruir resultados
    scores = data['summary']['scores']
    results = DISCResults(scores)

    # Salvar em arquivo temporário
    filename = f'/tmp/disc_result_{datetime.now().strftime("%Y%m%d_%H%M%S")}.txt'
    results.save_to_file(filename)

    return send_file(filename, as_attachment=True, download_name='disc_resultado.txt')


if __name__ == '__main__':
    print("\n" + "="*70)
    print("  🎯 DISC Assessment Web Application")
    print("="*70)
    print("\n  Servidor iniciando...")
    print("  Acesse: http://localhost:5000")
    print("  ou")
    print("  Acesse: http://seu-ip:5000")
    print("\n  Pressione CTRL+C para parar")
    print("="*70 + "\n")

    # Executar em modo de produção, use:
    # gunicorn -w 4 -b 0.0.0.0:5000 web_app:app

    app.run(host='0.0.0.0', port=5000, debug=False)
