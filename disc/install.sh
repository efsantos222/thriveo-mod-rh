#!/bin/bash

# Script de Instalação Automática - Sistema DISC
# Para Servidores Locaweb

echo "========================================================================"
echo "        Sistema de Avaliação DISC - Instalação Automática"
echo "========================================================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para mostrar mensagens
print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${YELLOW}ℹ${NC} $1"
}

# Verificar se está executando como root
if [ "$EUID" -eq 0 ]; then
    print_info "Executando como root. Recomendamos criar um usuário específico."
fi

# Passo 1: Verificar Python
echo ""
echo "Passo 1: Verificando Python..."
echo "----------------------------------------"

if command -v python3 &> /dev/null; then
    PYTHON_VERSION=$(python3 --version 2>&1 | awk '{print $2}')
    print_success "Python3 encontrado: $PYTHON_VERSION"
else
    print_error "Python3 não encontrado!"
    echo "Instalando Python3..."

    if command -v yum &> /dev/null; then
        sudo yum install python3 python3-pip -y
    elif command -v apt-get &> /dev/null; then
        sudo apt-get update
        sudo apt-get install python3 python3-pip -y
    else
        print_error "Gerenciador de pacotes não identificado. Instale Python3 manualmente."
        exit 1
    fi
fi

# Passo 2: Verificar pip
echo ""
echo "Passo 2: Verificando pip..."
echo "----------------------------------------"

if command -v pip3 &> /dev/null; then
    print_success "pip3 encontrado"
else
    print_error "pip3 não encontrado. Instalando..."
    python3 -m ensurepip --upgrade
fi

# Passo 3: Criar diretório de instalação
echo ""
echo "Passo 3: Configurando diretórios..."
echo "----------------------------------------"

read -p "Diretório de instalação [~/disc_assessment]: " INSTALL_DIR
INSTALL_DIR=${INSTALL_DIR:-~/disc_assessment}

if [ -d "$INSTALL_DIR" ]; then
    print_info "Diretório já existe: $INSTALL_DIR"
    read -p "Deseja sobrescrever? (s/n): " OVERWRITE
    if [ "$OVERWRITE" != "s" ]; then
        print_error "Instalação cancelada."
        exit 1
    fi
else
    mkdir -p "$INSTALL_DIR"
    print_success "Diretório criado: $INSTALL_DIR"
fi

# Passo 4: Verificar se já está no diretório com os arquivos
echo ""
echo "Passo 4: Verificando arquivos..."
echo "----------------------------------------"

CURRENT_DIR=$(pwd)
if [ -f "$CURRENT_DIR/main.py" ] && [ -f "$CURRENT_DIR/disc_test.py" ]; then
    print_success "Arquivos encontrados no diretório atual"
    INSTALL_DIR=$CURRENT_DIR
else
    print_info "Arquivos não encontrados no diretório atual"
    echo "Você precisa:"
    echo "  1. Clonar o repositório: git clone https://github.com/efsantos222/PromptMaster.git"
    echo "  2. Ou fazer upload manual dos arquivos via FTP"
    echo ""
    read -p "Deseja clonar o repositório agora? (s/n): " CLONE

    if [ "$CLONE" = "s" ]; then
        if command -v git &> /dev/null; then
            cd $(dirname "$INSTALL_DIR")
            git clone https://github.com/efsantos222/PromptMaster.git
            INSTALL_DIR="$(dirname "$INSTALL_DIR")/PromptMaster/disc_assessment"
            print_success "Repositório clonado"
        else
            print_error "Git não instalado. Instalando..."
            if command -v yum &> /dev/null; then
                sudo yum install git -y
            elif command -v apt-get &> /dev/null; then
                sudo apt-get install git -y
            fi
            cd $(dirname "$INSTALL_DIR")
            git clone https://github.com/efsantos222/PromptMaster.git
            INSTALL_DIR="$(dirname "$INSTALL_DIR")/PromptMaster/disc_assessment"
        fi
    else
        print_error "Instalação cancelada. Faça o upload dos arquivos primeiro."
        exit 1
    fi
fi

cd "$INSTALL_DIR"

# Passo 5: Configurar permissões
echo ""
echo "Passo 5: Configurando permissões..."
echo "----------------------------------------"

chmod +x main.py 2>/dev/null
chmod +x test_disc.py 2>/dev/null
chmod +x example_usage.py 2>/dev/null
chmod +x quick_demo.py 2>/dev/null
chmod +x web_app.py 2>/dev/null
chmod 755 .

print_success "Permissões configuradas"

# Passo 6: Testar instalação
echo ""
echo "Passo 6: Testando instalação..."
echo "----------------------------------------"

print_info "Executando testes..."
if python3 test_disc.py > /tmp/disc_test_output.txt 2>&1; then
    print_success "Testes executados com sucesso!"
else
    print_error "Alguns testes falharam. Verifique /tmp/disc_test_output.txt"
fi

# Passo 7: Perguntar sobre aplicação web
echo ""
echo "Passo 7: Configuração da aplicação web (opcional)..."
echo "----------------------------------------"

read -p "Deseja instalar a versão web? (s/n): " INSTALL_WEB

if [ "$INSTALL_WEB" = "s" ]; then
    print_info "Instalando Flask..."
    pip3 install flask

    print_success "Flask instalado"
    print_info "Para iniciar o servidor web, execute:"
    echo "      cd $INSTALL_DIR"
    echo "      python3 web_app.py"
    echo ""
    echo "  Acesse: http://seu-ip:5000"
fi

# Passo 8: Criar diretório para resultados
echo ""
echo "Passo 8: Criando diretórios auxiliares..."
echo "----------------------------------------"

mkdir -p ~/disc_results
chmod 700 ~/disc_results
print_success "Diretório de resultados criado: ~/disc_results"

# Passo 9: Configurar variáveis de ambiente
echo ""
echo "Passo 9: Configurando variáveis de ambiente..."
echo "----------------------------------------"

if ! grep -q "DISC_ASSESSMENT_HOME" ~/.bashrc 2>/dev/null; then
    echo "" >> ~/.bashrc
    echo "# DISC Assessment System" >> ~/.bashrc
    echo "export DISC_ASSESSMENT_HOME=\"$INSTALL_DIR\"" >> ~/.bashrc
    echo "export PATH=\"\$PATH:\$DISC_ASSESSMENT_HOME\"" >> ~/.bashrc
    echo "export PYTHONPATH=\"\$PYTHONPATH:\$DISC_ASSESSMENT_HOME\"" >> ~/.bashrc
    print_success "Variáveis adicionadas ao ~/.bashrc"
else
    print_info "Variáveis já configuradas"
fi

# Passo 10: Executar demo
echo ""
echo "Passo 10: Executando demonstração..."
echo "----------------------------------------"

read -p "Deseja executar o demo agora? (s/n): " RUN_DEMO

if [ "$RUN_DEMO" = "s" ]; then
    python3 main.py --demo
fi

# Resumo da instalação
echo ""
echo "========================================================================"
echo "                    INSTALAÇÃO CONCLUÍDA COM SUCESSO!"
echo "========================================================================"
echo ""
print_success "Sistema instalado em: $INSTALL_DIR"
print_success "Resultados serão salvos em: ~/disc_results"
echo ""
echo "📚 Como usar:"
echo "----------------------------------------"
echo "  1. Modo CLI (linha de comando):"
echo "     cd $INSTALL_DIR"
echo "     python3 main.py"
echo ""
echo "  2. Modo Demo:"
echo "     python3 main.py --demo"
echo ""
echo "  3. Ver exemplos:"
echo "     python3 example_usage.py"
echo ""

if [ "$INSTALL_WEB" = "s" ]; then
    echo "  4. Modo Web:"
    echo "     python3 web_app.py"
    echo "     Acesse: http://seu-ip:5000"
    echo ""
fi

echo "📖 Documentação:"
echo "----------------------------------------"
echo "  README.md              - Documentação completa"
echo "  INSTALACAO_SERVIDOR.md - Guia de instalação detalhado"
echo ""
echo "🧪 Testes:"
echo "----------------------------------------"
echo "  python3 test_disc.py   - Executar testes unitários"
echo "  python3 quick_demo.py  - Demonstração completa"
echo ""
echo "🔄 Para atualizar no futuro:"
echo "----------------------------------------"
echo "  cd $INSTALL_DIR"
echo "  git pull"
echo ""
echo "========================================================================"
echo "  🎉 Aproveite o Sistema de Avaliação DISC!"
echo "========================================================================"
echo ""

# Salvar informações da instalação
cat > "$INSTALL_DIR/install_info.txt" <<EOF
Sistema DISC - Informações de Instalação
=========================================

Data da instalação: $(date)
Diretório: $INSTALL_DIR
Python versão: $(python3 --version 2>&1)
Usuário: $(whoami)
Hostname: $(hostname)

Comandos úteis:
  python3 main.py              # Teste interativo
  python3 main.py --demo       # Demo
  python3 test_disc.py         # Testes
  python3 web_app.py           # Servidor web (se instalado)

Diretórios:
  Instalação: $INSTALL_DIR
  Resultados: ~/disc_results
EOF

print_success "Informações salvas em: $INSTALL_DIR/install_info.txt"
