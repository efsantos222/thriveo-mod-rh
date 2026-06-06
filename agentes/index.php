<?php
// ─── Configuração de segurança ────────────────────────────────────────────────
// A chave da API Anthropic fica APENAS no servidor PHP, nunca exposta ao browser
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="SysManager AI">
  <meta name="theme-color" content="#080b14">
  <title>SysManager AI · Agentes Corporativos</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div id="app">

  <!-- ── Sidebar ── -->
  <aside id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-brand">
        <div class="brand-icon">🏛️</div>
        <div>
          <div class="brand-name">SysManager AI</div>
          <div class="brand-sub">Diretoria Corporativa</div>
        </div>
      </div>
      <div class="integration-badges">
        <div class="badge badge-m365">
          <span class="dot dot-blue"></span> M365 Conectado
        </div>
        <div class="badge badge-clickup">
          <span class="dot dot-purple"></span> ClickUp
        </div>
        <div class="badge badge-apify" id="apify-badge" style="display:none">
          <span class="dot dot-orange"></span> Apify
        </div>
      </div>
      <input type="text" id="agent-search" placeholder="Buscar agente..." class="search-input" oninput="filterAgents(this.value)">
    </div>

    <div class="sidebar-list" id="agent-list">
      <!-- Gerado pelo JS -->
    </div>

    <div class="sidebar-footer">
      <div id="apify-config-panel" style="display:none"></div>
      <button class="apify-btn" id="apify-toggle-btn" onclick="toggleApifyConfig()">
        <span class="apify-btn-icon">🕷️</span>
        <div class="apify-btn-text">
          <div class="apify-btn-title" id="apify-btn-title">Apify Web Intelligence</div>
          <div class="apify-btn-sub" id="apify-btn-sub">⚙️ Clique para configurar</div>
        </div>
        <div class="dot dot-orange" id="apify-dot" style="display:none"></div>
      </button>
      <div class="footer-version">13 Agentes · M365 + ClickUp + Apify · v3.0</div>
    </div>
  </aside>

  <!-- ── Main Panel ── -->
  <main id="main-panel">

    <!-- HOME screen (padrão) -->
    <div id="screen-home" class="screen active">
      <div class="home-inner">
        <div class="home-icon">🏛️</div>
        <h1 class="home-title">Sistema de Agentes de IA</h1>
        <p class="home-subtitle">Diretoria Corporativa · Ezequiel Santos · SysManager</p>
        <div class="home-badges">
          <div class="badge badge-m365"><span class="dot dot-blue"></span> Microsoft 365 Conectado</div>
          <div class="badge badge-clickup"><span class="dot dot-purple"></span> ClickUp Conectado</div>
          <div id="home-apify-active" class="badge badge-apify" style="display:none"><span class="dot dot-orange"></span> Apify Ativo</div>
          <button id="home-apify-config-btn" class="badge badge-outline" onclick="toggleApifyConfig()" style="cursor:pointer">🕷️ Configurar Apify</button>
        </div>

        <!-- Laércio destaque -->
        <button class="laercio-hero" onclick="openLaercio()">
          <div class="laercio-hero-icon">🎯</div>
          <div class="laercio-hero-content">
            <div class="laercio-hero-header">
              <span class="laercio-hero-name">Laércio</span>
              <span class="badge-pill badge-amber">Orquestrador Corporativo</span>
            </div>
            <p class="laercio-hero-desc">Aciona todos os 12 agentes em paralelo · Consolida relatórios · Gerencia transições</p>
            <div class="laercio-hero-tags">
              <span class="tag-amber">🌅 Briefing matinal</span>
              <span class="tag-amber">📊 Status geral</span>
              <span class="tag-amber">⚠️ Pendências críticas</span>
            </div>
          </div>
          <span class="laercio-hero-arrow">→</span>
        </button>

        <p class="home-divider">── ou acesse um agente especializado diretamente ──</p>
        <div class="agent-grid" id="agent-grid">
          <!-- Gerado pelo JS -->
        </div>
      </div>
    </div>

    <!-- LAERCIO screen -->
    <div id="screen-laercio" class="screen">
      <div class="chat-header chat-header-amber">
        <button class="back-btn" onclick="goHome()">← Voltar</button>
        <div class="agent-avatar" style="background:rgba(245,158,11,0.2);border:1px solid rgba(245,158,11,0.5)">🎯</div>
        <div class="agent-info">
          <div class="agent-info-row">
            <span class="agent-name agent-name-amber">Laércio</span>
            <span class="badge-pill badge-amber">Orquestrador · 12 Agentes</span>
          </div>
          <p class="agent-desc">Aciona agentes em paralelo e consolida respostas</p>
        </div>
        <button class="new-chat-btn" onclick="clearLaercioChat()">Nova conversa</button>
      </div>
      <div id="laercio-progress" style="display:none" class="progress-bar-wrap"></div>
      <div class="messages-area" id="laercio-messages"></div>
      <div class="input-area input-area-amber" id="laercio-input-area">
        <div class="quick-actions" id="laercio-quick-actions"></div>
        <div class="pending-files" id="laercio-pending-files" style="display:none"></div>
        <div class="input-row">
          <button class="attach-btn" onclick="document.getElementById('file-input').click()" title="Anexar arquivo">📎</button>
          <textarea id="laercio-textarea" class="chat-textarea chat-textarea-amber" rows="2"
            placeholder="Peça ao Laércio para acionar agentes, gerar briefing matinal, identificar pendências... (Enter para enviar)"
            onkeydown="handleKey(event,'laercio')"></textarea>
          <button class="send-btn" id="laercio-send-btn" onclick="sendLaercio()" disabled style="background:rgba(255,255,255,0.1)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
        <p class="input-footer">Laércio aciona agentes em paralelo · M365 + ClickUp · Aceita arquivos por arraste</p>
      </div>
    </div>

    <!-- AGENT screen (reutilizada para qualquer agente) -->
    <div id="screen-agent" class="screen">
      <div class="chat-header" id="agent-header">
        <button class="back-btn" onclick="goHome()">← Voltar</button>
        <div class="agent-avatar" id="agent-avatar"></div>
        <div class="agent-info">
          <div class="agent-info-row" id="agent-info-row"></div>
          <p class="agent-desc" id="agent-desc"></p>
        </div>
        <button class="cap-btn hidden-mobile" id="cap-btn" onclick="toggleCapabilities()">⚡ Capacidades</button>
        <button class="new-chat-btn" onclick="clearAgentChat()">Nova conversa</button>
      </div>
      <div id="capabilities-panel" style="display:none" class="cap-panel"></div>
      <div class="messages-area" id="agent-messages"></div>
      <div class="input-area" id="agent-input-area">
        <div class="quick-actions" id="agent-quick-actions"></div>
        <div class="pending-files" id="agent-pending-files" style="display:none"></div>
        <div class="input-row">
          <button class="attach-btn" onclick="document.getElementById('file-input').click()" title="Anexar arquivo">📎</button>
          <textarea id="agent-textarea" class="chat-textarea" rows="2"
            placeholder="Pergunte ao agente... (Enter · Shift+Enter = nova linha)"
            onkeydown="handleKey(event,'agent')"></textarea>
          <button class="send-btn" id="agent-send-btn" onclick="sendAgent()" disabled style="background:rgba(255,255,255,0.1)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
        <p class="input-footer">Integrado com Outlook · Teams · SharePoint · OneDrive · ClickUp · Aceita arquivos por arraste</p>
      </div>
    </div>

  </main>
</div>

<!-- File input oculto -->
<input type="file" id="file-input" multiple accept="image/*,.pdf,.txt,.csv,.md,.json" style="display:none" onchange="handleFileInput(event)">

<script src="agents.js"></script>
<script src="app.js"></script>
</body>
</html>
