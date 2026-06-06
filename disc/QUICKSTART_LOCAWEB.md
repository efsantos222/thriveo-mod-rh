# 🚀 Guia Rápido - Instalação Locaweb

## ⚡ Instalação Rápida (5 minutos)

### Método 1: Script Automático (Recomendado)

```bash
# 1. Conectar ao servidor via SSH
ssh usuario@seu-servidor.locaweb.com.br

# 2. Baixar e executar script de instalação
wget https://raw.githubusercontent.com/efsantos222/PromptMaster/main/disc_assessment/install.sh
chmod +x install.sh
./install.sh
```

**Pronto!** O script irá:
- ✅ Verificar/instalar Python
- ✅ Clonar o repositório
- ✅ Configurar permissões
- ✅ Executar testes
- ✅ Configurar ambiente

---

### Método 2: Manual (10 minutos)

```bash
# 1. Conectar ao servidor
ssh usuario@seu-servidor.locaweb.com.br

# 2. Instalar dependências
sudo yum install python3 git -y
# ou para Ubuntu/Debian:
# sudo apt-get install python3 git -y

# 3. Clonar repositório
cd ~
git clone https://github.com/efsantos222/PromptMaster.git
cd PromptMaster/disc_assessment

# 4. Tornar executável
chmod +x *.py

# 5. Testar
python3 main.py --demo
```

---

### Método 3: Upload via FTP

1. **Baixe o projeto** no seu computador:
   ```bash
   git clone https://github.com/efsantos222/PromptMaster.git
   ```

2. **Conecte via FTP/SFTP** usando FileZilla ou similar:
   - Host: ftp.seudominio.com.br
   - Usuário: seu_usuario
   - Senha: sua_senha
   - Porta: 21 (FTP) ou 22 (SFTP)

3. **Faça upload** da pasta `disc_assessment` para:
   ```
   /home/seu-usuario/disc_assessment/
   ```

4. **Via SSH**, configure permissões:
   ```bash
   cd ~/disc_assessment
   chmod +x *.py
   python3 main.py --demo
   ```

---

## 🌐 Configurar Aplicação Web

### Passo 1: Instalar Flask

```bash
cd ~/PromptMaster/disc_assessment
pip3 install flask
```

### Passo 2: Iniciar servidor web

```bash
python3 web_app.py
```

O sistema estará disponível em:
- **Servidor local**: http://localhost:5000
- **Rede externa**: http://seu-ip:5000

### Passo 3: Configurar porta no firewall

```bash
# CentOS/RHEL
sudo firewall-cmd --permanent --add-port=5000/tcp
sudo firewall-cmd --reload

# Ubuntu/Debian
sudo ufw allow 5000/tcp
```

---

## 🔒 Configurar para Produção (Opcional)

### Usar Gunicorn (recomendado)

```bash
# Instalar
pip3 install gunicorn

# Executar (4 workers)
cd ~/PromptMaster/disc_assessment
gunicorn -w 4 -b 0.0.0.0:5000 web_app:app
```

### Criar serviço systemd

```bash
# Criar arquivo de serviço
sudo nano /etc/systemd/system/disc-web.service
```

Conteúdo:
```ini
[Unit]
Description=DISC Assessment Web App
After=network.target

[Service]
User=seu-usuario
WorkingDirectory=/home/seu-usuario/PromptMaster/disc_assessment
ExecStart=/usr/bin/python3 /home/seu-usuario/PromptMaster/disc_assessment/web_app.py
Restart=always

[Install]
WantedBy=multi-user.target
```

Ativar:
```bash
sudo systemctl daemon-reload
sudo systemctl start disc-web
sudo systemctl enable disc-web
sudo systemctl status disc-web
```

---

## 🎯 Uso Básico

### Modo CLI (Terminal)

```bash
cd ~/PromptMaster/disc_assessment

# Teste completo interativo
python3 main.py

# Demonstração rápida
python3 main.py --demo

# Ver exemplos
python3 example_usage.py

# Demo completa
python3 quick_demo.py
```

### Modo Web (Navegador)

1. Inicie o servidor: `python3 web_app.py`
2. Acesse: http://seu-ip:5000
3. Clique em "Começar Teste"
4. Responda as 20 perguntas
5. Veja seus resultados!

---

## 📊 Estrutura de Arquivos

```
~/PromptMaster/disc_assessment/
├── main.py                    # CLI - Ponto de entrada
├── web_app.py                 # WEB - Aplicação Flask
├── disc_test.py               # Lógica do teste
├── disc_data.py               # Perguntas e perfis
├── disc_results.py            # Processamento de resultados
├── example_usage.py           # Exemplos de código
├── test_disc.py               # Testes unitários
├── quick_demo.py              # Demo completa
├── install.sh                 # Script de instalação
├── README.md                  # Documentação completa
├── INSTALACAO_SERVIDOR.md     # Guia detalhado
└── QUICKSTART_LOCAWEB.md      # Este arquivo
```

---

## 🔧 Resolução de Problemas

### Python não encontrado
```bash
# Criar symlink
sudo ln -s /usr/bin/python3 /usr/bin/python
```

### Erro de permissão
```bash
chmod 755 ~/PromptMaster/disc_assessment
chmod +x ~/PromptMaster/disc_assessment/*.py
```

### Porta 5000 já em uso
```bash
# Verificar o que está usando
sudo netstat -tulpn | grep 5000

# Usar outra porta
python3 web_app.py --port 8080
```

### Módulo não encontrado
```bash
# Adicionar ao PYTHONPATH
export PYTHONPATH=~/PromptMaster:$PYTHONPATH
echo 'export PYTHONPATH=~/PromptMaster:$PYTHONPATH' >> ~/.bashrc
```

---

## 📱 Acessar de fora do servidor

### Opção 1: IP Direto
```
http://seu-ip-publico:5000
```

### Opção 2: Domínio (com Apache/Nginx)

**Apache (proxy reverso)**:
```apache
<VirtualHost *:80>
    ServerName disc.seudominio.com.br

    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:5000/
    ProxyPassReverse / http://127.0.0.1:5000/
</VirtualHost>
```

**Nginx (proxy reverso)**:
```nginx
server {
    listen 80;
    server_name disc.seudominio.com.br;

    location / {
        proxy_pass http://127.0.0.1:5000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

---

## 📞 Suporte

### Documentação
- **README.md** - Guia completo
- **INSTALACAO_SERVIDOR.md** - Instalação detalhada
- Este arquivo - Início rápido

### Testes
```bash
# Executar todos os testes
python3 test_disc.py

# Ver se tudo está funcionando
python3 quick_demo.py
```

### Locaweb
- Telefone: 0800 000 7778
- Chat: https://www.locaweb.com.br/
- Painel: https://painel.locaweb.com.br/

---

## ✅ Checklist de Instalação

- [ ] Conectado ao servidor via SSH
- [ ] Python 3.7+ instalado
- [ ] Repositório clonado ou arquivos enviados
- [ ] Permissões configuradas (chmod +x)
- [ ] Testes executados com sucesso
- [ ] Demo funcionando (python3 main.py --demo)
- [ ] (Opcional) Flask instalado
- [ ] (Opcional) Web app funcionando
- [ ] (Opcional) Porta configurada no firewall
- [ ] (Opcional) Serviço systemd configurado

---

## 🎉 Pronto!

Agora você pode usar o Sistema DISC:

**Via Terminal**:
```bash
python3 main.py
```

**Via Web**:
```
http://seu-ip:5000
```

**Programático** (Python):
```python
from disc_assessment import DISCTest
test = DISCTest()
results = test.run_test()
results.display_results()
```

---

📚 **Mais informações**: Consulte README.md e INSTALACAO_SERVIDOR.md
