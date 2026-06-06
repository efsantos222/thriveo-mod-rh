<div class="glass-card">
    <h2>Formatador de Currículos com IA</h2>
    <p>Importe um PDF, defina as regras e deixe a IA padronizar o documento.</p>

    <!-- Step 1: Upload -->
    <div class="upload-area" id="drop-zone">
        <input type="file" id="file-input" accept="application/pdf" style="display: none;">
        <div style="font-size: 3rem; color: var(--primary);">📄</div>
        <h3 style="margin: 1rem 0;">Clique ou arraste seu arquivo PDF aqui</h3>
        <p id="file-name" style="margin:0; font-weight: 600; color:var(--accent);">Nenhum arquivo selecionado</p>
    </div>

    <!-- Step 2: Configuration -->
    <!-- Step 2: Configuration -->
    <div style="margin-top: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
            <h3>Estrutura e Ordem (Arraste para Reordenar)</h3>
            <div class="config-list" id="sortable-list">
                <div class="config-item" data-id="personal"><span class="drag-handle">☰</span> <input type="checkbox"
                        checked id="sec-personal"> <label>Dados Pessoais</label></div>
                <div class="config-item" data-id="summary"><span class="drag-handle">☰</span> <input type="checkbox"
                        checked id="sec-summary"> <label>Resumo Profissional</label></div>
                <div class="config-item" data-id="experience"><span class="drag-handle">☰</span> <input type="checkbox"
                        checked id="sec-exp"> <label>Experiência Profissional</label></div>
                <div class="config-item" data-id="education"><span class="drag-handle">☰</span> <input type="checkbox"
                        checked id="sec-edu"> <label>Formação Acadêmica</label></div>
                <div class="config-item" data-id="skills"><span class="drag-handle">☰</span> <input type="checkbox"
                        checked id="sec-skills"> <label>Habilidades Técnicas</label></div>
                <div class="config-item" data-id="tech_competencies"><span class="drag-handle">☰</span> <input
                        type="checkbox" id="sec-tech-comp"> <label>Competências Técnicas</label></div>
                <div class="config-item" data-id="certifications"><span class="drag-handle">☰</span> <input
                        type="checkbox" id="sec-certs"> <label>Certificações</label></div>
                <div class="config-item" data-id="languages"><span class="drag-handle">☰</span> <input type="checkbox"
                        checked id="sec-langs"> <label>Idiomas</label></div>
            </div>
        </div>
        <div>
            <h3>Regras de Exibição</h3>
            <div class="option-group">
                <input type="checkbox" id="anon-contact"> <label for="anon-contact">Ocultar Contatos Pessoais
                    (LGPD)</label>
            </div>
            <div class="option-group">
                <input type="checkbox" checked id="format-name"> <label for="format-name">Simplificar Nome (Ex: João S.)
                    - Cor Preta</label>
            </div>
            <div class="option-group">
                <input type="checkbox" id="remove-photo"> <label for="remove-photo">Remover menções a
                    foto/imagem</label>
            </div>
            <div class="option-group">
                <input type="checkbox" id="standard-date"> <label for="standard-date">Padronizar Datas (MM/AAAA)</label>
            </div>
            <div class="option-group">
                <input type="checkbox" checked id="fix-grammar"> <label for="fix-grammar">Corrigir Gramática e
                    Ortografia</label>
            </div>

            <h3>Personalização Visual</h3>
            <div class="option-group" style="display: block;">
                <label style="display:block; margin-bottom: 0.5rem; color: var(--text-muted);">Logo da Empresa:</label>
                <input type="file" id="logo-input" accept="image/*"
                    style="width: 100%; padding: 0.5rem; background: rgba(255,255,255,0.05); border-radius: 4px; color: white;">
            </div>
        </div>
    </div>

    <!-- Action -->
    <div style="margin-top: 2rem; text-align: right;">
        <button id="btn-generate" class="btn btn-primary" disabled>
            ⚡ Processar e Formatar com IA
        </button>
    </div>
</div>

<!-- Step 3: Preview -->
<div class="glass-card" style="margin-top: 2rem;" id="preview-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Pré-visualização</h3>
        <button class="btn btn-primary" onclick="printCV()">🖨️ Exportar PDF</button>
    </div>
    <div id="cv-content" class="cv-preview">
        <p style="text-align: center; padding: 4rem; opacity: 0.5;">O currículo formatado aparecerá aqui...</p>
    </div>
</div>

<!-- SortableJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<!-- HTML2PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    .config-item {
        background: rgba(255, 255, 255, 0.05);
        padding: 0.8rem;
        margin-bottom: 0.5rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        cursor: move;
    }

    .drag-handle {
        cursor: grab;
        color: var(--text-muted);
    }

    .option-group {
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    input[type="checkbox"] {
        width: 1.2rem;
        height: 1.2rem;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .cv-preview {
        background: white;
        padding: 4rem;
        /* A4 padding */
        min-height: 800px;
        border-radius: 4px;
        font-family: 'Arial', sans-serif;
        /* Changed to Arial */
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    }

    /* Force Black on ALL elements inside the CV preview AND print */
    #cv-content,
    #cv-content * {
        color: #000000 !important;
        text-shadow: none !important;
        font-family: 'Arial', sans-serif !important;
    }

    /* Enforce Headers Size and Override Global Gradients */
    #cv-content h1,
    #cv-content h1 * {
        font-size: 42px !important;
        /* Increased proportionally */
        font-weight: 900 !important;
        margin-bottom: 0.5rem !important;
        color: #000000 !important;
        background: none !important;
        -webkit-text-fill-color: #000000 !important;
        -webkit-background-clip: border-box !important;
    }

    #cv-content h2,
    #cv-content h2 * {
        font-size: 26px !important;
        /* Increased proportionally */
        font-weight: 700 !important;
        text-transform: uppercase !important;
        border-bottom: 2px solid #000000 !important;
        margin-top: 1.5rem !important;
        color: #000000 !important;
    }

    #cv-content p,
    #cv-content li,
    #cv-content span,
    #cv-content div {
        font-size: 16px !important;
        /* Increased to approx 12pt */
        line-height: 1.5 !important;
        color: #000000 !important;
    }

    /* Separator for Job Items */
    .job-item {
        border-bottom: 1px solid #000000;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
        page-break-inside: avoid; /* Prevent splitting a job across pages if possible */
    }
    .job-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    /* Print Styles for A4 */
    @media print {
        @page {
            size: A4;
            margin: 20mm;
        }
        body * { visibility: hidden; }
        #cv-content, #cv-content * { visibility: visible; }
        #cv-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0;
            margin: 0;
            box-shadow: none;
            font-size: 11pt !important; /* Optimal read size */
        }

        /* Ensure headers don't break awkwardly */
        h1, h2, h3 { page-break-after: avoid; }

        /* Ensure list items are clean */
        li { page-break-inside: avoid; }
    }
</style>

<script>
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');
    const fileName = document.getElementById('file-name');
    const generateBtn = document.getElementById('btn-generate');
    const logoInput = document.getElementById('logo-input');
    let extractedText = "";
    let logoBase64 = "";

    // Handle Logo Upload
    logoInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                logoBase64 = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Initialize Sortable
    new Sortable(document.getElementById('sortable-list'), {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost'
    });

    // Drag and Drop
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.background = 'rgba(255,255,255,0.05)'; });
    dropZone.addEventListener('dragleave', () => { dropZone.style.background = 'transparent'; });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.background = 'transparent';
        if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) handleFile(e.target.files[0]);
    });

    async function handleFile(file) {
        if (file.type !== 'application/pdf') {
            alert('Por favor, envie apenas arquivos PDF.');
            return;
        }
        fileName.textContent = file.name + ' (Processando...)';

        try {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;

            let fullText = "";
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const textContent = await page.getTextContent();
                fullText += textContent.items.map(item => item.str).join(" ") + "\n";
            }

            extractedText = fullText;
            fileName.textContent = file.name + ' (Pronto para processar)';
            fileName.style.color = '#88ff88';
            generateBtn.disabled = false;
        } catch (err) {
            console.error(err);
            fileName.textContent = 'Erro ao ler PDF';
            fileName.style.color = '#ff8888';
        }
    }

    generateBtn.addEventListener('click', async () => {
        if (!extractedText) return;

        generateBtn.disabled = true;
        generateBtn.innerHTML = '⚡ Processando... (Isso pode levar até 1 minuto)';
        const cvContent = document.getElementById('cv-content');
        cvContent.innerHTML = '<div style="text-align:center; padding-top: 50px;">🤖 A IA está analisando e formatando o currículo...</div>';

        // Gather Options

        // Get Order
        const orderItems = document.querySelectorAll('.config-item');
        let sectionOrder = [];
        orderItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox.checked) {
                sectionOrder.push(item.getAttribute('data-id'));
            }
        });

        const options = {
            section_order: sectionOrder,
            format_name: document.getElementById('format-name').checked,
            anon_contact: document.getElementById('anon-contact').checked,
            include_personal: document.getElementById('sec-personal').checked,
            include_summary: document.getElementById('sec-summary').checked,
            include_exp: document.getElementById('sec-exp').checked,
            include_edu: document.getElementById('sec-edu').checked,
            include_skills: document.getElementById('sec-skills').checked,
            include_tech_competencies: document.getElementById('sec-tech-comp').checked,
            include_certifications: document.getElementById('sec-certs').checked,
            include_langs: document.getElementById('sec-langs').checked,
            remove_photo: document.getElementById('remove-photo').checked,
            standard_date: document.getElementById('standard-date').checked,
            fix_grammar: document.getElementById('fix-grammar').checked
        };

        try {
            const response = await fetch('api/process_cv.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    text: extractedText,
                    options: options
                })
            });

            const data = await response.json();

            if (data.success) {
                let finalHtml = data.html;

                // Inject Logo if present
                if (logoBase64) {
                    const logoHtml = `<div style="margin-bottom: 2rem;"><img src="${logoBase64}" style="max-height: 80px; max-width: 250px; display: block;"></div>`;

                    // Try to inject inside the main container if possible, otherwise prepend
                    if (finalHtml.includes('<div class="cv-document">')) {
                        finalHtml = finalHtml.replace('<div class="cv-document">', `<div class="cv-document">${logoHtml}`);
                    } else if (finalHtml.includes("<div class='cv-document'>")) {
                        finalHtml = finalHtml.replace("<div class='cv-document'>", `<div class='cv-document'>${logoHtml}`);
                    } else {
                        // Fallback
                        finalHtml = logoHtml + finalHtml;
                    }
                }

                cvContent.innerHTML = finalHtml;
            } else {
                cvContent.innerHTML = `<div style="color: red; text-align:center;">Erro: ${data.error}</div>`;
            }
        } catch (err) {
            cvContent.innerHTML = `<div style="color: red; text-align:center;">Erro de conexão.</div>`;
        }

        generateBtn.disabled = false;
        generateBtn.innerHTML = '⚡ Processar e Formatar com IA';
    });

    function printCV() {
        const element = document.getElementById('cv-content');
        
        // Save original styles
        const originalPadding = element.style.padding;
        const originalShadow = element.style.boxShadow;
        const originalMinHeight = element.style.minHeight;
        
        // Prepare element for export: remove padding (let PDF margins handle it) and shadows
        element.style.padding = '0px';
        element.style.boxShadow = 'none';
        element.style.minHeight = 'auto'; // Prevent forcing height

        // Configuration for html2pdf
        const opt = {
            margin:       [15, 10, 15, 10], // Top, Right, Bottom, Left (mm)
            filename:     'Curriculo_Formatado.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { 
                scale: 2, 
                useCORS: true, 
                scrollY: 0,
                letterRendering: true
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
        };

        // Generate PDF and then restore styles
        html2pdf().set(opt).from(element).save().then(() => {
            element.style.padding = originalPadding;
            element.style.boxShadow = originalShadow;
            element.style.minHeight = originalMinHeight;
        }).catch(err => {
            console.error(err);
            // Restore styles even on error
            element.style.padding = originalPadding;
            element.style.boxShadow = originalShadow;
            element.style.minHeight = originalMinHeight;
        });
    }
</script>