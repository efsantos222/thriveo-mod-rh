# 🚀 Guia de Instalação - Servidor Dedicado Locaweb

Guia completo para instalar o Sistema de Avaliação DISC em um servidor dedicado da Locaweb.

## 📋 Pré-requisitos

- Servidor dedicado Locaweb com acesso SSH
- Python 3.7 ou superior
- Git (opcional, para clonar o repositório)
- Acesso root ou sudo

## 🔐 Passo 1: Conectar ao Servidor

### Via SSH

```bash
# Conecte ao seu servidor usando SSH
ssh usuario@seu-servidor.locaweb.com.br

# Ou se você tem um IP
ssh usuario@123.456.789.012
```

### Via Painel Locaweb

Se preferir, acesse o painel de controle da Locaweb e use o terminal web.

## 🐍 Passo 2: Verificar/Instalar Python

```bash
# Verificar se Python está instalado
python3 --version

# Se não estiver instalado (CentOS/RHEL)
sudo yum install python3 python3-pip -y

# Se não estiver instalado (Ubuntu/Debian)
sudo apt-get update
sudo apt-get install python3 python3-pip -y
```

## 📦 Passo 3: Escolher Método de Instalação

### Método A: Via Git (Recomendado)

```bash
# Instalar Git se necessário
sudo yum install git -y        # CentOS/RHEL
# ou
sudo apt-get install git -y    # Ubuntu/Debian

# Navegar para o diretório desejado
cd /home/seu-usuario/
# ou
cd /var/www/

# Clonar o repositório
git clone https://github.com/efsantos222/PromptMaster.git

# Entrar no diretório
cd PromptMaster/disc_assessment
```

### Método B: Upload Manual via FTP/SFTP

1. **Baixe o repositório** localmente:
   ```bash
   git clone https://github.com/efsantos222/PromptMaster.git
   ```

2. **Conecte via FTP/SFTP** usando:
   - FileZilla
   - WinSCP
   - Cyberduck

3. **Faça upload da pasta** `disc_assessment` para seu servidor:
   ```
   /home/seu-usuario/disc_assessment/
   ```

### Método C: Download Direto no Servidor

```bash
# Criar diretório
mkdir -p /home/seu-usuario/disc_assessment
cd /home/seu-usuario/disc_assessment

# Baixar arquivos via wget ou curl
wget https://raw.githubusercontent.com/efsantos222/PromptMaster/main/disc_assessment/main.py
wget https://raw.githubusercontent.com/efsantos222/PromptMaster/main/disc_assessment/disc_test.py
wget https://raw.githubusercontent.com/efsantos222/PromptMaster/main/disc_assessment/disc_data.py
wget https://raw.githubusercontent.com/efsantos222/PromptMaster/main/disc_assessment/disc_results.py
wget https://raw.githubusercontent.com/efsantos222/PromptMaster/main/disc_assessment/__init__.py
# ... baixar todos os arquivos necessários
```

## ⚙️ Passo 4: Configurar Permissões

```bash
# Navegar para o diretório
cd /home/seu-usuario/PromptMaster/disc_assessment

# Tornar os scripts executáveis
chmod +x main.py
chmod +x example_usage.py
chmod +x test_disc.py
chmod +x quick_demo.py

# Ajustar permissões do diretório
chmod 755 .
```

## ✅ Passo 5: Testar a Instalação

```bash
# Testar se o Python encontra os módulos
python3 -c "import sys; print(sys.version)"

# Executar os testes
python3 test_disc.py

# Executar o demo
python3 main.py --demo

# Testar o quick demo
python3 quick_demo.py
```

## 🌐 Passo 6: Configurar como Aplicação Web (Opcional)

Se você quiser tornar o sistema acessível via web, você pode usar Flask:

### 6.1: Instalar Flask

```bash
pip3 install flask
```

### 6.2: Criar arquivo web.py

Vou criar um exemplo de aplicação web para você.

## 🔒 Passo 7: Configurar Firewall (Opcional)

```bash
# Se for executar como aplicação web
sudo firewall-cmd --permanent --add-port=5000/tcp
sudo firewall-cmd --reload

# Ou no iptables
sudo iptables -A INPUT -p tcp --dport 5000 -j ACCEPT
```

## 🔄 Passo 8: Configurar como Serviço (Opcional)

### Criar arquivo de serviço systemd

```bash
sudo nano /etc/systemd/system/disc-assessment.service
```

Conteúdo do arquivo:

```ini
[Unit]
Description=DISC Assessment System
After=network.target

[Service]
Type=simple
User=seu-usuario
WorkingDirectory=/home/seu-usuario/PromptMaster/disc_assessment
ExecStart=/usr/bin/python3 /home/seu-usuario/PromptMaster/disc_assessment/main.py
Restart=always

[Install]
WantedBy=multi-user.target
```

### Iniciar o serviço

```bash
sudo systemctl daemon-reload
sudo systemctl start disc-assessment
sudo systemctl enable disc-assessment
sudo systemctl status disc-assessment
```

## 📊 Uso no Servidor

### Uso via Linha de Comando

```bash
# Executar teste interativo
python3 main.py

# Executar demo
python3 main.py --demo

# Ver exemplos
python3 example_usage.py
```

### Uso Programático

Crie seus próprios scripts:

```python
#!/usr/bin/env python3
import sys
sys.path.append('/home/seu-usuario/PromptMaster/disc_assessment')

from disc_assessment import DISCTest

test = DISCTest()
results = test.quick_test(['D'] * 10 + ['I'] * 10)
results.save_to_file('/var/www/html/results/resultado.txt')
```

## 🔍 Troubleshooting

### Erro: "Python não encontrado"

```bash
# Criar symlink
sudo ln -s /usr/bin/python3 /usr/bin/python
```

### Erro: "Módulo não encontrado"

```bash
# Verificar PYTHONPATH
export PYTHONPATH=/home/seu-usuario/PromptMaster:$PYTHONPATH

# Adicionar ao .bashrc para permanente
echo 'export PYTHONPATH=/home/seu-usuario/PromptMaster:$PYTHONPATH' >> ~/.bashrc
source ~/.bashrc
```

### Erro: "Permissão negada"

```bash
# Ajustar permissões
chmod -R 755 /home/seu-usuario/PromptMaster/disc_assessment
chown -R seu-usuario:seu-usuario /home/seu-usuario/PromptMaster
```

### Erro: "Encoding UTF-8"

```bash
# Configurar locale
export LANG=pt_BR.UTF-8
export LC_ALL=pt_BR.UTF-8

# Adicionar ao .bashrc
echo 'export LANG=pt_BR.UTF-8' >> ~/.bashrc
echo 'export LC_ALL=pt_BR.UTF-8' >> ~/.bashrc
```

## 📁 Estrutura Recomendada no Servidor

```
/home/seu-usuario/
├── PromptMaster/
│   └── disc_assessment/
│       ├── main.py
│       ├── disc_test.py
│       ├── disc_data.py
│       ├── disc_results.py
│       └── ...
├── disc_results/          # Para armazenar resultados
│   ├── user1_result.txt
│   └── user1_result.json
└── logs/                  # Para logs (opcional)
    └── disc_assessment.log
```

## 🔐 Segurança

### Boas Práticas

1. **Não execute como root**
   ```bash
   # Crie um usuário específico
   sudo useradd -m discapp
   sudo -u discapp python3 main.py
   ```

2. **Proteja os resultados**
   ```bash
   mkdir -p ~/disc_results
   chmod 700 ~/disc_results
   ```

3. **Use ambiente virtual** (recomendado)
   ```bash
   python3 -m venv /home/seu-usuario/venv
   source /home/seu-usuario/venv/bin/activate
   cd /home/seu-usuario/PromptMaster/disc_assessment
   python main.py
   ```

## 📞 Suporte Locaweb

Se encontrar problemas específicos do servidor:

- **Telefone**: 0800 000 7778
- **Chat**: https://www.locaweb.com.br/
- **Painel**: https://painel.locaweb.com.br/

## 🎯 Próximos Passos

1. ✅ Instalar o sistema
2. ✅ Testar com `python3 main.py --demo`
3. ✅ Criar scripts personalizados
4. 🔄 (Opcional) Configurar interface web
5. 🔄 (Opcional) Integrar com seu sistema existente

## 📚 Documentação Adicional

- [README.md](README.md) - Documentação completa do sistema
- [example_usage.py](example_usage.py) - Exemplos de uso
- [test_disc.py](test_disc.py) - Testes unitários

---

💡 **Dica**: Para atualizar o sistema no futuro, basta fazer `git pull` no diretório do projeto!
