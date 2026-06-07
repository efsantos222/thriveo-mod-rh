<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Connections - Jogos de Apresentação</title>
    <meta name="description" content="Jogos interativos de apresentação para reuniões virtuais em equipe. Promova conexões autênticas e momentos divertidos.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎮 Virtual Connections</h1>
            <p>Jogos interativos de apresentação para reuniões virtuais</p>
        </div>

        <div class="glass-effect-strong" style="padding: 48px; text-align: center; margin-bottom: 40px;">
            <h2 style="color: white; font-size: 32px; margin-bottom: 16px;">Bem-vindo!</h2>
            <p style="color: rgba(255, 255, 255, 0.9); font-size: 18px; margin-bottom: 32px;">
                Crie conexões autênticas através de jogos de apresentação divertidos
            </p>
            <div class="flex-center gap-4">
                <a href="pages/create-session.php" class="btn btn-primary" data-testid="button-create-session" style="font-size: 18px; padding: 16px 32px;">
                    ✨ Criar Nova Sessão
                </a>
                <a href="pages/admin-login.php" class="btn btn-secondary" data-testid="link-admin" style="font-size: 18px; padding: 16px 32px;">
                    🔒 Área Administrativa
                </a>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="card">
                <div style="text-align: center; padding: 20px;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); border-radius: 20px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 40px;">
                        ⚡
                    </div>
                    <h3 style="font-size: 24px; margin-bottom: 12px;">Apresentação Relâmpago</h3>
                    <p style="color: #6b7280;">Apresentações rápidas com desafios surpresa</p>
                </div>
            </div>

            <div class="card">
                <div style="text-align: center; padding: 20px;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #ec4899 0%, #d946ef 100%); border-radius: 20px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 40px;">
                        😀
                    </div>
                    <h3 style="font-size: 24px; margin-bottom: 12px;">Apresentação com Emoji</h3>
                    <p style="color: #6b7280;">Use emojis para contar sua história</p>
                </div>
            </div>

            <div class="card">
                <div style="text-align: center; padding: 20px;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%); border-radius: 20px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 40px;">
                        📖
                    </div>
                    <h3 style="font-size: 24px; margin-bottom: 12px;">Construtor de Histórias</h3>
                    <p style="color: #6b7280;">Crie uma história colaborativa em equipe</p>
                </div>
            </div>

            <div class="card">
                <div style="text-align: center; padding: 20px;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%); border-radius: 20px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 40px;">
                        🎁
                    </div>
                    <h3 style="font-size: 24px; margin-bottom: 12px;">Caixa Misteriosa</h3>
                    <p style="color: #6b7280;">Perguntas aleatórias para quebrar o gelo</p>
                </div>
            </div>
        </div>

        <div class="glass-effect" style="padding: 32px; margin-top: 40px; text-align: center;">
            <h3 style="color: white; font-size: 24px; margin-bottom: 16px;">Como Funciona?</h3>
            <div class="grid grid-3">
                <div style="color: white; padding: 20px;">
                    <div style="font-size: 48px; margin-bottom: 12px;">1️⃣</div>
                    <h4 style="font-size: 18px; margin-bottom: 8px;">Crie uma Sessão</h4>
                    <p style="opacity: 0.9; font-size: 14px;">Adicione os participantes e escolha um jogo</p>
                </div>
                <div style="color: white; padding: 20px;">
                    <div style="font-size: 48px; margin-bottom: 12px;">2️⃣</div>
                    <h4 style="font-size: 18px; margin-bottom: 8px;">Jogue Juntos</h4>
                    <p style="opacity: 0.9; font-size: 14px;">Cada participante tem sua vez de brilhar</p>
                </div>
                <div style="color: white; padding: 20px;">
                    <div style="font-size: 48px; margin-bottom: 12px;">3️⃣</div>
                    <h4 style="font-size: 18px; margin-bottom: 8px;">Conecte-se</h4>
                    <p style="opacity: 0.9; font-size: 14px;">Conheça melhor sua equipe de forma divertida</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
