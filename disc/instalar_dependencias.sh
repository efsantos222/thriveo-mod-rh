#!/bin/bash

# ==============================================================================
# SCRIPT DE INSTALAÇÃO AUTOMÁTICA (THRIVEO DISC)
# ==============================================================================
# Este script gerencia a criação do Ambiente Virtual (venv) e instalação de pacotes.
# Ele resolve problemas de PERMISSÃO ao garantir que nada seja instalado no sistema global.

# Determina o diretório onde o script está rodando
BASE_DIR="$(pwd)"
VENV_NAME="venv"
VENV_PATH="$BASE_DIR/$VENV_NAME"

echo "============================================================"
echo "🚀 Iniciando Configuração do Ambiente Python"
echo "📂 Diretório Base: $BASE_DIR"
echo "🐍 Ambiente Virtual Alvo: $VENV_PATH"
echo "============================================================"

# 1. Verificar se o venv já existe
if [ ! -d "$VENV_PATH" ]; then
    echo "⚠️  Ambiente virtual '$VENV_NAME' não encontrado."
    echo "🔨 Criando novo ambiente virtual..."
    
    # Tenta criar com python3
    if command -v python3 &> /dev/null; then
        python3 -m venv "$VENV_NAME"
    else
        # Fallback para tentar achar o python correto na Locaweb se python3 não estiver no path
        echo "⚠️  Comando 'python3' não encontrado. Tentando localizar..."
        # Tenta criar usando o caminho padrão da Locaweb se disponível
        if [ -f "/usr/bin/python3" ]; then
            /usr/bin/python3 -m venv "$VENV_NAME"
        else
            echo "❌ ERRO: Não consegui encontrar o Python 3 para criar o venv."
            echo "   Por favor, crie manualmente: python3 -m venv venv"
            exit 1
        fi
    fi
    
    if [ -d "$VENV_PATH" ]; then
        echo "✅ Ambiente virtual criado com sucesso!"
    else
        echo "❌ ERRO: Falha ao criar a pasta $VENV_NAME."
        exit 1
    fi
else
    echo "✅ Ambiente virtual '$VENV_NAME' já existe."
fi

# 2. Atualizar pip (opcional, mas recomendado) e Instalar Dependências
if [ -f "$VENV_PATH/bin/pip" ]; then
    echo "📦 Atualizando pip..."
    "$VENV_PATH/bin/pip" install --upgrade pip
    
    echo "📦 Instalando dependências do requirements.txt..."
    if [ -f "requirements.txt" ]; then
        "$VENV_PATH/bin/pip" install -r requirements.txt
        
        if [ $? -eq 0 ]; then
            echo ""
            echo "🎉 SUCESSO! Instalação concluída."
            echo "============================================================"
            echo "Para ativar o ambiente manualmente no futuro, use:"
            echo "source $VENV_NAME/bin/activate"
            echo "============================================================"
        else
            echo "❌ ERRO durante a instalação das dependências."
        fi
    else
        echo "❌ ERRO: Arquivo requirements.txt não encontrado em $BASE_DIR."
    fi
else
    echo "❌ ERRO CRÍTICO: O binário pip não foi encontrado em $VENV_PATH/bin/pip."
    echo "O ambiente virtual pode estar corrompido. Tente apagar a pasta 'venv' e rodar novamente."
fi
