"""
DISC Assessment Data
Contains questions and profile descriptions for the DISC personality assessment
"""

# DISC Test Questions
# Each question has 4 options corresponding to D, I, S, C profiles
DISC_QUESTIONS = [
    {
        "question": "Como você se descreve em situações de trabalho?",
        "options": {
            "D": "Direto, gosto de resultados rápidos e assumir controle",
            "I": "Entusiasmado, gosto de trabalhar com pessoas e ser sociável",
            "S": "Calmo, prefiro estabilidade e trabalho em equipe",
            "C": "Analítico, valorizo precisão e qualidade"
        }
    },
    {
        "question": "Quando enfrenta um problema, você tende a:",
        "options": {
            "D": "Tomar decisões rápidas e agir imediatamente",
            "I": "Discutir com outras pessoas e buscar consenso",
            "S": "Pensar cuidadosamente e buscar a melhor solução para todos",
            "C": "Analisar todos os dados antes de decidir"
        }
    },
    {
        "question": "Em reuniões, você geralmente:",
        "options": {
            "D": "Lidera a discussão e busca decisões rápidas",
            "I": "Participa ativamente, compartilha ideias e anima o grupo",
            "S": "Ouve mais do que fala e apoia as ideias dos outros",
            "C": "Contribui com fatos e análises detalhadas"
        }
    },
    {
        "question": "Seu estilo de comunicação é:",
        "options": {
            "D": "Direto ao ponto, objetivo e assertivo",
            "I": "Amigável, expressivo e persuasivo",
            "S": "Paciente, atencioso e cooperativo",
            "C": "Formal, preciso e baseado em fatos"
        }
    },
    {
        "question": "Sob pressão, você:",
        "options": {
            "D": "Torna-se mais determinado e agressivo",
            "I": "Mantém o otimismo e busca motivar os outros",
            "S": "Permanece calmo e procura manter a harmonia",
            "C": "Foca em evitar erros e manter padrões"
        }
    },
    {
        "question": "Você prefere um ambiente de trabalho que seja:",
        "options": {
            "D": "Desafiador, competitivo e orientado a resultados",
            "I": "Dinâmico, social e cheio de interações",
            "S": "Estável, previsível e cooperativo",
            "C": "Organizado, estruturado e com processos claros"
        }
    },
    {
        "question": "Ao liderar um projeto, você:",
        "options": {
            "D": "Define metas claras e cobra resultados",
            "I": "Motiva a equipe e mantém o entusiasmo",
            "S": "Garante que todos estejam confortáveis e apoiados",
            "C": "Estabelece processos e monitora a qualidade"
        }
    },
    {
        "question": "Suas principais motivações são:",
        "options": {
            "D": "Vencer desafios e alcançar poder",
            "I": "Reconhecimento social e aprovação",
            "S": "Segurança e relacionamentos estáveis",
            "C": "Excelência e precisão no trabalho"
        }
    },
    {
        "question": "Você toma decisões baseado em:",
        "options": {
            "D": "Resultados e eficiência",
            "I": "Intuição e opinião dos outros",
            "S": "Impacto nas pessoas e relacionamentos",
            "C": "Dados, fatos e análises"
        }
    },
    {
        "question": "Seu maior medo no trabalho é:",
        "options": {
            "D": "Perder o controle ou ser dominado",
            "I": "Ser rejeitado ou ignorado",
            "S": "Mudanças súbitas e conflitos",
            "C": "Cometer erros ou ser criticado"
        }
    },
    {
        "question": "Como você lida com mudanças?",
        "options": {
            "D": "Vejo como oportunidades e ajo rapidamente",
            "I": "Fico animado com novas possibilidades",
            "S": "Preciso de tempo para me adaptar",
            "C": "Avalio cuidadosamente os riscos e benefícios"
        }
    },
    {
        "question": "Seu ritmo de trabalho é:",
        "options": {
            "D": "Rápido e focado em resultados",
            "I": "Variável, dependendo da interação social",
            "S": "Constante e consistente",
            "C": "Metódico e deliberado"
        }
    },
    {
        "question": "Ao resolver conflitos, você:",
        "options": {
            "D": "Confronta diretamente e busca resolver rápido",
            "I": "Usa charme e persuasão para suavizar",
            "S": "Evita confrontos e busca compromisso",
            "C": "Apresenta fatos e lógica"
        }
    },
    {
        "question": "Suas maiores forças são:",
        "options": {
            "D": "Determinação e capacidade de decisão",
            "I": "Entusiasmo e habilidades sociais",
            "S": "Paciência e lealdade",
            "C": "Precisão e pensamento analítico"
        }
    },
    {
        "question": "Você se sente mais confortável quando:",
        "options": {
            "D": "Está no comando e controlando a situação",
            "I": "Está cercado de pessoas e sendo apreciado",
            "S": "Está em um ambiente harmonioso e previsível",
            "C": "Tem todas as informações e padrões claros"
        }
    },
    {
        "question": "Ao definir metas, você:",
        "options": {
            "D": "Define metas ambiciosas e desafiadoras",
            "I": "Define metas que envolvam reconhecimento",
            "S": "Prefere metas realistas e alcançáveis",
            "C": "Define metas baseadas em padrões de qualidade"
        }
    },
    {
        "question": "Como você reage a críticas?",
        "options": {
            "D": "Fico na defensiva e argumento",
            "I": "Levo para o lado pessoal",
            "S": "Aceito calmamente, mesmo que me magoe",
            "C": "Avalio a validade das críticas objetivamente"
        }
    },
    {
        "question": "Seu foco principal no trabalho é:",
        "options": {
            "D": "Alcançar objetivos e vencer",
            "I": "Construir relacionamentos e influenciar",
            "S": "Manter harmonia e apoiar a equipe",
            "C": "Garantir qualidade e precisão"
        }
    },
    {
        "question": "Ao trabalhar em equipe, você:",
        "options": {
            "D": "Assume a liderança naturalmente",
            "I": "Energiza e motiva o grupo",
            "S": "Apoia e colabora com todos",
            "C": "Garante que os procedimentos sejam seguidos"
        }
    },
    {
        "question": "Você é mais valorizado por:",
        "options": {
            "D": "Sua capacidade de entregar resultados",
            "I": "Sua capacidade de inspirar pessoas",
            "S": "Sua confiabilidade e lealdade",
            "C": "Sua atenção aos detalhes e precisão"
        }
    }
]

# Profile Descriptions
PROFILE_DESCRIPTIONS = {
    "D": {
        "name": "Dominância (Executor)",
        "characteristics": [
            "Direto e assertivo",
            "Orientado a resultados",
            "Gosta de desafios",
            "Tomada de decisão rápida",
            "Focado em eficiência",
            "Competitivo",
            "Busca controle"
        ],
        "strengths": [
            "Liderança natural",
            "Determinação",
            "Iniciativa",
            "Capacidade de decisão rápida",
            "Orientação para objetivos",
            "Aceita desafios"
        ],
        "weaknesses": [
            "Pode ser impaciente",
            "Às vezes insensível",
            "Pode dominar conversas",
            "Dificuldade em delegar",
            "Resistente a seguir ordens"
        ],
        "ideal_environment": "Ambiente competitivo, desafiador, com autonomia e foco em resultados",
        "communication_style": "Direto, objetivo, focado no essencial",
        "motivation": "Resultados, desafios, poder e autoridade",
        "stress_response": "Torna-se mais agressivo e determinado"
    },
    "I": {
        "name": "Influência (Comunicador)",
        "characteristics": [
            "Entusiasmado e otimista",
            "Sociável e persuasivo",
            "Expressivo emocionalmente",
            "Gosta de trabalhar com pessoas",
            "Criativo e inovador",
            "Busca reconhecimento",
            "Confiante"
        ],
        "strengths": [
            "Excelentes habilidades de comunicação",
            "Capacidade de motivar",
            "Criatividade",
            "Otimismo",
            "Networking natural",
            "Persuasão"
        ],
        "weaknesses": [
            "Pode ser desorganizado",
            "Falta de atenção aos detalhes",
            "Dificuldade em cumprir prazos",
            "Evita conflitos",
            "Pode ser impulsivo"
        ],
        "ideal_environment": "Ambiente social, dinâmico, com reconhecimento público e trabalho em equipe",
        "communication_style": "Amigável, expressivo, persuasivo e entusiasta",
        "motivation": "Reconhecimento, aprovação social, relacionamentos",
        "stress_response": "Mantém otimismo e busca apoio dos outros"
    },
    "S": {
        "name": "Estabilidade (Apoiador)",
        "characteristics": [
            "Calmo e paciente",
            "Leal e confiável",
            "Prefere rotina",
            "Cooperativo",
            "Bom ouvinte",
            "Evita conflitos",
            "Orientado para equipe"
        ],
        "strengths": [
            "Paciência",
            "Lealdade",
            "Trabalho em equipe",
            "Estabilidade emocional",
            "Capacidade de ouvir",
            "Confiabilidade"
        ],
        "weaknesses": [
            "Resistente a mudanças",
            "Dificuldade em dizer não",
            "Evita confrontos",
            "Pode ser indeciso",
            "Dificuldade em se adaptar rapidamente"
        ],
        "ideal_environment": "Ambiente estável, previsível, com equipe harmoniosa e processos claros",
        "communication_style": "Paciente, atencioso, cooperativo e apoiador",
        "motivation": "Segurança, estabilidade, harmonia no grupo",
        "stress_response": "Permanece calmo e busca manter a paz"
    },
    "C": {
        "name": "Conformidade (Analista)",
        "characteristics": [
            "Analítico e preciso",
            "Orientado a detalhes",
            "Sistemático",
            "Busca qualidade",
            "Cauteloso",
            "Valoriza padrões",
            "Objetivo"
        ],
        "strengths": [
            "Atenção aos detalhes",
            "Pensamento analítico",
            "Qualidade no trabalho",
            "Organização",
            "Precisão",
            "Planejamento sistemático"
        ],
        "weaknesses": [
            "Pode ser perfeccionista",
            "Dificuldade com ambiguidade",
            "Lentidão nas decisões",
            "Crítico demais",
            "Dificuldade em expressar emoções"
        ],
        "ideal_environment": "Ambiente organizado, estruturado, com padrões claros e foco em qualidade",
        "communication_style": "Formal, preciso, baseado em fatos e dados",
        "motivation": "Excelência, precisão, conhecimento e padrões altos",
        "stress_response": "Foca em evitar erros e manter controle de qualidade"
    }
}

# Combination profiles (when two traits are close)
COMBINATION_PROFILES = {
    "DI": "Executor-Comunicador: Líder carismático, assertivo e sociável",
    "DS": "Executor-Apoiador: Líder confiável, focado em resultados mas também em pessoas",
    "DC": "Executor-Analista: Líder estratégico, orientado a resultados com foco em qualidade",
    "IS": "Comunicador-Apoiador: Conselheiro, sociável e empático",
    "IC": "Comunicador-Analista: Criativo mas organizado, expressivo mas preciso",
    "SC": "Apoiador-Analista: Confiável e meticuloso, estável e preciso"
}
