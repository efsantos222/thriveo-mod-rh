document.addEventListener('DOMContentLoaded', loadCompanies);

function loadCompanies() {
    const grid = document.getElementById('companiesGrid');
    grid.innerHTML = '<p>Carregando...</p>';

    fetch('../api/companies/list.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCompanies(data.data);
            } else {
                grid.innerHTML = `<p class="error">Erro: ${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            grid.innerHTML = '<p class="error">Erro ao carregar empresas.</p>';
        });
}

function renderCompanies(companies) {
    const grid = document.getElementById('companiesGrid');
    
    if (companies.length === 0) {
        grid.innerHTML = '<p>Nenhuma empresa encontrada.</p>';
        return;
    }

    grid.innerHTML = companies.map(company => `
        <div class="user-card">
            <div class="user-header">
                <div class="user-avatar">
                    <i class="fas fa-building"></i>
                </div>
                <div class="user-basic-info">
                    <h3>${company.nome_empresa}</h3>
                    <p>${company.cnpj || 'CNPJ não informado'}</p>
                </div>
            </div>
            <div class="user-details">
                <p><i class="fas fa-calendar"></i> Criado em: ${new Date(company.data_criacao).toLocaleDateString()}</p>
            </div>
            <div class="user-actions">
                <button class="btn-icon" onclick="deleteCompany(${company.id_empresa})" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function openNewCompanyModal() {
    document.getElementById('newCompanyModal').style.display = 'block';
}

function closeNewCompanyModal() {
    document.getElementById('newCompanyModal').style.display = 'none';
    document.getElementById('newCompanyForm').reset();
}

function submitNewCompany(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {
        nome_empresa: formData.get('nome_empresa'),
        cnpj: formData.get('cnpj')
    };

    fetch('../api/companies/create.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            closeNewCompanyModal();
            loadCompanies();
            // Optional: Show success message
        } else {
            alert('Erro: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao criar empresa.');
    });
}

function deleteCompany(id) {
    if (!confirm('Tem certeza que deseja excluir esta empresa?')) return;

    fetch('../api/companies/delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id_empresa: id })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadCompanies();
        } else {
            alert('Erro: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao excluir empresa.');
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('newCompanyModal');
    if (event.target == modal) {
        closeNewCompanyModal();
    }
}
