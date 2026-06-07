<?php
// modulosrs/matchcv/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check main system login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

require_once 'config.php';
checkAccess(); // Enforce trial/subscription
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Match CV — Análise de Aderência</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap"
    rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
  <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg: #f4f6fa;
      --surface: #ffffff;
      --surface2: #f1f5f9;
      --border: #e2e8f0;
      --accent: #f97316; /* Theme Orange */
      --accent2: #ff6584;
      --accent3: #059669;
      --text: #0f172a;
      --muted: #64748b;
      --card-bg: #ffffff;
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      overflow-x: hidden;
    }

    .app {
      min-height: 100vh;
      background: var(--bg);
    }

    /* Header */
    header {
      padding: 1rem 2rem;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 1px 3px 0 rgba(0,0,0,0.07);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .logo {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 1.4rem;
      color: var(--accent);
      letter-spacing: -0.02em;
      text-decoration: none;
    }

    .back-link {
        color: var(--muted);
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.2s;
    }

    .back-link:hover {
        color: var(--accent);
    }

    .badge {
      font-size: 0.65rem;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      background: rgba(249, 115, 22, 0.15);
      border: 1px solid rgba(249, 115, 22, 0.3);
      color: var(--accent);
      padding: 0.2rem 0.6rem;
      border-radius: 20px;
    }

    main {
      max-width: 1100px;
      margin: 0 auto;
      padding: 2rem;
    }

    /* Hero */
    .hero {
      text-align: center;
      margin-bottom: 3rem;
    }

    .hero h1 {
      font-family: 'Syne', sans-serif;
      font-size: clamp(1.8rem, 4vw, 2.8rem);
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -0.03em;
      margin-bottom: 1rem;
    }

    .hero h1 span {
      color: var(--accent);
    }

    .hero p {
      font-size: 1rem;
      color: var(--muted);
      max-width: 550px;
      margin: 0 auto;
      line-height: 1.6;
    }

    /* Upload grid */
    .upload-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    @media (max-width: 650px) {
      .upload-grid {
        grid-template-columns: 1fr;
      }
    }

    .upload-card {
      background: var(--surface);
      border: 2px dashed var(--border);
      border-radius: 20px;
      padding: 2.5rem 2rem;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .upload-card:hover {
      border-color: var(--accent);
      background: rgba(249, 115, 22, 0.05);
    }

    .upload-card.has-file {
      border-style: solid;
      border-color: var(--accent3);
      background: rgba(67, 233, 123, 0.04);
    }

    .upload-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.2rem;
      font-size: 1.5rem;
      background: #f1f5f9;
    }

    .upload-card.has-file .upload-icon {
      background: rgba(67, 233, 123, 0.1);
      color: var(--accent3);
    }

    .file-name {
      margin-top: 0.8rem;
      font-size: 0.75rem;
      font-weight: 500;
      color: var(--accent3);
      padding: 0.3rem 0.8rem;
      background: rgba(67, 233, 123, 0.1);
      border-radius: 20px;
      display: inline-block;
    }

    input[type=file] { display: none; }

    /* Button */
    .analyze-btn {
      width: 100%;
      padding: 1.2rem;
      font-family: 'Syne', sans-serif;
      font-size: 1.1rem;
      font-weight: 800;
      background: var(--accent);
      color: white;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.8rem;
      transition: all 0.3s;
    }

    .analyze-btn:hover:not(:disabled) {
      filter: brightness(1.1);
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(249, 115, 22, 0.3);
    }

    .analyze-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    /* Results */
    .results { display: none; margin-top: 2rem; }
    .section { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem; }
    .section-title { font-family: 'Syne', sans-serif; font-size: 0.85rem; font-weight: 800; text-transform: uppercase; color: var(--accent); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .score-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; flex: 1; }
    .score-value { font-family: 'Syne', sans-serif; font-size: 2.5rem; font-weight: 800; color: var(--accent3); }
    
    .processing-state { text-align: center; padding: 4rem; display: none; }
    .spinner { width: 30px; height: 30px; border: 3px solid rgba(0,0,0,0.1); border-top-color: var(--accent); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Modal */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 1rem; }
    .modal-overlay.open { display: flex; }
    .modal { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; width: 100%; max-width: 700px; overflow: hidden; }
    .modal-header { padding: 1.2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .modal-body { padding: 1.5rem; }
    .modal-textarea { width: 100%; height: 400px; background: #000; color: #0f0; font-family: monospace; border-radius: 10px; padding: 1rem; }
  </style>
</head>

<body>
  <div class="app">
    <header>
      <div class="header-left">
        <a href="../../dashboard.php" class="logo">Thriveo MatchCV</a>
        <a href="../../dashboard.php" class="back-link">← Voltar</a>
      </div>
      <div class="badge">IA Matcher</div>
    </header>

    <main>
      <div id="uploadScreen">
        <div class="hero">
          <h1>Análise de <span>Aderência CV</span></h1>
          <p>Compare os requisitos da vaga com o currículo do candidato usando Inteligência Artificial de ponta.</p>
        </div>

        <div class="upload-grid">
          <div class="upload-card" id="cardSpec" onclick="document.getElementById('inputSpec').click()">
            <div class="upload-icon" id="iconSpec">📋</div>
            <h3>Requisitos da Vaga</h3>
            <p id="hintSpec">Clique para enviar o PDF da Vaga</p>
            <div class="file-name" id="nameSpec" style="display:none"></div>
            <input type="file" id="inputSpec" accept=".pdf" />
          </div>

          <div class="upload-card" id="cardResume" onclick="document.getElementById('inputResume').click()">
            <div class="upload-icon" id="iconResume">👤</div>
            <h3>Currículo</h3>
            <p id="hintResume">Clique para enviar o PDF do CV</p>
            <div class="file-name" id="nameResume" style="display:none"></div>
            <input type="file" id="inputResume" accept=".pdf" />
          </div>
        </div>

        <button class="analyze-btn" id="analyzeBtn" onclick="analyze()" disabled>
          <span>Iniciar Análise com IA</span>
        </button>
        <p id="errorMsg" style="color: #ff6584; margin-top: 1rem; text-align: center; display: none;"></p>
      </div>

      <div class="processing-state" id="processingState">
        <div class="spinner"></div>
        <h3 style="margin-top: 2rem;">Cruzando informações com IA...</h3>
        <p style="color: var(--muted); margin-top: 1rem;">Isso pode levar alguns segundos.</p>
      </div>

      <div class="results" id="results">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Relatório de Match</h2>
            <button onclick="reset()" style="background:none; border: 1px solid var(--border); color: var(--text); padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer;">← Nova Análise</button>
        </div>
        <div id="sectionsContainer"></div>
      </div>
    </main>
  </div>

  <div class="modal-overlay" id="modalOverlay">
      <div class="modal">
          <div class="modal-header">
              <h3>Relatório TXT</h3>
              <button onclick="closeModal()" style="background:none; border:none; color:var(--text); cursor:pointer;">✕</button>
          </div>
          <div class="modal-body">
              <textarea id="modalTextarea" class="modal-textarea" readonly></textarea>
          </div>
      </div>
  </div>

  <script>
    let specFile = null, resumeFile = null, reportData = null;

    function setFile(file, type) {
      if (!file) return;
      if (type === 'spec') {
        specFile = file;
        document.getElementById('cardSpec').classList.add('has-file');
        document.getElementById('nameSpec').textContent = file.name;
        document.getElementById('nameSpec').style.display = 'inline-block';
      } else {
        resumeFile = file;
        document.getElementById('cardResume').classList.add('has-file');
        document.getElementById('nameResume').textContent = file.name;
        document.getElementById('nameResume').style.display = 'inline-block';
      }
      document.getElementById('analyzeBtn').disabled = !(specFile && resumeFile);
    }

    document.getElementById('inputSpec').onchange = e => setFile(e.target.files[0], 'spec');
    document.getElementById('inputResume').onchange = e => setFile(e.target.files[0], 'resume');

    async function getPdfText(file) {
      const buffer = await file.arrayBuffer();
      const pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
      let text = "";
      for (let i = 1; i <= pdf.numPages; i++) {
        const page = await pdf.getPage(i);
        const content = await page.getTextContent();
        text += content.items.map(item => item.str).join(" ") + "\n";
      }
      return text;
    }

    async function analyze() {
      document.getElementById('uploadScreen').style.display = 'none';
      document.getElementById('processingState').style.display = 'block';
      
      try {
        const [specText, resumeText] = await Promise.all([getPdfText(specFile), getPdfText(resumeFile)]);
        const response = await fetch('proxy.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            messages: [{ role: 'user', content: `Compare currículo e vaga. Gere relatório em Português.\nVAGA:\n${specText}\nCV:\n${resumeText}` }]
          })
        });
        const data = await response.json();
        const text = data.choices[0].message.content;
        renderResults(text);
      } catch (e) {
        alert("Erro: " + e.message);
        reset();
      }
    }

    function renderResults(text) {
      document.getElementById('processingState').style.display = 'none';
      document.getElementById('results').style.display = 'block';
      const container = document.getElementById('sectionsContainer');
      container.innerHTML = `<div class="section"><div class="section-title">Análise da IA</div><div style="white-space: pre-wrap; line-height: 1.6;">${text}</div></div>`;
    }

    function reset() { location.reload(); }
    function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }
  </script>
</body>
</html>