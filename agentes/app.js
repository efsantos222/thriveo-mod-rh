// ─── app.js ───────────────────────────────────────────────────────────────────
// Lógica principal da aplicação — equivale ao componente App do React

// ── Estado global ──
let activeAgent   = null;
let laercioActive = false;
let chats         = {};      // { agentId: [{role, content, attachments}] }
let laercioChats  = [];
let agentStatuses = {};
let loading       = false;
let pendingFiles  = [];
let dragOver      = false;
let showCapabilities = false;
let apifyKey      = "";

// ── Inicialização ──
document.addEventListener("DOMContentLoaded", () => {
  apifyKey = localStorage.getItem("apify_key") || "";
  updateApifyUI();
  renderAgentList();
  renderAgentGrid();
  renderLaercioQuickActions();

  // Drag & drop global na área de mensagens
  document.getElementById("laercio-messages").addEventListener("dragover", onDragOver);
  document.getElementById("laercio-messages").addEventListener("dragleave", onDragLeave);
  document.getElementById("laercio-messages").addEventListener("drop", onDrop);
  document.getElementById("agent-messages").addEventListener("dragover", onDragOver);
  document.getElementById("agent-messages").addEventListener("dragleave", onDragLeave);
  document.getElementById("agent-messages").addEventListener("drop", onDrop);

  // Textarea auto-send listeners
  document.getElementById("laercio-textarea").addEventListener("input", updateSendBtns);
  document.getElementById("agent-textarea").addEventListener("input", updateSendBtns);
});

// ── API call via PHP proxy ──
// Nota: mcpServers ignorado neste deploy standalone (requer claude.ai + conectores)
async function callClaude(messages, systemPrompt, mcpServers = [], tools = [], betaPdf = false) {
  const standaloneNote = "\n\n[MODO STANDALONE]: Você opera via API direta, sem acesso aos conectores MCP (M365, ClickUp). Responda com seu conhecimento especializado. Se o usuário pedir dados reais de e-mails/SharePoint/ClickUp, explique que neste modo você atua por conhecimento próprio e que a integração em tempo real está disponível na versão claude.ai com os conectores configurados.";
  const payload = { system: systemPrompt + standaloneNote, messages };
  if (tools.length) payload.tools = tools;
  if (betaPdf) payload.beta_pdf = true;

  const resp = await fetch("api.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });

  const data = await resp.json();
  if (!resp.ok) throw new Error(data?.error?.message || `HTTP ${resp.status}`);

  const text = (data.content || []).filter(b => b.type === "text").map(b => b.text).join("");
  return text || "Erro ao processar resposta.";
}

// ── Web search via PHP proxy ──
async function apifySearch(query, searchType, onStatus) {
  const searchQueries = {
    LINKEDIN: `site:linkedin.com/in "${query}" -jobs`,
    NEWS:     `${query} notícias 2025`,
    TENDER:   `licitação "${query}" site:pncp.gov.br OR site:comprasgovernamentais.gov.br`,
    SUPPLIER: `"${query}" fornecedor preço cotação Brasil 2025`,
    MARKET:   `${query} mercado Brasil benchmarking concorrentes`,
  };
  const searchQuery = searchQueries[searchType] || query;
  onStatus?.("🌐 Buscando na web...");

  const prompt = `Faça uma busca web sobre: "${searchQuery}"

Após buscar, retorne um JSON com este formato exato (sem markdown, só JSON puro):
{"results": [{"title": "...", "url": "...", "description": "..."}]}

Inclua até 8 resultados mais relevantes. Foque em resultados em português do Brasil quando possível.`;

  try {
    const payload = {
      system: "Você é um assistente de pesquisa web.",
      messages: [{ role: "user", content: prompt }],
      tools: [{ type: "web_search_20250305", name: "web_search" }],
    };
    const resp = await fetch("api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await resp.json();
    if (!resp.ok) throw new Error(data?.error?.message || `HTTP ${resp.status}`);

    const textBlocks = (data.content || []).filter(b => b.type === "text").map(b => b.text).join("");
    let results = [];
    const jsonMatch = textBlocks.match(/\{\s*"results"\s*:\s*\[[\s\S]*?\]\s*\}/);
    if (jsonMatch) { try { results = JSON.parse(jsonMatch[0]).results || []; } catch {} }
    if (!results.length && textBlocks.trim()) {
      results = [{ title: `Resultado: ${query}`, url: "", description: textBlocks.slice(0, 800) }];
    }
    onStatus?.(`✅ Web: ${results.length} resultados obtidos`);
    return { results, query, timestamp: new Date().toLocaleString("pt-BR") };
  } catch (e) {
    onStatus?.(`❌ Busca web: ${e.message}`);
    throw e;
  }
}

function formatApifyResults(data) {
  if (!data?.results?.length) return "Nenhum resultado encontrado.";
  return data.results.slice(0, 8).map((item, i) =>
    `[${i+1}] ${item.title || "Sem título"}\n${item.url ? `URL: ${item.url}\n` : ""}${item.description || ""}`.trim()
  ).join("\n\n---\n\n");
}

// ── Build API messages (handles attachments) ──
function buildApiMessages(history) {
  return history.map(msg => {
    if (msg.role === "user" && msg.attachments && msg.attachments.length > 0) {
      const content = [];
      const userText = (msg.content || "").trim();
      if (userText) content.push({ type: "text", text: userText });
      for (const att of msg.attachments) {
        if (att.kind === "image") {
          const safeType = ["image/jpeg","image/png","image/gif","image/webp"].includes(att.mimeType) ? att.mimeType : "image/jpeg";
          content.push({ type: "image", source: { type: "base64", media_type: safeType, data: att.data } });
        } else if (att.kind === "pdf") {
          content.push({ type: "document", source: { type: "base64", media_type: "application/pdf", data: att.data } });
        } else if (att.kind === "text") {
          const preview = att.text ? att.text.slice(0, 50000) : "(arquivo vazio)";
          content.push({ type: "text", text: `[Arquivo: ${att.name}]\n\`\`\`\n${preview}\n\`\`\`` });
        }
      }
      if (!content.some(b => b.type === "text")) content.push({ type: "text", text: "Analise o(s) arquivo(s) anexo(s)." });
      return { role: "user", content };
    }
    const text = (msg.content || "").trim();
    return { role: msg.role, content: text || " " };
  });
}

// ── Orchestration ──
async function orchestrateAgents(agentIds, userMessage, onProgress) {
  const targets = agentIds.map(id => AGENTS.find(a => a.id === id)).filter(Boolean);
  return Promise.all(targets.map(async agent => {
    onProgress?.(agent.id, "running");
    try {
      const result = await callClaude(
        [{ role: "user", content: userMessage }],
        agent.systemPrompt,
        agent.mcpServers
      );
      onProgress?.(agent.id, "done");
      return { agent, result, error: null };
    } catch (e) {
      onProgress?.(agent.id, "error");
      return { agent, result: null, error: e.message };
    }
  }));
}

// ── Navigation ──
function showScreen(id) {
  document.querySelectorAll(".screen").forEach(s => s.classList.remove("active"));
  document.getElementById(id).classList.add("active");
}
function goHome() {
  activeAgent   = null;
  laercioActive = false;
  showScreen("screen-home");
  updateSendBtns();
  document.getElementById("capabilities-panel").style.display = "none";
  showCapabilities = false;
}

// ── Render agent list (sidebar) ──
function renderAgentList() {
  const list = document.getElementById("agent-list");
  let html = "";

  // Laércio no topo
  html += `<button class="agent-card laercio-card ${laercioActive ? "active" : ""}" onclick="openLaercio()">
    <div class="ac-avatar" style="background:rgba(245,158,11,0.2);border:1px solid rgba(245,158,11,0.5)">🎯</div>
    <div class="ac-body">
      <div class="ac-row">
        <span class="ac-name" style="color:#fcd34d">Laércio</span>
        <span class="badge-pill badge-amber">Orquestrador</span>
        ${laercioChats.length > 0 ? '<span class="dot dot-amber"></span>' : ""}
      </div>
      <p class="ac-sub">Coordena todos os 12 agentes</p>
    </div>
  </button>`;

  html += `<div class="list-sep">── Agentes Especializados ──</div>`;

  AGENTS.forEach(agent => {
    const isActive = activeAgent?.id === agent.id;
    const hasHistory = !!(chats[agent.id] && chats[agent.id].length > 0);
    html += `<button class="agent-card ${isActive ? "active" : ""}" onclick="openAgent(${agent.id})"
      style="${isActive ? `background:linear-gradient(135deg,${agent.colorDark}55,${agent.color}33)` : ""}">
      <div class="ac-avatar" style="background:${agent.color}25;border:1px solid ${agent.color}40">${agent.emoji}</div>
      <div class="ac-body">
        <div class="ac-row">
          <span class="ac-name">${agent.name}</span>
          <span class="badge-pill ${CADENCE_COLOR[agent.cadence]}">${agent.cadence}</span>
          ${hasHistory ? `<span class="dot" style="background:${agent.color}"></span>` : ""}
        </div>
        <p class="ac-sub">${agent.title}</p>
      </div>
    </button>`;
  });

  list.innerHTML = html;
}

function filterAgents(query) {
  const q = query.toLowerCase();
  document.querySelectorAll(".agent-card").forEach(card => {
    const text = card.innerText.toLowerCase();
    card.style.display = text.includes(q) ? "" : "none";
  });
}

// ── Render home grid ──
function renderAgentGrid() {
  const grid = document.getElementById("agent-grid");
  grid.innerHTML = AGENTS.map(agent => `
    <button class="grid-agent-card" onclick="openAgent(${agent.id})">
      <div class="gac-top">
        <span class="gac-emoji">${agent.emoji}</span>
        <div>
          <div class="gac-name">${agent.name}</div>
          <span class="badge-pill ${CADENCE_COLOR[agent.cadence]}">${agent.cadence}</span>
        </div>
      </div>
      <p class="gac-title">${agent.title}</p>
      <div class="integration-mini">
        ${agent.mcpServers.some(s => s.name === "microsoft365") ? '<span class="badge-mini badge-m365">⊞ M365</span>' : ""}
        ${agent.mcpServers.some(s => s.name === "clickup") ? '<span class="badge-mini badge-clickup">✓ ClickUp</span>' : ""}
      </div>
    </button>
  `).join("");
}

// ── Open Laércio ──
async function openLaercio() {
  laercioActive = true;
  activeAgent   = null;
  showCapabilities = false;
  showScreen("screen-laercio");
  renderAgentList();
  renderLaercioQuickActions();
  updateSendBtns();
  clearPendingFiles();

  if (laercioChats.length === 0) {
    setLoading(true, "🎯 Laércio inicializando...", "laercio");
    try {
      const greeting = await callClaude(
        [{ role: "user", content: "Olá Laércio, estou abrindo o sistema agora." }],
        LAERCIO.systemPrompt,
        LAERCIO.mcpServers
      );
      laercioChats.push({ role: "assistant", content: greeting });
    } catch {
      laercioChats.push({ role: "assistant", content: "Olá Ezequiel! Sou Laércio, seu Orquestrador Corporativo. Posso acionar qualquer um dos 12 agentes especializados. Como posso ajudar?" });
    }
    setLoading(false, "", "laercio");
    renderMessages("laercio");
  } else {
    renderMessages("laercio");
  }
}

// ── Open regular agent ──
async function openAgent(agentIdOrObj) {
  const agent = typeof agentIdOrObj === "number" ? AGENTS.find(a => a.id === agentIdOrObj) : agentIdOrObj;
  if (!agent) return;
  activeAgent   = agent;
  laercioActive = false;
  showCapabilities = false;
  showScreen("screen-agent");
  renderAgentList();
  renderAgentHeader();
  renderAgentQuickActions();
  updateSendBtns();
  clearPendingFiles();

  if (!chats[agent.id] || chats[agent.id].length === 0) {
    setLoading(true, "Inicializando...", "agent");
    try {
      const greeting = await callClaude(
        [{ role: "user", content: "Olá, estou iniciando nossa conversa." }],
        agent.systemPrompt,
        agent.mcpServers
      );
      chats[agent.id] = [{ role: "assistant", content: greeting }];
    } catch {
      chats[agent.id] = [{ role: "assistant", content: `Olá! Sou ${agent.name}, consultor(a) de ${agent.title}. Como posso ajudar?` }];
    }
    setLoading(false, "", "agent");
    renderMessages("agent");
  } else {
    renderMessages("agent");
  }
}

// ── Agent header ──
function renderAgentHeader() {
  if (!activeAgent) return;
  const a = activeAgent;
  document.getElementById("agent-avatar").style.cssText = `background:${a.color}25;border:1px solid ${a.color}40`;
  document.getElementById("agent-avatar").textContent = a.emoji;
  document.getElementById("agent-info-row").innerHTML = `
    <span class="agent-name" style="color:${a.color}">${a.name}</span>
    <span class="badge-pill ${CADENCE_COLOR[a.cadence]}">${a.cadence}</span>
    ${a.mcpServers.some(s => s.name === "microsoft365") ? '<span class="badge-mini badge-m365">⊞ M365</span>' : ""}
    ${a.mcpServers.some(s => s.name === "clickup") ? '<span class="badge-mini badge-clickup">✓ ClickUp</span>' : ""}
  `;
  document.getElementById("agent-desc").textContent = a.title;
  document.getElementById("agent-send-btn").style.background = `linear-gradient(135deg,${a.color},${a.colorDark})`;
  document.getElementById("agent-send-btn").disabled = false;

  // Capabilities panel
  const cap = document.getElementById("capabilities-panel");
  cap.innerHTML = `
    <div class="cap-header">
      <div class="cap-title"><span>${a.emoji}</span> Capacidades de integração de <strong>${a.name}</strong></div>
      <button onclick="toggleCapabilities()">×</button>
    </div>
    <div class="cap-grid">
      ${(a.mcpCapabilities || []).map(c => `<div class="cap-item">${c}</div>`).join("")}
    </div>
    <div class="cap-footer">
      ${a.mcpServers.some(s => s.name === "microsoft365") ? '<span class="badge-mini badge-m365">⊞ M365</span>' : ""}
      ${a.mcpServers.some(s => s.name === "clickup") ? '<span class="badge-mini badge-clickup">✓ ClickUp</span>' : ""}
      <span class="cap-hint">Pergunte e o agente buscará as informações automaticamente</span>
    </div>`;
}

function toggleCapabilities() {
  showCapabilities = !showCapabilities;
  document.getElementById("capabilities-panel").style.display = showCapabilities ? "block" : "none";
}

// ── Laércio quick actions ──
function renderLaercioQuickActions() {
  const el = document.getElementById("laercio-quick-actions");
  el.innerHTML = LAERCIO.quickActions.map(action =>
    `<button class="quick-btn quick-btn-amber" onclick="setInput('laercio','${action.replace(/'/g,"\\'")}')">
      ${action}
    </button>`
  ).join("") + `<button class="quick-btn attach-quick" onclick="document.getElementById('file-input').click()">📎 Anexar</button>`;
}

// ── Agent quick actions ──
function renderAgentQuickActions() {
  if (!activeAgent) return;
  const actions = getQuickActions(activeAgent);
  document.getElementById("agent-quick-actions").innerHTML = actions.map(action =>
    `<button class="quick-btn" onclick="setInput('agent','${action.replace(/'/g,"\\'")}')">
      ${action}
    </button>`
  ).join("") + `<button class="quick-btn attach-quick" onclick="document.getElementById('file-input').click()">📎 Anexar</button>`;
}

function setInput(type, text) {
  const ta = document.getElementById(type === "laercio" ? "laercio-textarea" : "agent-textarea");
  ta.value = text;
  ta.focus();
  updateSendBtns();
}

// ── Messages rendering ──
function renderMessages(type) {
  const msgs = type === "laercio" ? laercioChats : (activeAgent ? chats[activeAgent.id] || [] : []);
  const el   = document.getElementById(type === "laercio" ? "laercio-messages" : "agent-messages");
  const agentColor = type === "laercio" ? "#f59e0b" : (activeAgent?.color || "#6366f1");
  const agentEmoji = type === "laercio" ? "🎯" : (activeAgent?.emoji || "");
  const agentName  = type === "laercio" ? "Laércio · Orquestrador" : (activeAgent?.name || "");

  el.innerHTML = msgs.map(msg => {
    if (msg.role === "user") {
      return `<div class="msg msg-user">
        <div class="msg-avatar msg-avatar-user">ES</div>
        <div class="msg-body msg-body-user">
          <span class="msg-name">Ezequiel Santos</span>
          ${renderAttachments(msg.attachments)}
          ${msg.content ? `<div class="msg-bubble msg-bubble-user">${escapeHtml(msg.content)}</div>` : ""}
        </div>
      </div>`;
    } else {
      const agentIds = msg.agentIds || [];
      return `<div class="msg msg-agent">
        <div class="msg-avatar" style="background:${agentColor}30;border:1px solid ${agentColor}50">${agentEmoji}</div>
        <div class="msg-body">
          <span class="msg-name" style="color:${agentColor}">${agentName}</span>
          ${agentIds.length > 0 ? `<div class="agent-sources">${agentIds.map(id => {
            const a = AGENTS.find(ag => ag.id === id);
            return a ? `<span class="agent-source-tag" style="background:${a.color}15;border-color:${a.color}40;color:${a.color}">${a.emoji} ${a.name}</span>` : "";
          }).join("")}</div>` : ""}
          <div class="msg-bubble" style="background:linear-gradient(135deg,${agentColor}20,${agentColor}10);border:1px solid ${agentColor}30">${escapeHtml(msg.content)}</div>
        </div>
      </div>`;
    }
  }).join("");

  // Scroll to bottom
  el.scrollTop = el.scrollHeight;
}

function renderAttachments(atts) {
  if (!atts || atts.length === 0) return "";
  return `<div class="attachments-row">${atts.map(att => `
    <div class="att-pill">
      ${att.kind === "image" && att.preview ? `<img src="${att.preview}" class="att-thumb" alt="${att.name}">` : `<span>${FILE_ICONS[att.kind]}</span>`}
      <span class="att-name">${att.name}</span>
      <span class="att-size">(${formatBytes(att.size)})</span>
    </div>`).join("")}</div>`;
}

// ── Typing indicator ──
function showTyping(type, status) {
  const agentColor = type === "laercio" ? "#f59e0b" : (activeAgent?.color || "#6366f1");
  const agentEmoji = type === "laercio" ? "🎯" : (activeAgent?.emoji || "");
  const el = document.getElementById(type === "laercio" ? "laercio-messages" : "agent-messages");
  const div = document.createElement("div");
  div.id = "typing-indicator";
  div.className = "msg msg-agent";
  div.innerHTML = `
    <div class="msg-avatar" style="background:${agentColor}30;border:1px solid ${agentColor}50">${agentEmoji}</div>
    <div class="msg-body">
      <div class="typing-dots">
        <div class="dot-bounce" style="background:${agentColor};animation-delay:0ms"></div>
        <div class="dot-bounce" style="background:${agentColor};animation-delay:150ms"></div>
        <div class="dot-bounce" style="background:${agentColor};animation-delay:300ms"></div>
      </div>
      ${status ? `<p class="typing-status">${status}</p>` : ""}
    </div>`;
  el.appendChild(div);
  el.scrollTop = el.scrollHeight;
}

function removeTyping() {
  document.getElementById("typing-indicator")?.remove();
}

function setLoadingStatus(status) {
  const el = document.querySelector("#typing-indicator .typing-status");
  if (el) el.textContent = status;
}

// ── Orchestration progress bar ──
function renderProgressBar(statuses) {
  const el = document.getElementById("laercio-progress");
  if (!statuses || Object.keys(statuses).length === 0) { el.style.display = "none"; return; }
  el.style.display = "block";
  el.innerHTML = `
    <div class="pb-title">🎯 Laércio acionando agentes em paralelo...</div>
    <div class="pb-grid">
      ${Object.entries(statuses).map(([id, status]) => {
        const a = AGENTS.find(ag => ag.id === parseInt(id));
        if (!a) return "";
        return `<div class="pb-item">
          <span>${a.emoji}</span>
          <span class="pb-name">${a.name}</span>
          ${status === "running" ? '<div class="spinner"></div>' : ""}
          ${status === "done"    ? '<span class="pb-done">✓</span>' : ""}
          ${status === "error"   ? '<span class="pb-err">✗</span>' : ""}
        </div>`;
      }).join("")}
    </div>`;
}

// ── Send Laércio ──
async function sendLaercio() {
  const ta = document.getElementById("laercio-textarea");
  const userText = ta.value.trim();
  if ((!userText && pendingFiles.length === 0) || loading) return;

  const userMsg = {
    role: "user",
    content: userText || (pendingFiles.length > 0 ? "Analise este(s) arquivo(s)." : ""),
    attachments: pendingFiles.length > 0 ? [...pendingFiles] : undefined,
  };
  laercioChats.push(userMsg);
  ta.value = "";
  clearPendingFiles();
  renderMessages("laercio");
  loading = true;
  updateSendBtns();

  const relevantIds = detectRelevantAgents(userText);
  const isBriefing  = /briefing|bom dia|status de todos/.test(userText.toLowerCase());
  const targetIds   = isBriefing ? [12, 4, 2, 3] : relevantIds;

  let finalContent = "";
  let usedAgentIds = [];

  if (targetIds.length >= 2) {
    const initStatuses = {};
    targetIds.forEach(id => { initStatuses[id] = "running"; });
    agentStatuses = initStatuses;
    renderProgressBar(agentStatuses);
    showTyping("laercio", `Acionando ${targetIds.length} agentes em paralelo...`);

    const results = await orchestrateAgents(targetIds, userText, (id, status) => {
      agentStatuses[id] = status;
      renderProgressBar(agentStatuses);
    });
    usedAgentIds = targetIds;

    const agentContext = results.map(({ agent, result, error }) =>
      `=== ${agent.emoji} ${agent.name} (${agent.title}) ===\n${error ? `ERRO: ${error}` : result}`
    ).join("\n\n");

    const synthesisMessages = [
      ...buildApiMessages(laercioChats),
      { role: "user", content: `Você acionou os seguintes agentes em paralelo e recebeu estas respostas:\n\n${agentContext}\n\nAgora sintetize tudo em uma resposta executiva consolidada para Ezequiel.` },
    ];
    setLoadingStatus("Consolidando respostas...");
    try { finalContent = await callClaude(synthesisMessages, LAERCIO.systemPrompt, LAERCIO.mcpServers); }
    catch (e) { finalContent = `Erro ao consolidar: ${e.message}`; }
    agentStatuses = {};
    renderProgressBar({});

  } else if (targetIds.length === 1) {
    const agent = AGENTS.find(a => a.id === targetIds[0]);
    agentStatuses = { [targetIds[0]]: "running" };
    renderProgressBar(agentStatuses);
    showTyping("laercio", `Consultando ${agent?.name}...`);

    const results = await orchestrateAgents(targetIds, userText, (id, status) => {
      agentStatuses[id] = status;
      renderProgressBar(agentStatuses);
    });
    usedAgentIds = targetIds;
    const r = results[0];
    const agentContext = `${r.agent.emoji} ${r.agent.name}: ${r.error ? `ERRO: ${r.error}` : r.result}`;
    const synthesisMessages = [
      ...buildApiMessages(laercioChats),
      { role: "user", content: `Resposta de ${r.agent.name}:\n\n${agentContext}\n\nComente brevemente e indique se o usuário deve continuar com ${r.agent.name} ou se outro agente pode ajudar.` },
    ];
    try { finalContent = await callClaude(synthesisMessages, LAERCIO.systemPrompt, LAERCIO.mcpServers); }
    catch (e) { finalContent = r.error ? `Erro: ${r.error}` : r.result; }
    agentStatuses = {};
    renderProgressBar({});

  } else {
    // Sem agente específico
    showTyping("laercio", "Processando com IA...");
    const apifyIntent = detectApifyIntent(userText);
    let webContext = "";
    if (apifyIntent) {
      try {
        const apifyData = await apifySearch(userText, apifyIntent, s => setLoadingStatus(s));
        webContext = `\n\n━━━ DADOS DA WEB ━━━\nBusca: "${apifyData.query}" · ${apifyData.timestamp}\n\n${formatApifyResults(apifyData)}\n━━━ FIM ━━━`;
      } catch {}
    }
    try {
      const apiMessages = buildApiMessages(laercioChats);
      if (webContext && apiMessages.length > 0) {
        const last = apiMessages[apiMessages.length - 1];
        apiMessages[apiMessages.length - 1] = { ...last, content: (typeof last.content === "string" ? last.content : "") + webContext };
      }
      finalContent = await callClaude(apiMessages, LAERCIO.systemPrompt, LAERCIO.mcpServers);
    } catch (e) { finalContent = `Erro: ${e.message}`; }
  }

  removeTyping();
  laercioChats.push({ role: "assistant", content: finalContent, agentIds: usedAgentIds });
  loading = false;
  updateSendBtns();
  renderMessages("laercio");
}

// ── Send regular agent ──
async function sendAgent() {
  if (!activeAgent) return;
  const ta = document.getElementById("agent-textarea");
  const userText = ta.value.trim();
  if ((!userText && pendingFiles.length === 0) || loading) return;

  const userMsg = {
    role: "user",
    content: userText || (pendingFiles.length > 0 ? "Analise o(s) arquivo(s) anexo(s)." : ""),
    attachments: pendingFiles.length > 0 ? [...pendingFiles] : undefined,
  };
  const currentHistory = chats[activeAgent.id] || [];
  const newHistory = [...currentHistory, userMsg];
  chats[activeAgent.id] = newHistory;
  ta.value = "";
  clearPendingFiles();
  renderMessages("agent");
  loading = true;
  updateSendBtns();
  showTyping("agent", "Consultando Microsoft 365...");

  try {
    const apifyIntent = detectApifyIntent(userText);
    let webContext = "";
    if (apifyIntent) {
      try {
        const apifyData = await apifySearch(userText, apifyIntent, s => setLoadingStatus(s));
        webContext = `\n\n━━━ DADOS DA WEB ━━━\n${formatApifyResults(apifyData)}\n━━━ FIM ━━━`;
      } catch {}
    }
    setLoadingStatus("Processando com IA...");
    const hasPdf = userMsg.attachments?.some(a => a.kind === "pdf");
    const apiMessages = buildApiMessages(newHistory);
    if (webContext && apiMessages.length > 0) {
      const last = apiMessages[apiMessages.length - 1];
      apiMessages[apiMessages.length - 1] = { ...last, content: (typeof last.content === "string" ? last.content : "") + webContext };
    }
    const reply = await callClaude(apiMessages, activeAgent.systemPrompt, activeAgent.mcpServers, [], hasPdf);
    chats[activeAgent.id] = [...newHistory, { role: "assistant", content: reply }];
  } catch (err) {
    chats[activeAgent.id] = [...newHistory, { role: "assistant", content: `Desculpe, ocorreu um erro.\n\nErro: ${err.message}` }];
  }

  removeTyping();
  loading = false;
  updateSendBtns();
  renderMessages("agent");
}

// ── Clear chats ──
function clearLaercioChat() {
  laercioChats = [];
  agentStatuses = {};
  renderProgressBar({});
  clearPendingFiles();
  document.getElementById("laercio-messages").innerHTML = "";
  setTimeout(() => openLaercio(), 100);
}
function clearAgentChat() {
  if (!activeAgent) return;
  chats[activeAgent.id] = [];
  clearPendingFiles();
  document.getElementById("agent-messages").innerHTML = "";
  setTimeout(() => openAgent(activeAgent), 100);
}

// ── File handling ──
function readFileAsBase64(file) {
  return new Promise((res, rej) => {
    const r = new FileReader();
    r.onload = () => res(r.result.split(",")[1]);
    r.onerror = () => rej(new Error("Falha ao ler arquivo"));
    r.readAsDataURL(file);
  });
}
function readFileAsText(file) {
  return new Promise((res, rej) => {
    const r = new FileReader();
    r.onload = () => res(r.result);
    r.onerror = () => rej(new Error("Falha ao ler arquivo"));
    r.readAsText(file, "UTF-8");
  });
}
function formatBytes(bytes) {
  if (bytes < 1024) return bytes + " B";
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
  return (bytes / (1024 * 1024)).toFixed(1) + " MB";
}

async function processFile(file) {
  const normalizedType = file.type || (file.name.endsWith(".pdf") ? "application/pdf"
    : file.name.endsWith(".csv") ? "text/csv"
    : file.name.endsWith(".md") ? "text/markdown"
    : file.name.endsWith(".json") ? "application/json"
    : "text/plain");
  const kind = ACCEPTED_TYPES[normalizedType] || ACCEPTED_TYPES[file.type];
  if (!kind) { alert(`Tipo não suportado: ${file.name}`); return null; }
  const base = { name: file.name, size: file.size, mimeType: normalizedType, kind };
  try {
    if (kind === "image") {
      const data = await readFileAsBase64(file);
      return { ...base, data, preview: URL.createObjectURL(file) };
    } else if (kind === "pdf") {
      const data = await readFileAsBase64(file);
      const cleanData = data.includes(",") ? data.split(",")[1] : data;
      return { ...base, mimeType: "application/pdf", data: cleanData };
    } else {
      const text = await readFileAsText(file);
      return { ...base, text: text || "" };
    }
  } catch (e) { alert(`Erro ao processar ${file.name}: ${e.message}`); return null; }
}

async function handleFileInput(e) {
  const files = e.target.files;
  if (!files.length) return;
  const results = await Promise.all(Array.from(files).map(processFile));
  const valid = results.filter(Boolean);
  if (valid.length) {
    pendingFiles = [...pendingFiles, ...valid];
    renderPendingFiles();
    updateSendBtns();
  }
  e.target.value = "";
}

function removePending(idx) {
  if (pendingFiles[idx]?.preview) URL.revokeObjectURL(pendingFiles[idx].preview);
  pendingFiles.splice(idx, 1);
  renderPendingFiles();
  updateSendBtns();
}

function clearPendingFiles() {
  pendingFiles.forEach(f => { if (f.preview) URL.revokeObjectURL(f.preview); });
  pendingFiles = [];
  renderPendingFiles();
}

function renderPendingFiles() {
  const type = laercioActive ? "laercio" : "agent";
  const el = document.getElementById(`${type}-pending-files`);
  if (!el) return;
  if (pendingFiles.length === 0) { el.style.display = "none"; return; }
  el.style.display = "flex";
  el.innerHTML = `<span class="pf-label">Arquivos para enviar:</span>` +
    pendingFiles.map((att, i) => `
      <div class="att-pill">
        ${att.kind === "image" && att.preview ? `<img src="${att.preview}" class="att-thumb" alt="${att.name}">` : `<span>${FILE_ICONS[att.kind]}</span>`}
        <span class="att-name">${att.name}</span>
        <span class="att-size">(${formatBytes(att.size)})</span>
        <button class="att-remove" onclick="removePending(${i})">×</button>
      </div>`).join("");
}

// ── Drag & drop ──
function onDragOver(e) { e.preventDefault(); }
function onDragLeave() {}
async function onDrop(e) {
  e.preventDefault();
  const files = e.dataTransfer.files;
  if (!files.length) return;
  const results = await Promise.all(Array.from(files).map(processFile));
  const valid = results.filter(Boolean);
  if (valid.length) {
    pendingFiles = [...pendingFiles, ...valid];
    renderPendingFiles();
    updateSendBtns();
  }
}

// ── UI helpers ──
function setLoading(isLoading, status, type) {
  loading = isLoading;
  if (isLoading) showTyping(type, status);
  else removeTyping();
  updateSendBtns();
}

function updateSendBtns() {
  const laercioBtn = document.getElementById("laercio-send-btn");
  const agentBtn   = document.getElementById("agent-send-btn");
  const laercioTa  = document.getElementById("laercio-textarea");
  const agentTa    = document.getElementById("agent-textarea");

  const canLaercio = (laercioTa?.value.trim().length > 0 || pendingFiles.length > 0) && !loading;
  const canAgent   = (agentTa?.value.trim().length > 0 || pendingFiles.length > 0) && !loading && !!activeAgent;

  if (laercioBtn) {
    laercioBtn.disabled = !canLaercio;
    laercioBtn.style.background = canLaercio ? "linear-gradient(135deg,#f59e0b,#b45309)" : "rgba(255,255,255,0.1)";
  }
  if (agentBtn && activeAgent) {
    agentBtn.disabled = !canAgent;
    agentBtn.style.background = canAgent ? `linear-gradient(135deg,${activeAgent.color},${activeAgent.colorDark})` : "rgba(255,255,255,0.1)";
  }
}

function handleKey(e, type) {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    if (type === "laercio") sendLaercio();
    else sendAgent();
  }
  updateSendBtns();
}

function escapeHtml(text) {
  if (!text) return "";
  return text.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;")
    .replace(/"/g,"&quot;").replace(/\n/g,"<br>");
}

// ── Apify config ──
function updateApifyUI() {
  const hasBadge = !!apifyKey;
  document.getElementById("apify-badge").style.display = hasBadge ? "flex" : "none";
  document.getElementById("home-apify-active").style.display = hasBadge ? "flex" : "none";
  document.getElementById("home-apify-config-btn").style.display = hasBadge ? "none" : "";
  document.getElementById("apify-dot").style.display = hasBadge ? "block" : "none";
  document.getElementById("apify-btn-title").style.color = hasBadge ? "#fb923c" : "rgba(255,255,255,0.4)";
  document.getElementById("apify-btn-sub").textContent = hasBadge ? "✅ Ativo · Busca web habilitada" : "⚙️ Clique para configurar";
}

function toggleApifyConfig() {
  const panel = document.getElementById("apify-config-panel");
  if (panel.style.display === "none" || !panel.innerHTML) {
    panel.style.display = "block";
    panel.innerHTML = `
      <div class="apify-panel">
        <div class="ap-header">
          <span>🕷️ Apify Web Intelligence</span>
          <button onclick="toggleApifyConfig()">×</button>
        </div>
        <div class="ap-body">
          <input type="password" id="apify-key-input" placeholder="${apifyKey ? "••••••••••••••" : "apify_api_xxxxx (opcional)"}"
            class="apify-key-input" oninput="" />
          <div class="ap-btns">
            <button class="ap-save-btn" onclick="saveApifyKey()">
              ${apifyKey ? "Atualizar chave" : "Salvar chave"}
            </button>
            ${apifyKey ? `<button class="ap-clear-btn" onclick="clearApifyKey()">Remover</button>` : ""}
          </div>
          <a href="https://console.apify.com/account/integrations" target="_blank" class="ap-link">→ Obter chave em console.apify.com ↗</a>
        </div>
      </div>`;
  } else {
    panel.style.display = "none";
  }
}

function saveApifyKey() {
  const val = document.getElementById("apify-key-input")?.value.trim() || "";
  if (!val) return;
  apifyKey = val;
  try { localStorage.setItem("apify_key", val); } catch {}
  updateApifyUI();
  document.getElementById("apify-config-panel").style.display = "none";
}
function clearApifyKey() {
  apifyKey = "";
  try { localStorage.removeItem("apify_key"); } catch {}
  updateApifyUI();
  document.getElementById("apify-config-panel").style.display = "none";
}
