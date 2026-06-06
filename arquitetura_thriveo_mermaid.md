# 🏗️ Arquitetura Geral do Sistema Thriveo AI Jobs

Este diagrama ilustra a integração entre os componentes do sistema, desde a autenticação do usuário até o processamento de IA e persistência de dados.

```mermaid
graph TD
    %% Usuário e Frontend
    User((Candidato / Usuário))
    UI["Frontend (Elementor + Templates PHP)"]
    Hero[Widget Hero - Acesso Rápido]
    Dash[Dashboard de Perfil]
    Cert[Módulo de Certificações]
    Ref[Referências Profissionais D4]
    
    %% Camada de Autenticação
    subgraph Auth_Layer [Escudo de Autenticação]
        JWT[(LocalStorage: JWT Token)]
        Session[(PHP Session: candidato_id)]
        Sync{{Motor de Sincronização AJAX}}
        OAuth1[GitHub Auth]
        OAuth2[LinkedIn Auth]
    end

    %% Camada de Lógica (Controllers)
    subgraph Backend [Core do Plugin - Local]
        Main[thriveo-ai-jobs.php]
        OAuthH[OAuth Handler]
        AIH[AI Handler]
        CertC[Certificacao Controller]
        RefC[Referencias Controller]
        SoftC[Soft Skills Controller]
    end

    %% Serviços Externos
    subgraph External [Serviços Externos]
        OpenAI[OpenAI API - GPT-4o]
        GH_API[GitHub API]
        LI_API[LinkedIn API]
    end

    %% Banco de Dados
    subgraph DB [Persistência MySQL]
        T_Cand[thriveo_candidatos]
        T_Vagas[thriveo_vagas]
        T_Quest[thriveo_questoes_tecnicas]
        T_Cert[thriveo_certificacoes]
        T_Ref[thriveo_referencias]
    end

    %% Fluxos de Dados
    User --> Hero
    Hero --> Sync
    Sync -.-> |Valida JWT| OAuthH
    OAuthH --> |Cria/Recupera Sessão| Session
    Session --> Dash
    
    Dash --> Cert
    Cert --> CertC
    CertC --> |Verifica Estoque Questoes| T_Quest
    CertC --> |Trigger - Se < 20 Questoes| AIH
    AIH --> OpenAI
    OpenAI -.-> |Gera JSON| AIH
    AIH --> |Injeta Novas Questoes| T_Quest
    
    Dash --> Ref
    Ref --> RefC
    RefC --> T_Ref
    
    OAuthH <--> GH_API
    OAuthH <--> LI_API
    OAuthH --> T_Cand
    
    %% Estilização
    style Auth_Layer fill:#f0f7ff,stroke:#0056b3,stroke-width:2px
    style Backend fill:#fffbe6,stroke:#d4a017,stroke-width:2px
    style External fill:#f6ffed,stroke:#52c41a,stroke-width:2px
    style DB fill:#fff1f0,stroke:#f5222d,stroke-width:2px
    style User fill:#efdbff,stroke:#722ed1,stroke-width:2px
```

### 🗝️ Componentes Chave:

1.  **Actor (User):** Especialista em IA que consome os serviços.
2.  **Auth Layer:** O ponto central de segurança. O **Sync Motor** é a inovação que evita o logoff quando a sessão PHP expira, usando o **JWT** como âncora de identidade.
3.  **Core del Plugin:** Controladores PHP que processam a lógica de negócio separada da visualização.
4.  **AI Handler:** Ponte inteligente com a **OpenAI (GPT-4o)** para automação de testes técnicos.
5.  **DB Schema:** Tabelas customizadas no WordPress para gestão de talentos e validações.

---
**Diagrama gerado em 10 de Março de 2026.**
