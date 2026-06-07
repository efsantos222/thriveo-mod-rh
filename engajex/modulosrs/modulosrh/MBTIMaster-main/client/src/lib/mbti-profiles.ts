export interface MbtiProfile {
  tipo: string;
  nome: string;
  descricao: string;
  caracteristicasGerais: string[];
  pontoFortes: string[];
  oportunidadesMelhoria: string[];
  pontosDesenvolver: string[];
  ambientesIdeais: string[];
  estilosTrabalho: string[];
  relacionamentos: string[];
  comunicacao: string;
  lideranca?: string;
  carreirasSugeridas: string[];
}

export const mbtiProfiles: Record<string, MbtiProfile> = {
  'ESTJ': {
    tipo: 'ESTJ',
    nome: 'O Executivo',
    descricao: 'Pessoas práticas, organizadas e orientadas para resultados. Excelentes em administração e liderança.',
    caracteristicasGerais: [
      'Focado em eficiência e produtividade',
      'Orientado para objetivos e resultados',
      'Gosta de estrutura e organização',
      'Toma decisões baseadas em fatos e lógica',
      'Valoriza tradições e sistemas estabelecidos',
      'Comunicação direta e assertiva'
    ],
    pontoFortes: [
      'Excelente capacidade de organização e planejamento',
      'Liderança natural e habilidade para dirigir equipes',
      'Forte senso de responsabilidade e comprometimento',
      'Eficiência na execução de tarefas e projetos',
      'Habilidade para tomar decisões rápidas e práticas',
      'Confiabilidade e consistência no trabalho'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior flexibilidade para mudanças',
      'Considerar mais as emoções nas decisões',
      'Dar mais espaço para criatividade e inovação',
      'Melhorar a paciência com processos menos estruturados',
      'Ser mais receptivo a feedback e críticas',
      'Equilibrar foco em resultados com bem-estar da equipe'
    ],
    pontosDesenvolver: [
      'Inteligência emocional e empatia',
      'Adaptabilidade a ambientes em constante mudança',
      'Habilidades de escuta ativa',
      'Abertura para novas ideias e abordagens',
      'Delegação efetiva sem microgerenciamento',
      'Comunicação mais sensível e inclusiva'
    ],
    ambientesIdeais: [
      'Organizações hierárquicas bem estruturadas',
      'Ambientes com processos claros e definidos',
      'Equipes focadas em resultados mensuráveis',
      'Culturas corporativas tradicionais',
      'Projetos com prazos e objetivos específicos'
    ],
    estilosTrabalho: [
      'Preferência por horários regulares e rotinas',
      'Trabalho em equipe com papéis bem definidos',
      'Foco em metas de curto e médio prazo',
      'Uso de ferramentas de gestão e controle',
      'Reuniões estruturadas e objetivas'
    ],
    relacionamentos: [
      'Valoriza lealdade e compromisso',
      'Comunicação direta e honesta',
      'Gosta de relacionamentos estáveis e duradouros',
      'Pode ter dificuldade com conflitos emocionais',
      'Aprecia pessoas confiáveis e pontuais'
    ],
    comunicacao: 'Direta, objetiva e focada em fatos. Prefere comunicação clara e sem ambiguidades.',
    lideranca: 'Liderança diretiva, focada em resultados e organização. Estabelece expectativas claras e monitora o progresso.',
    carreirasSugeridas: [
      'Administração e Gestão',
      'Direito e Advocacia',
      'Engenharia de Produção',
      'Consultoria Empresarial',
      'Gestão de Projetos',
      'Setor Financeiro',
      'Administração Pública'
    ]
  },

  'ISTJ': {
    tipo: 'ISTJ',
    nome: 'O Logístico',
    descricao: 'Pessoas confiáveis, meticulosas e orientadas para o dever. Excelentes em manter sistemas e processos funcionando.',
    caracteristicasGerais: [
      'Extremamente confiável e responsável',
      'Prefere trabalhar de forma independente',
      'Orientado para detalhes e precisão',
      'Valoriza tradições e procedimentos estabelecidos',
      'Toma decisões baseadas em experiências passadas',
      'Comunicação calma e pensada'
    ],
    pontoFortes: [
      'Altíssima confiabilidade e pontualidade',
      'Atenção excepcional aos detalhes',
      'Capacidade de trabalho independente e autônomo',
      'Excelente memória para fatos e procedimentos',
      'Persistência e dedicação a longo prazo',
      'Habilidade para manter qualidade consistente'
    ],
    oportunidadesMelhoria: [
      'Ser mais aberto a novas ideias e métodos',
      'Comunicar-se mais proativamente com a equipe',
      'Desenvolver maior tolerância a ambiguidade',
      'Ser mais flexível com prazos e mudanças',
      'Expressar opiniões pessoais com mais frequência',
      'Buscar mais feedback sobre seu trabalho'
    ],
    pontosDesenvolver: [
      'Habilidades de comunicação interpessoal',
      'Adaptabilidade a mudanças organizacionais',
      'Pensamento estratégico e visão de futuro',
      'Colaboração em equipes multidisciplinares',
      'Liderança e influência sobre outros',
      'Inovação e criatividade no trabalho'
    ],
    ambientesIdeais: [
      'Organizações estáveis com processos claros',
      'Ambientes que valorizam qualidade e precisão',
      'Equipes pequenas e coesas',
      'Culturas conservadoras e tradicionais',
      'Trabalho com autonomia e responsabilidade individual'
    ],
    estilosTrabalho: [
      'Trabalho individual ou em duplas',
      'Projetos de longa duração',
      'Ambientes silenciosos e organizados',
      'Rotinas e procedimentos bem estabelecidos',
      'Tempo adequado para planejamento e preparação'
    ],
    relacionamentos: [
      'Relacionamentos profundos e de longa duração',
      'Valoriza confiança e lealdade',
      'Prefere grupos pequenos a grandes eventos sociais',
      'Comunicação mais reservada inicialmente',
      'Aprecia pessoas honestas e diretas'
    ],
    comunicacao: 'Cuidadosa, precisa e bem fundamentada. Prefere comunicação escrita ou preparada com antecedência.',
    carreirasSugeridas: [
      'Contabilidade e Auditoria',
      'Administração de Sistemas',
      'Biblioteconomia e Arquivologia',
      'Engenharia Civil',
      'Medicina (especialidades técnicas)',
      'Gestão de Qualidade',
      'Análise de Dados'
    ]
  },

  'ESFJ': {
    tipo: 'ESFJ',
    nome: 'O Cônsul',
    descricao: 'Pessoas calorosas, conscientes e cooperativas. Excelentes em harmonizar equipes e apoiar outros.',
    caracteristicasGerais: [
      'Extremamente orientado para as pessoas',
      'Busca harmonia e cooperação',
      'Valoriza tradições e valores familiares',
      'Toma decisões considerando impacto nas pessoas',
      'Comunicação calorosa e empática',
      'Gosta de ajudar e apoiar outros'
    ],
    pontoFortes: [
      'Excelentes habilidades interpessoais',
      'Capacidade natural de motivar e apoiar equipes',
      'Forte senso de responsabilidade social',
      'Habilidade para criar ambiente harmonioso',
      'Comunicação empática e inclusiva',
      'Lealdade e dedicação às organizações'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior assertividade',
      'Tomar decisões mais objetivas quando necessário',
      'Lidar melhor com conflitos e críticas',
      'Focar mais em resultados além das relações',
      'Ser menos sensível a feedback negativo',
      'Estabelecer limites mais claros'
    ],
    pontosDesenvolver: [
      'Pensamento analítico e crítico',
      'Habilidades de negociação e confronto',
      'Liderança em situações de crise',
      'Tomada de decisões impopulares',
      'Gestão de stress e pressão',
      'Comunicação assertiva em conflitos'
    ],
    ambientesIdeais: [
      'Organizações com foco em pessoas e valores',
      'Ambientes colaborativos e cooperativos',
      'Culturas que valorizam diversidade e inclusão',
      'Equipes unidas com propósito comum',
      'Organizações de serviços ou sem fins lucrativos'
    ],
    estilosTrabalho: [
      'Trabalho em equipe e colaboração',
      'Projetos com impacto social positivo',
      'Ambiente de trabalho amigável e acolhedor',
      'Reconhecimento e feedback positivo',
      'Oportunidades de mentoria e desenvolvimento'
    ],
    relacionamentos: [
      'Rede ampla de relacionamentos pessoais',
      'Valoriza conexões emocionais profundas',
      'Gosta de celebrar conquistas em grupo',
      'Busca consenso e evita conflitos',
      'Aprecia pessoas autênticas e leais'
    ],
    comunicacao: 'Calorosa, empática e focada nas pessoas. Busca conexão emocional e compreensão mútua.',
    lideranca: 'Liderança servidora, focada no desenvolvimento das pessoas e criação de ambiente positivo.',
    carreirasSugeridas: [
      'Recursos Humanos',
      'Educação e Treinamento',
      'Psicologia e Aconselhamento',
      'Serviço Social',
      'Enfermagem e Cuidados de Saúde',
      'Marketing e Relações Públicas',
      'Gestão de Eventos'
    ]
  },

  'ISFJ': {
    tipo: 'ISFJ',
    nome: 'O Protetor',
    descricao: 'Pessoas dedicadas, atenciosas e protetoras. Excelentes em cuidar e apoiar outros de forma discreta.',
    caracteristicasGerais: [
      'Extremamente leal e dedicado',
      'Foco no bem-estar dos outros',
      'Trabalha de forma discreta e consistente',
      'Valoriza estabilidade e segurança',
      'Atenção especial às necessidades individuais',
      'Comunicação gentil e considerada'
    ],
    pontoFortes: [
      'Capacidade excepcional de apoiar e cuidar',
      'Atenção minuciosa às necessidades dos outros',
      'Trabalho consistente e de alta qualidade',
      'Lealdade e confiabilidade excepcionais',
      'Habilidade para criar ambientes acolhedores',
      'Memória excelente para detalhes pessoais'
    ],
    oportunidadesMelhoria: [
      'Expressar suas próprias necessidades',
      'Ser mais assertivo em suas opiniões',
      'Lidar melhor com mudanças inesperadas',
      'Aceitar reconhecimento por seu trabalho',
      'Desenvolver maior confiança em si mesmo',
      'Ser mais direto quando necessário'
    ],
    pontosDesenvolver: [
      'Autoadvocacia e assertividade',
      'Liderança e tomada de iniciativa',
      'Comunicação em grupos grandes',
      'Gestão de conflitos',
      'Pensamento estratégico',
      'Adaptabilidade a mudanças rápidas'
    ],
    ambientesIdeais: [
      'Organizações estáveis com valores claros',
      'Ambientes que valorizam cuidado e qualidade',
      'Equipes pequenas e coesas',
      'Culturas que reconhecem contribuições individuais',
      'Trabalho com propósito social ou humanitário'
    ],
    estilosTrabalho: [
      'Trabalho independente ou em duplas',
      'Projetos com impacto direto nas pessoas',
      'Ambiente tranquilo e organizado',
      'Tempo adequado para fazer trabalho de qualidade',
      'Feedback construtivo e encorajador'
    ],
    relacionamentos: [
      'Relacionamentos profundos e significativos',
      'Preferência por círculos sociais pequenos',
      'Valoriza confiança e intimidade',
      'Gosta de ajudar amigos e família',
      'Comunicação mais reservada inicialmente'
    ],
    comunicacao: 'Gentil, atenciosa e personalizada. Prefere conversas individuais e comunicação não-confrontativa.',
    carreirasSugeridas: [
      'Enfermagem e Cuidados de Saúde',
      'Educação Infantil',
      'Serviço Social',
      'Psicologia Clínica',
      'Biblioteconomia',
      'Administração de Escritório',
      'Terapia Ocupacional'
    ]
  },

  'ESTP': {
    tipo: 'ESTP',
    nome: 'O Empresário',
    descricao: 'Pessoas energéticas, adaptáveis e orientadas para a ação. Excelentes em resolver problemas práticos.',
    caracteristicasGerais: [
      'Orientado para ação e resultados imediatos',
      'Extremamente adaptável e flexível',
      'Foco no presente e situações concretas',
      'Toma decisões rápidas baseadas na situação',
      'Comunicação direta e persuasiva',
      'Gosta de variedade e novos desafios'
    ],
    pontoFortes: [
      'Capacidade excepcional de adaptação',
      'Habilidade para resolver problemas rapidamente',
      'Energia e entusiasmo contagiantes',
      'Excelente em situações de crise',
      'Persuasão e influência natural',
      'Capacidade de motivar equipes para ação'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior paciência com planejamento',
      'Considerar consequências de longo prazo',
      'Melhorar habilidades de escuta ativa',
      'Ser mais consistente em rotinas',
      'Desenvolver maior sensibilidade emocional',
      'Focar em qualidade além de velocidade'
    ],
    pontosDesenvolver: [
      'Planejamento estratégico e visão de futuro',
      'Persistência em projetos de longo prazo',
      'Análise aprofundada antes da ação',
      'Sensibilidade às necessidades emocionais',
      'Documentação e seguimento de processos',
      'Reflexão e autoavaliação regular'
    ],
    ambientesIdeais: [
      'Organizações dinâmicas e em crescimento',
      'Ambientes com variedade e mudanças',
      'Culturas que valorizam resultados rápidos',
      'Equipes ágeis e orientadas para ação',
      'Trabalho de campo ou com clientes'
    ],
    estilosTrabalho: [
      'Projetos variados e dinâmicos',
      'Trabalho com pessoas e interação',
      'Ambiente flexível e não-rotineiro',
      'Feedback imediato e reconhecimento',
      'Oportunidades de viagem e movimento'
    ],
    relacionamentos: [
      'Rede ampla e diversificada de contatos',
      'Relacionamentos casuais e divertidos',
      'Gosta de atividades sociais e eventos',
      'Comunicação espontânea e animada',
      'Aprecia pessoas autênticas e diretas'
    ],
    comunicacao: 'Direta, persuasiva e carismática. Prefere comunicação face a face e interações dinâmicas.',
    lideranca: 'Liderança por exemplo, inspirando através da energia e capacidade de adaptação.',
    carreirasSugeridas: [
      'Vendas e Desenvolvimento de Negócios',
      'Marketing e Publicidade',
      'Consultoria Empresarial',
      'Gestão de Projetos',
      'Empreendedorismo',
      'Gestão de Crises',
      'Recursos Humanos'
    ]
  },

  'ISTP': {
    tipo: 'ISTP',
    nome: 'O Virtuoso',
    descricao: 'Pessoas práticas, adaptáveis e orientadas para soluções. Excelentes em entender como as coisas funcionam.',
    caracteristicasGerais: [
      'Extremamente prático e orientado para soluções',
      'Prefere trabalhar com as mãos e ferramentas',
      'Adaptável e flexível às circunstâncias',
      'Toma decisões baseadas em lógica prática',
      'Comunicação concisa e direta',
      'Valoriza autonomia e independência'
    ],
    pontoFortes: [
      'Excelente capacidade de resolução de problemas',
      'Habilidade técnica e mecânica superior',
      'Adaptabilidade em situações imprevistas',
      'Calma e eficácia sob pressão',
      'Independência e autoconfiança',
      'Capacidade de aprender fazendo'
    ],
    oportunidadesMelhoria: [
      'Desenvolver habilidades de comunicação interpessoal',
      'Ser mais proativo em compartilhar conhecimento',
      'Melhorar habilidades de planejamento',
      'Considerar mais o impacto nas pessoas',
      'Desenvolver paciência com processos burocráticos',
      'Ser mais aberto a feedback e sugestões'
    ],
    pontosDesenvolver: [
      'Liderança e gestão de equipes',
      'Comunicação em apresentações',
      'Planejamento estratégico',
      'Relacionamentos interpessoais profundos',
      'Expressão de ideias e conceitos abstratos',
      'Colaboração em projetos de equipe'
    ],
    ambientesIdeais: [
      'Organizações técnicas e especializadas',
      'Ambientes que valorizam competência técnica',
      'Trabalho com autonomia e flexibilidade',
      'Culturas que focam em resultados práticos',
      'Projetos hands-on e experimentais'
    ],
    estilosTrabalho: [
      'Trabalho individual e autônomo',
      'Projetos técnicos e práticos',
      'Ambiente flexível sem microgerenciamento',
      'Acesso a ferramentas e recursos adequados',
      'Variedade de desafios técnicos'
    ],
    relacionamentos: [
      'Círculo pequeno de amigos próximos',
      'Relacionamentos baseados em atividades comuns',
      'Valoriza lealdade e confiança',
      'Comunicação mais reservada',
      'Aprecia pessoas práticas e diretas'
    ],
    comunicacao: 'Concisa, técnica e focada em fatos. Prefere demonstrar através de ações.',
    carreirasSugeridas: [
      'Engenharia Mecânica',
      'Tecnologia da Informação',
      'Manutenção e Reparo',
      'Arquitetura',
      'Aviação',
      'Segurança e Emergências',
      'Pesquisa e Desenvolvimento'
    ]
  },

  'ESFP': {
    tipo: 'ESFP',
    nome: 'O Animador',
    descricao: 'Pessoas espontâneas, entusiasmadas e orientadas para pessoas. Excelentes em motivar e inspirar outros.',
    caracteristicasGerais: [
      'Extremamente sociável e carismático',
      'Foco no presente e experiências concretas',
      'Orientado para pessoas e relacionamentos',
      'Toma decisões baseadas em valores pessoais',
      'Comunicação calorosa e envolvente',
      'Busca diversão e significado no trabalho'
    ],
    pontoFortes: [
      'Capacidade excepcional de motivar outros',
      'Habilidades interpessoais naturais',
      'Adaptabilidade e flexibilidade',
      'Energia e entusiasmo contagiantes',
      'Criatividade e pensamento inovador',
      'Capacidade de trabalhar bem sob pressão'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior foco em planejamento',
      'Melhorar habilidades analíticas',
      'Ser mais consistente em rotinas',
      'Considerar consequências de longo prazo',
      'Desenvolver maior paciência com detalhes',
      'Lidar melhor com críticas construtivas'
    ],
    pontosDesenvolver: [
      'Análise crítica e pensamento lógico',
      'Planejamento estratégico e organização',
      'Persistência em projetos complexos',
      'Gestão de tempo e prioridades',
      'Comunicação técnica e formal',
      'Tolerância a rotinas e processos'
    ],
    ambientesIdeais: [
      'Organizações dinâmicas e inovadoras',
      'Ambientes colaborativos e sociais',
      'Culturas que valorizam criatividade',
      'Equipes diversas e inclusivas',
      'Trabalho com impacto direto nas pessoas'
    ],
    estilosTrabalho: [
      'Trabalho em equipe e colaboração',
      'Projetos criativos e variados',
      'Ambiente flexível e estimulante',
      'Interação frequente com pessoas',
      'Reconhecimento e feedback positivo'
    ],
    relacionamentos: [
      'Rede ampla e diversificada de amigos',
      'Relacionamentos calorosos e autênticos',
      'Gosta de atividades sociais e celebrações',
      'Comunicação expressiva e emocional',
      'Valoriza harmonia e positividade'
    ],
    comunicacao: 'Calorosa, expressiva e inspiradora. Excelente em apresentações e comunicação emocional.',
    lideranca: 'Liderança inspiradora, motivando através do exemplo e criando ambiente positivo.',
    carreirasSugeridas: [
      'Arte e Design',
      'Marketing e Comunicação',
      'Educação e Treinamento',
      'Psicologia e Aconselhamento',
      'Entretenimento e Mídia',
      'Vendas e Atendimento',
      'Gestão de Eventos'
    ]
  },

  'ISFP': {
    tipo: 'ISFP',
    nome: 'O Aventureiro',
    descricao: 'Pessoas artísticas, flexíveis e orientadas por valores. Excelentes em expressar criatividade e autenticidade.',
    caracteristicasGerais: [
      'Orientado por valores pessoais profundos',
      'Extremamente criativo e artístico',
      'Flexível e adaptável às situações',
      'Toma decisões baseadas em princípios',
      'Comunicação gentil e autêntica',
      'Valoriza individualidade e expressão pessoal'
    ],
    pontoFortes: [
      'Criatividade e pensamento original',
      'Sensibilidade às necessidades dos outros',
      'Flexibilidade e adaptabilidade',
      'Trabalho independente de alta qualidade',
      'Integridade e autenticidade pessoal',
      'Capacidade de ver beleza e potencial'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior assertividade',
      'Melhorar habilidades de planejamento',
      'Ser mais proativo na comunicação',
      'Lidar melhor com conflitos',
      'Desenvolver maior confiança em si mesmo',
      'Focar em objetivos de longo prazo'
    ],
    pontosDesenvolver: [
      'Liderança e influência sobre outros',
      'Comunicação em grupos grandes',
      'Pensamento analítico e lógico',
      'Gestão de projetos e prazos',
      'Networking e relacionamentos profissionais',
      'Negociação e resolução de conflitos'
    ],
    ambientesIdeais: [
      'Organizações com valores alinhados',
      'Ambientes criativos e flexíveis',
      'Culturas que valorizam individualidade',
      'Equipes pequenas e colaborativas',
      'Trabalho com propósito e significado'
    ],
    estilosTrabalho: [
      'Trabalho independente e autônomo',
      'Projetos criativos e significativos',
      'Ambiente flexível e não-estruturado',
      'Tempo para reflexão e desenvolvimento',
      'Variedade e liberdade de expressão'
    ],
    relacionamentos: [
      'Relacionamentos profundos e autênticos',
      'Círculo pequeno de amigos íntimos',
      'Valoriza compreensão mútua',
      'Comunicação mais reservada inicialmente',
      'Aprecia pessoas genuínas e empáticas'
    ],
    comunicacao: 'Gentil, autêntica e expressiva. Prefere comunicação individual e criativa.',
    carreirasSugeridas: [
      'Arte e Design Gráfico',
      'Psicologia e Terapia',
      'Educação Artística',
      'Fotografia e Cinema',
      'Escritura e Jornalismo',
      'Trabalho Social',
      'Música e Performance'
    ]
  },

  // Continuarei com os tipos NT (ENTJ, INTJ, ENTP, INTP) e NF (ENFJ, INFJ, ENFP, INFP)
  
  'ENTJ': {
    tipo: 'ENTJ',
    nome: 'O Comandante',
    descricao: 'Pessoas visionárias, decididas e orientadas para liderança. Excelentes em estratégia e implementação.',
    caracteristicasGerais: [
      'Líder natural com visão estratégica',
      'Orientado para eficiência e resultados',
      'Foco no futuro e possibilidades',
      'Toma decisões baseadas em lógica e estratégia',
      'Comunicação assertiva e persuasiva',
      'Busca constantemente crescimento e melhoria'
    ],
    pontoFortes: [
      'Liderança visionária e estratégica',
      'Capacidade excepcional de planejamento',
      'Habilidade para inspirar e motivar equipes',
      'Pensamento sistêmico e integrado',
      'Tomada de decisão rápida e eficaz',
      'Orientação para resultados e crescimento'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior paciência com processos',
      'Considerar mais as emoções nas decisões',
      'Ser mais receptivo a feedback',
      'Delegar de forma mais efetiva',
      'Equilibrar ambição com bem-estar pessoal',
      'Desenvolver maior sensibilidade interpessoal'
    ],
    pontosDesenvolver: [
      'Inteligência emocional e empatia',
      'Habilidades de escuta ativa',
      'Paciência com ritmos diferentes',
      'Comunicação mais inclusiva',
      'Gestão de stress e burnout',
      'Apreciação por perspectivas divergentes'
    ],
    ambientesIdeais: [
      'Organizações em crescimento e transformação',
      'Ambientes desafiadores e competitivos',
      'Culturas que valorizam inovação',
      'Posições de liderança estratégica',
      'Projetos complexos e de grande escala'
    ],
    estilosTrabalho: [
      'Liderança de equipes multidisciplinares',
      'Projetos estratégicos de longo prazo',
      'Ambiente dinâmico e desafiador',
      'Autonomia para tomar decisões importantes',
      'Acesso a recursos e informações estratégicas'
    ],
    relacionamentos: [
      'Rede profissional ampla e influente',
      'Relacionamentos baseados em respeito mútuo',
      'Valoriza competência e inteligência',
      'Comunicação direta e eficiente',
      'Aprecia pessoas ambiciosas e determinadas'
    ],
    comunicacao: 'Assertiva, estratégica e inspiradora. Excelente em apresentações e comunicação de visão.',
    lideranca: 'Liderança transformacional, focada em visão, estratégia e crescimento organizacional.',
    carreirasSugeridas: [
      'CEO e Alta Direção',
      'Consultoria Estratégica',
      'Investimentos e Finanças',
      'Empreendedorismo',
      'Gestão de Projetos Complexos',
      'Política e Governo',
      'Advocacia Corporativa'
    ]
  },

  'INTJ': {
    tipo: 'INTJ',
    nome: 'O Arquiteto',
    descricao: 'Pessoas estratégicas, independentes e orientadas para inovação. Excelentes em criar sistemas e soluções complexas.',
    caracteristicasGerais: [
      'Pensador estratégico e visionário',
      'Extremamente independente e autoconfiante',
      'Foco em padrões e sistemas complexos',
      'Toma decisões baseadas em análise profunda',
      'Comunicação precisa e fundamentada',
      'Busca constantemente conhecimento e eficiência'
    ],
    pontoFortes: [
      'Pensamento estratégico excepcional',
      'Capacidade de análise complexa e profunda',
      'Inovação e criação de soluções originais',
      'Independência e autogestão',
      'Visão de longo prazo clara',
      'Competência técnica e intelectual'
    ],
    oportunidadesMelhoria: [
      'Desenvolver habilidades interpessoais',
      'Ser mais paciente com processos colaborativos',
      'Comunicar ideias de forma mais acessível',
      'Considerar perspectivas emocionais',
      'Ser mais flexível com mudanças de planos',
      'Valorizar contribuições de outros'
    ],
    pontosDesenvolver: [
      'Liderança e gestão de pessoas',
      'Comunicação interpessoal',
      'Trabalho em equipe colaborativo',
      'Apresentação de ideias complexas',
      'Networking e relacionamentos',
      'Implementação prática de visões'
    ],
    ambientesIdeais: [
      'Organizações inovadoras e tecnológicas',
      'Ambientes que valorizam competência',
      'Culturas meritocráticas',
      'Trabalho independente ou equipes pequenas',
      'Projetos de pesquisa e desenvolvimento'
    ],
    estilosTrabalho: [
      'Trabalho independente e autônomo',
      'Projetos de longo prazo e complexos',
      'Ambiente silencioso e organizado',
      'Acesso a recursos e informações',
      'Liberdade para inovar e experimentar'
    ],
    relacionamentos: [
      'Círculo pequeno de relacionamentos profundos',
      'Valoriza inteligência e competência',
      'Relacionamentos baseados em interesses comuns',
      'Comunicação intelectual e analítica',
      'Aprecia pessoas independentes e originais'
    ],
    comunicacao: 'Precisa, analítica e fundamentada. Prefere comunicação escrita e preparada.',
    carreirasSugeridas: [
      'Pesquisa e Desenvolvimento',
      'Arquitetura de Sistemas',
      'Consultoria Estratégica',
      'Academia e Pesquisa',
      'Engenharia de Software',
      'Planejamento Estratégico',
      'Análise de Investimentos'
    ]
  },

  'ENTP': {
    tipo: 'ENTP',
    nome: 'O Debatedor',
    descricao: 'Pessoas inovadoras, versáteis e orientadas para possibilidades. Excelentes em gerar ideias e soluções criativas.',
    caracteristicasGerais: [
      'Extremamente criativo e inovador',
      'Orientado para possibilidades e potencial',
      'Comunicador natural e persuasivo',
      'Toma decisões explorando múltiplas opções',
      'Gosta de debate e troca de ideias',
      'Busca constantemente novos desafios'
    ],
    pontoFortes: [
      'Geração excepcional de ideias e soluções',
      'Capacidade de ver conexões e padrões',
      'Adaptabilidade e flexibilidade mental',
      'Habilidades de comunicação e persuasão',
      'Energia e entusiasmo para projetos',
      'Capacidade de inspirar e motivar outros'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior foco e persistência',
      'Melhorar habilidades de implementação',
      'Ser mais consistente em rotinas',
      'Considerar detalhes práticos',
      'Desenvolver maior paciência com processos',
      'Focar em conclusão de projetos'
    ],
    pontosDesenvolver: [
      'Gestão de projetos e implementação',
      'Atenção a detalhes e qualidade',
      'Persistência em tarefas rotineiras',
      'Planejamento operacional detalhado',
      'Sensibilidade às necessidades emocionais',
      'Organização e gestão de tempo'
    ],
    ambientesIdeais: [
      'Organizações inovadoras e dinâmicas',
      'Ambientes que valorizam criatividade',
      'Culturas que promovem debate e troca',
      'Startups e empresas em crescimento',
      'Projetos de inovação e mudança'
    ],
    estilosTrabalho: [
      'Trabalho em equipe criativa',
      'Projetos diversos e desafiadores',
      'Ambiente estimulante e flexível',
      'Brainstorming e sessões criativas',
      'Liberdade para explorar ideias'
    ],
    relacionamentos: [
      'Rede ampla e diversificada',
      'Relacionamentos baseados em interesses',
      'Gosta de debates e discussões',
      'Comunicação animada e envolvente',
      'Valoriza inteligência e originalidade'
    ],
    comunicacao: 'Criativa, persuasiva e envolvente. Excelente em brainstorming e apresentação de ideias.',
    lideranca: 'Liderança visionária e inspiradora, focada em inovação e transformação.',
    carreirasSugeridas: [
      'Marketing e Inovação',
      'Consultoria e Estratégia',
      'Empreendedorismo',
      'Pesquisa e Desenvolvimento',
      'Jornalismo e Mídia',
      'Vendas Consultivas',
      'Design Thinking'
    ]
  },

  'INTP': {
    tipo: 'INTP',
    nome: 'O Pensador',
    descricao: 'Pessoas analíticas, objetivas e orientadas para conhecimento. Excelentes em análise teórica e solução de problemas complexos.',
    caracteristicasGerais: [
      'Pensador analítico e lógico',
      'Extremamente curioso e questionador',
      'Orientado para teorias e conceitos abstratos',
      'Toma decisões baseadas em análise lógica',
      'Comunicação precisa e fundamentada',
      'Valoriza conhecimento e competência intelectual'
    ],
    pontoFortes: [
      'Capacidade analítica excepcional',
      'Pensamento lógico e objetivo',
      'Criatividade intelectual e originalidade',
      'Independência e autonomia mental',
      'Capacidade de síntese complexa',
      'Inovação teórica e conceitual'
    ],
    oportunidadesMelhoria: [
      'Desenvolver habilidades de implementação',
      'Melhorar comunicação interpessoal',
      'Ser mais proativo em projetos',
      'Considerar aspectos práticos e emocionais',
      'Desenvolver maior persistência',
      'Focar em aplicação prática de ideias'
    ],
    pontosDesenvolver: [
      'Liderança e gestão de pessoas',
      'Comunicação para audiências diversas',
      'Gestão de projetos práticos',
      'Networking e relacionamentos',
      'Apresentação e vendas de ideias',
      'Trabalho em equipes grandes'
    ],
    ambientesIdeais: [
      'Organizações de pesquisa e desenvolvimento',
      'Ambientes acadêmicos e científicos',
      'Culturas que valorizam inovação intelectual',
      'Trabalho independente ou equipes técnicas',
      'Projetos de análise e solução de problemas'
    ],
    estilosTrabalho: [
      'Trabalho independente e reflexivo',
      'Projetos de análise e pesquisa',
      'Ambiente silencioso e organizado',
      'Liberdade para explorar conceitos',
      'Acesso a informações e recursos'
    ],
    relacionamentos: [
      'Círculo pequeno de relacionamentos intelectuais',
      'Valoriza competência e originalidade',
      'Relacionamentos baseados em interesses comuns',
      'Comunicação intelectual e analítica',
      'Aprecia pessoas independentes e criativas'
    ],
    comunicacao: 'Precisa, lógica e analítica. Prefere discussões intelectuais aprofundadas.',
    carreirasSugeridas: [
      'Pesquisa Científica',
      'Desenvolvimento de Software',
      'Análise de Sistemas',
      'Academia e Ensino Superior',
      'Consultoria Técnica',
      'Arquitetura de Informação',
      'Análise de Dados'
    ]
  },

  'ENFJ': {
    tipo: 'ENFJ',
    nome: 'O Protagonista',
    descricao: 'Pessoas carismáticas, altruístas e orientadas para o desenvolvimento de outros. Excelentes em inspirar e liderar pessoas.',
    caracteristicasGerais: [
      'Líder natural focado em pessoas',
      'Extremamente empático e compreensivo',
      'Orientado para potencial e crescimento',
      'Toma decisões considerando impacto humano',
      'Comunicação inspiradora e motivacional',
      'Busca harmonia e desenvolvimento coletivo'
    ],
    pontoFortes: [
      'Liderança inspiradora e carismática',
      'Capacidade excepcional de desenvolver pessoas',
      'Habilidades de comunicação e influência',
      'Visão de potencial humano',
      'Criação de ambientes colaborativos',
      'Motivação e engajamento de equipes'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior objetividade em decisões',
      'Ser mais assertivo quando necessário',
      'Focar em resultados além de processos',
      'Lidar melhor com conflitos diretos',
      'Estabelecer limites mais claros',
      'Considerar aspectos práticos e financeiros'
    ],
    pontosDesenvolver: [
      'Análise crítica e pensamento lógico',
      'Gestão de conflitos e confrontos',
      'Tomada de decisões impopulares',
      'Análise financeira e operacional',
      'Delegação efetiva',
      'Gestão de stress e sobrecarga'
    ],
    ambientesIdeais: [
      'Organizações com missão social clara',
      'Ambientes colaborativos e inclusivos',
      'Culturas que valorizam desenvolvimento humano',
      'Posições de liderança e influência',
      'Projetos com impacto social positivo'
    ],
    estilosTrabalho: [
      'Liderança de equipes diversas',
      'Projetos de desenvolvimento organizacional',
      'Ambiente colaborativo e estimulante',
      'Foco em crescimento e aprendizagem',
      'Comunicação frequente e feedback'
    ],
    relacionamentos: [
      'Rede ampla de relacionamentos significativos',
      'Mentor natural e conselheiro',
      'Valoriza autenticidade e crescimento',
      'Comunicação empática e inspiradora',
      'Cria conexões profundas com pessoas'
    ],
    comunicacao: 'Inspiradora, empática e motivacional. Excelente em comunicação interpessoal e de grupos.',
    lideranca: 'Liderança transformacional, focada no desenvolvimento de pessoas e criação de visão compartilhada.',
    carreirasSugeridas: [
      'Recursos Humanos e Desenvolvimento',
      'Educação e Treinamento',
      'Psicologia Organizacional',
      'Consultoria em Liderança',
      'Coaching e Mentoria',
      'Gestão de Mudanças',
      'ONGs e Terceiro Setor'
    ]
  },

  'INFJ': {
    tipo: 'INFJ',
    nome: 'O Advogado',
    descricao: 'Pessoas visionárias, principiadas e orientadas para propósito. Excelentes em compreender pessoas e criar mudanças significativas.',
    caracteristicasGerais: [
      'Visionário com forte senso de propósito',
      'Extremamente intuitivo sobre pessoas',
      'Orientado por valores e princípios profundos',
      'Toma decisões considerando impacto de longo prazo',
      'Comunicação profunda e significativa',
      'Busca constantemente crescimento e autenticidade'
    ],
    pontoFortes: [
      'Visão excepcional de potencial humano',
      'Capacidade de compreender necessidades profundas',
      'Criatividade e inovação conceitual',
      'Integridade e autenticidade pessoal',
      'Habilidade de inspirar mudanças significativas',
      'Pensamento estratégico humanizado'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior assertividade',
      'Ser mais prático na implementação',
      'Comunicar necessidades pessoais',
      'Lidar melhor com críticas',
      'Desenvolver maior tolerância a conflitos',
      'Focar em resultados mensuráveis'
    ],
    pontosDesenvolver: [
      'Liderança em situações conflituosas',
      'Comunicação em grupos grandes',
      'Gestão operacional e administrativa',
      'Networking e autopromoção',
      'Tomada de decisões rápidas',
      'Delegação e controle de qualidade'
    ],
    ambientesIdeais: [
      'Organizações com propósito claro',
      'Ambientes que valorizam autenticidade',
      'Culturas focadas em impacto social',
      'Trabalho significativo e transformador',
      'Equipes pequenas e coesas'
    ],
    estilosTrabalho: [
      'Trabalho independente ou pequenas equipes',
      'Projetos com impacto significativo',
      'Ambiente tranquilo e reflexivo',
      'Tempo para planejamento e reflexão',
      'Alinhamento com valores pessoais'
    ],
    relacionamentos: [
      'Relacionamentos profundos e autênticos',
      'Círculo pequeno de amigos íntimos',
      'Mentor e conselheiro natural',
      'Comunicação significativa e profunda',
      'Valoriza autenticidade e crescimento mútuo'
    ],
    comunicacao: 'Profunda, intuitiva e inspiradora. Excelente em comunicação individual e escrita.',
    carreirasSugeridas: [
      'Psicologia e Terapia',
      'Consultoria Organizacional',
      'Educação e Desenvolvimento',
      'Escritura e Jornalismo',
      'Arte e Design',
      'ONGs e Causas Sociais',
      'Coaching e Mentoria'
    ]
  },

  'ENFP': {
    tipo: 'ENFP',
    nome: 'O Ativista',
    descricao: 'Pessoas entusiasmadas, criativas e orientadas para possibilidades. Excelentes em inspirar outros e gerar mudanças positivas.',
    caracteristicasGerais: [
      'Extremamente entusiasta e otimista',
      'Orientado para possibilidades humanas',
      'Altamente criativo e inovador',
      'Toma decisões baseadas em valores e potencial',
      'Comunicação carismática e inspiradora',
      'Busca constantemente significado e autenticidade'
    ],
    pontoFortes: [
      'Capacidade excepcional de inspirar outros',
      'Criatividade e inovação constantes',
      'Habilidades interpessoais excepcionais',
      'Energia e entusiasmo contagiantes',
      'Visão de potencial e possibilidades',
      'Adaptabilidade e flexibilidade'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior foco e persistência',
      'Melhorar habilidades de organização',
      'Ser mais consistente em rotinas',
      'Considerar aspectos práticos e financeiros',
      'Desenvolver maior paciência com detalhes',
      'Focar em conclusão de projetos'
    ],
    pontosDesenvolver: [
      'Gestão de projetos e implementação',
      'Planejamento financeiro e operacional',
      'Atenção a detalhes e qualidade',
      'Persistência em tarefas rotineiras',
      'Análise crítica e objetiva',
      'Gestão de tempo e prioridades'
    ],
    ambientesIdeais: [
      'Organizações inovadoras e dinâmicas',
      'Ambientes criativos e colaborativos',
      'Culturas que valorizam diversidade',
      'Startups e empresas de impacto social',
      'Projetos de mudança e transformação'
    ],
    estilosTrabalho: [
      'Trabalho em equipe colaborativa',
      'Projetos criativos e significativos',
      'Ambiente flexível e estimulante',
      'Variedade e novos desafios',
      'Interação frequente com pessoas'
    ],
    relacionamentos: [
      'Rede ampla e diversificada de amigos',
      'Relacionamentos calorosos e autênticos',
      'Mentor e inspirador natural',
      'Comunicação expressiva e emocional',
      'Valoriza autenticidade e crescimento'
    ],
    comunicacao: 'Carismática, inspiradora e emocional. Excelente em motivar e engajar audiências.',
    lideranca: 'Liderança inspiradora e visionária, focada em potencial humano e mudanças positivas.',
    carreirasSugeridas: [
      'Marketing e Comunicação',
      'Recursos Humanos',
      'Arte e Design',
      'Psicologia e Aconselhamento',
      'Educação e Treinamento',
      'Empreendedorismo Social',
      'Mídia e Entretenimento'
    ]
  },

  'INFP': {
    tipo: 'INFP',
    nome: 'O Mediador',
    descricao: 'Pessoas idealistas, leais e orientadas por valores. Excelentes em buscar autenticidade e promover crescimento pessoal.',
    caracteristicasGerais: [
      'Orientado por valores profundos e autenticidade',
      'Extremamente empático e compreensivo',
      'Focado em potencial e crescimento pessoal',
      'Toma decisões baseadas em princípios',
      'Comunicação autêntica e significativa',
      'Busca constantemente harmonia e propósito'
    ],
    pontoFortes: [
      'Capacidade excepcional de compreender pessoas',
      'Criatividade e originalidade',
      'Integridade e autenticidade pessoal',
      'Habilidade de mediar conflitos',
      'Visão de potencial humano',
      'Flexibilidade e adaptabilidade'
    ],
    oportunidadesMelhoria: [
      'Desenvolver maior assertividade',
      'Ser mais prático na implementação',
      'Melhorar habilidades de organização',
      'Comunicar necessidades pessoais',
      'Lidar melhor com críticas',
      'Focar em objetivos mensuráveis'
    ],
    pontosDesenvolver: [
      'Liderança e tomada de iniciativa',
      'Comunicação em grupos grandes',
      'Gestão de projetos e prazos',
      'Análise crítica e objetiva',
      'Networking e autopromoção',
      'Negociação e confronto'
    ],
    ambientesIdeais: [
      'Organizações com valores alinhados',
      'Ambientes que valorizam autenticidade',
      'Culturas inclusivas e empáticas',
      'Trabalho com propósito e significado',
      'Equipes pequenas e colaborativas'
    ],
    estilosTrabalho: [
      'Trabalho independente e reflexivo',
      'Projetos significativos e criativos',
      'Ambiente flexível e acolhedor',
      'Tempo para reflexão e desenvolvimento',
      'Alinhamento com valores pessoais'
    ],
    relacionamentos: [
      'Relacionamentos profundos e leais',
      'Círculo pequeno de amigos íntimos',
      'Valoriza autenticidade e compreensão',
      'Comunicação empática e cuidadosa',
      'Busca conexões significativas'
    ],
    comunicacao: 'Autêntica, empática e cuidadosa. Excelente em comunicação individual e escrita criativa.',
    carreirasSugeridas: [
      'Psicologia e Terapia',
      'Arte e Escritura',
      'Educação e Desenvolvimento',
      'Trabalho Social',
      'Design e Criação',
      'ONGs e Causas Sociais',
      'Jornalismo e Comunicação'
    ]
  }
};

export function getMbtiProfile(tipo: string): MbtiProfile | null {
  return mbtiProfiles[tipo.toUpperCase()] || null;
}

export function getAllMbtiTypes(): string[] {
  return Object.keys(mbtiProfiles);
}