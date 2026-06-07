// Função para limpar campos do formulário
function clearFormFields() {
    // Limpar campos de texto, email e password
    document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]').forEach(input => {
        input.value = '';
    });

    // Limpar campos select
    document.querySelectorAll('select').forEach(select => {
        select.selectedIndex = 0;
    });

    // Limpar campos textarea
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.value = '';
    });

    // Remover classes de validação
    document.querySelectorAll('.was-validated').forEach(form => {
        form.classList.remove('was-validated');
    });
}

// Função para alternar visibilidade da senha
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.querySelector(`[data-password-toggle="${inputId}"]`);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Limpar campos ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    clearFormFields();
});

// Limpar campos ao abrir modais
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            const forms = this.querySelectorAll('form');
            forms.forEach(form => {
                form.reset();
                form.classList.remove('was-validated');
            });
        });
    });
});

// Limpar formulário após submissão bem-sucedida
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            // Verificar se há erro na URL após o redirecionamento
            const checkForError = setInterval(() => {
                const urlParams = new URLSearchParams(window.location.search);
                if (!urlParams.has('error')) {
                    // Se não houver erro, limpar o formulário
                    this.reset();
                    // Limpar campos ocultos também
                    this.querySelectorAll('input[type="hidden"]').forEach(input => {
                        input.value = '';
                    });
                }
                clearInterval(checkForError);
            }, 100);
        });
    });
});
