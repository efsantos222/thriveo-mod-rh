# TestProf SoftSkill System

Sistema de gestão de testes comportamentais (DISC e MBTI) com correção via Inteligência Artificial.

## Funcionalidades

- **Admin**: Gerencia Recrutadores, Banco de Questões e Chave API OpenAI.
- **Recrutador**: Gerencia Candidatos, Atribui Testes, Visualiza Relatórios Gerados por IA, Envia Feedback.
- **Candidato**: Realiza testes atribuídos, Visualiza Feedback.
- **IA**: Integração com OpenAI (GPT-4o) para análise de perfil comportamental.

## Instalação (Subdiretório /softskill)

Este sistema foi configurado para funcionar em `testprof.com.br/softskill`.

1. **Upload**:
   - Suba TODOS os arquivos e pastas do projeto para a pasta `softskill` dentro de `public_html`.
   - Certifique-se de incluir o arquivo `.htaccess` que está na raiz do projeto (ele é oculto em alguns sistemas).

2. **Funcionamento**:
   - O arquivo `.htaccess` na raiz redireciona automaticamente o tráfego para a pasta `public/`.
   - O sistema detecta automaticamente que está rodando em um subdiretório e ajusta os links (CSS, menus).
   
3. **Acesso**:
   - Acesse: `https://testprof.com.br/softskill`
   - Se for a primeira vez, vá para `https://testprof.com.br/softskill/public/install.php` para instalar o banco.

4. **Credenciais Padrão**:
   - **Email**: admin@testprof.com.br
   - **Senha**: admin123


