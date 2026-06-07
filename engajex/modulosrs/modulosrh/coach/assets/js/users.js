document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    if (typeof USER_PROFILE !== 'undefined' && USER_PROFILE === 'administrador') {
        loadCompaniesDropdown();
    }
});

function loadUsers() {
    const grid = document.getElementById('usersGrid');
    grid.innerHTML = '<p>Carregando...</p>';

    const search = document.getElementById('searchUser').value;
    const filterProfile = document.getElementById('filterProfile') ? document.getElementById('filterProfile').value : '';

    // Build query params if needed (Currently search is client-side implementation typically, but let's assume API returns all and we filter JS side or implement searching later. The current API list.php returns all)
    // Note: To implement real search, we'd pass query params to API.

    fetch('../api/users/list.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderUsers(data.data, search, filterProfile);
            } else {
                grid.innerHTML = `<p class="error">Erro: ${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            grid.innerHTML = '<p class="error">Erro ao carregar usuários.</p>';
        });
}

// Add event listeners for filters
const searchInput = document.getElementById('searchUser');
if (searchInput) {
    searchInput.addEventListener('input', loadUsers);
}
const profileFilter = document.getElementById('filterProfile');
if (profileFilter) {
    profileFilter.addEventListener('change', loadUsers);
}

function renderUsers(users, searchTerm, filterProfile) {
    const grid = document.getElementById('usersGrid');

    // Client-side filtering
    const filtered = users.filter(user => {
        const matchesSearch = user.nome.toLowerCase().includes(searchTerm.toLowerCase()) ||
            user.email.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesProfile = filterProfile ? user.perfil === filterProfile : true;
        return matchesSearch && matchesProfile;
    });

    if (filtered.length === 0) {
        grid.innerHTML = '<p>Nenhum usuário encontrado.</p>';
        return;
    }

    grid.innerHTML = filtered.map(user => `
        <div class="user-card">
            <div class="user-header">
                <div class="user-avatar">
                   <i class="fas fa-user"></i>
                </div>
                <div class="user-basic-info">
                    <h3>${user.nome}</h3>
                    <p>${user.email}</p>
                    <span class="user-role role-${user.perfil}">${formatProfile(user.perfil)}</span>
                </div>
            </div>
            <div class="user-details">
                ${user.nome_empresa ? `<p><i class="fas fa-building"></i> ${user.nome_empresa}</p>` : ''}
                <p><i class="fas fa-calendar"></i> Criado em: ${new Date(user.data_criacao).toLocaleDateString()}</p>
            </div>
            <div class="user-actions">
                ${canEdit(user) ? `
                <button class="btn-icon" onclick='openEditUserModal(${JSON.stringify(user).replace(/'/g, "&#39;")})' title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon" onclick="deleteUser(${user.id_usuario})" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
                ` : ''}
            </div>
        </div>
    `).join('');
}

function formatProfile(profile) {
    const map = {
        'administrador': 'Administrador',
        'master_coach': 'Master Coach',
        'coach': 'Coach',
        'coachee': 'Coachee',
        'colaborador': 'Colaborador',
        'gestor': 'Gestor'
    };
    return map[profile] || profile;
}

function canEdit(user) {
    // Logic to determine if current user can edit this user
    // Assuming backend prohibits deletion of self or unauthorized already, but UI should also be smart.
    return true;
}

function loadCompaniesDropdown() {
    const select = document.getElementById('id_empresa');
    if (!select) return;

    fetch('../api/companies/list.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let options = '<option value="">Nenhuma / Global</option>';
                data.data.forEach(c => {
                    options += `<option value="${c.id_empresa}">${c.nome_empresa}</option>`;
                });
                select.innerHTML = options;
            }
        });
}

function toggleCompanySelect() {
    const profile = document.getElementById('perfil').value;
    const group = document.getElementById('companyGroup');
    if (!group) return;

    // Show company select for Master Coach, Coachee, or when creating a user that belongs to a company
    if (['master_coach', 'coachee', 'colaborador', 'gestor', 'coach'].includes(profile)) {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
    }
}

function openEditUserModal(user) {
    const modal = document.getElementById('newUserModal');
    const form = document.getElementById('newUserForm');

    // Clear form
    form.reset();

    // Fill data
    document.getElementById('nome').value = user.nome;
    document.getElementById('email').value = user.email;
    const senhaInput = document.getElementById('senha');
    senhaInput.placeholder = "(Deixe em branco para manter)";
    senhaInput.required = false;

    // Handle profile select if it exists (Admin view)
    const perfilSelect = document.getElementById('perfil');
    if (perfilSelect) {
        perfilSelect.value = user.perfil;
        toggleCompanySelect(); // Show company field if needed

        // Wait for company dropdown to populate or set it directly if already loaded
        setTimeout(() => {
            const companySelect = document.getElementById('id_empresa');
            if (companySelect && user.id_empresa) {
                companySelect.value = user.id_empresa;
            }
        }, 100);
    }

    // Change form submission to update instead of create
    form.onsubmit = function (e) {
        submitEditUser(e, user.id_usuario);
    };

    // Change Title
    modal.querySelector('h2').textContent = 'Editar Usuário';

    modal.style.display = 'block';
}

function submitEditUser(event, id) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    data.id_usuario = id;

    if (!data.senha) delete data.senha; // Don't send empty password

    fetch('../api/users/update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                closeNewUserModal();
                loadUsers();
                alert('Usuário atualizado com sucesso!');
            } else {
                alert('Erro: ' + result.message);
            }
        });
}

function openNewUserModal() {
    document.getElementById('newUserModal').style.display = 'block';
}

function closeNewUserModal() {
    document.getElementById('newUserModal').style.display = 'none';
    document.getElementById('newUserForm').reset();
}

function submitNewUser(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    fetch('../api/users/create.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                closeNewUserModal();
                loadUsers();
                alert('Usuário criado com sucesso!');
            } else {
                alert('Erro: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao criar usuário.');
        });
}

function deleteUser(id) {
    if (!confirm('Tem certeza que deseja excluir este usuário?')) return;

    // Note: Assuming delete API exists or needs to be implemented/used
    // Using generic implementation assumption
    fetch('../api/users/delete.php', { // Assuming you have/will have this
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_usuario: id })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadUsers();
            else alert(data.message);
        });
}

// Close modal
window.onclick = function (event) {
    const modal = document.getElementById('newUserModal');
    if (event.target == modal) {
        closeNewUserModal();
    }
}
