# 🎯 Sistema de Avaliação DISC

Um sistema completo de avaliação de personalidade DISC desenvolvido em Python, que identifica perfis comportamentais em quatro dimensões principais.

## 📋 Índice

- [Sobre o Teste DISC](#sobre-o-teste-disc)
- [Características](#características)
- [Instalação](#instalação)
- [Uso](#uso)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Perfis DISC](#perfis-disc)
- [Exemplos](#exemplos)
- [API](#api)
- [Contribuindo](#contribuindo)

## 🎓 Sobre o Teste DISC

O teste DISC é uma ferramenta de avaliação comportamental desenvolvida pelo psicólogo William Moulton Marston. Ele classifica os padrões de comportamento das pessoas em quatro dimensões:

- 🔴 **D - Dominância (Executor)**: Como você responde a problemas e desafios
- 🟡 **I - Influência (Comunicador)**: Como você influencia outras pessoas
- 🟢 **S - Estabilidade (Apoiador)**: Como você responde ao ritmo e mudanças
- 🔵 **C - Conformidade (Analista)**: Como você responde a regras e procedimentos

## ✨ Características

- ✅ **20 perguntas cuidadosamente selecionadas** para avaliação precisa
- 📊 **Análise detalhada** com percentuais e gráficos visuais
- 🎯 **Identificação de perfis primários e secundários**
- 💡 **Perfis combinados** quando duas características são equilibradas
- 📝 **Relatórios completos** com pontos fortes, fraquezas e recomendações
- 💾 **Exportação de resultados** em TXT e JSON
- 🖥️ **Interface interativa** via linha de comando
- 🎮 **Modo demonstração** para testar o sistema
- 🌐 **Suporte completo em português**

## 🚀 Instalação

### Requisitos

- Python 3.7 ou superior

### Instalação Rápida

```bash
# Clone o repositório
git clone <repository-url>

# Navegue até o diretório
cd disc_assessment

# Nenhuma dependência externa necessária! O sistema usa apenas a biblioteca padrão do Python
```

## 💻 Uso

### Modo Interativo (Recomendado)

```bash
python main.py
```

Este modo oferece:
- Menu interativo completo
- Perguntas apresentadas uma por uma
- Barra de progresso visual
- Opções para salvar e exportar resultados

### Modo Demonstração

```bash
python main.py --demo
```

Executa o teste com respostas pré-definidas para demonstrar o funcionamento.

### Ajuda

```bash
python main.py --help
```

### Uso Programático

```python
from disc_assessment import DISCTest, DISCResults

# Criar e executar teste
test = DISCTest()
results = test.run_test()

# Exibir resultados
results.display_results()

# Salvar resultados
results.save_to_file("meu_resultado.txt")
results.export_json("meu_resultado.json")

# Obter resumo
summary = results.get_summary()
print(f"Perfil primário: {summary['primary_profile']}")
```

## 📁 Estrutura do Projeto

```
disc_assessment/
├── __init__.py           # Inicialização do pacote
├── main.py              # Ponto de entrada principal
├── disc_test.py         # Lógica do teste e interação
├── disc_data.py         # Perguntas e descrições dos perfis
├── disc_results.py      # Processamento e exibição de resultados
└── README.md           # Esta documentação
```

### Arquivos Principais

#### `disc_test.py`
Contém as classes principais:
- `DISCTest`: Administração do teste
- `InteractiveDISCTest`: Interface interativa com menu

#### `disc_data.py`
Define:
- `DISC_QUESTIONS`: 20 perguntas do teste
- `PROFILE_DESCRIPTIONS`: Descrições detalhadas dos perfis
- `COMBINATION_PROFILES`: Perfis combinados

#### `disc_results.py`
Processa e apresenta:
- Cálculo de pontuações
- Identificação de perfis
- Geração de relatórios
- Exportação de dados

## 🎭 Perfis DISC

### 🔴 D - Dominância (Executor)

**Características:**
- Direto e assertivo
- Orientado a resultados
- Gosta de desafios
- Tomada de decisão rápida

**Pontos Fortes:**
- Liderança natural
- Determinação
- Capacidade de decisão rápida

**Áreas de Atenção:**
- Pode ser impaciente
- Às vezes insensível
- Dificuldade em delegar

**Ambiente Ideal:** Competitivo, desafiador, com autonomia

### 🟡 I - Influência (Comunicador)

**Características:**
- Entusiasmado e otimista
- Sociável e persuasivo
- Expressivo emocionalmente
- Gosta de trabalhar com pessoas

**Pontos Fortes:**
- Excelentes habilidades de comunicação
- Capacidade de motivar
- Criatividade

**Áreas de Atenção:**
- Pode ser desorganizado
- Falta de atenção aos detalhes
- Dificuldade em cumprir prazos

**Ambiente Ideal:** Social, dinâmico, com reconhecimento público

### 🟢 S - Estabilidade (Apoiador)

**Características:**
- Calmo e paciente
- Leal e confiável
- Prefere rotina
- Cooperativo

**Pontos Fortes:**
- Paciência
- Lealdade
- Trabalho em equipe

**Áreas de Atenção:**
- Resistente a mudanças
- Dificuldade em dizer não
- Evita confrontos

**Ambiente Ideal:** Estável, previsível, com equipe harmoniosa

### 🔵 C - Conformidade (Analista)

**Características:**
- Analítico e preciso
- Orientado a detalhes
- Sistemático
- Busca qualidade

**Pontos Fortes:**
- Atenção aos detalhes
- Pensamento analítico
- Qualidade no trabalho

**Áreas de Atenção:**
- Pode ser perfeccionista
- Dificuldade com ambiguidade
- Lentidão nas decisões

**Ambiente Ideal:** Organizado, estruturado, com padrões claros

## 📊 Exemplos de Resultados

### Perfil Primário Dominante

```
========================================================================
                    RESULTADOS DO TESTE DISC
========================================================================

📊 PONTUAÇÃO POR DIMENSÃO:
------------------------------------------------------------------------
  D: ██████████████████████████████ 12 pontos ( 60.0%)
  I: ██████████                      4 pontos ( 20.0%)
  S: ████                            2 pontos ( 10.0%)
  C: ████                            2 pontos ( 10.0%)
------------------------------------------------------------------------
  Total: 20 pontos

========================================================================
🎯 PERFIL PRIMÁRIO: D - Dominância (Executor)
========================================================================
```

### Perfil Combinado

```
💡 PERFIL COMBINADO: DI
   Executor-Comunicador: Líder carismático, assertivo e sociável

   Você apresenta características equilibradas de:
   • D (45.0%) - Dominância (Executor)
   • I (40.0%) - Influência (Comunicador)
```

## 🔧 API

### Classe `DISCTest`

```python
class DISCTest:
    """Administra o teste DISC"""

    def __init__(self):
        """Inicializa o teste"""

    def run_test(self) -> DISCResults:
        """Executa o teste completo interativamente"""

    def quick_test(self, answers: list) -> DISCResults:
        """Executa teste com respostas pré-definidas"""

    def reset(self):
        """Reseta o estado do teste"""
```

### Classe `DISCResults`

```python
class DISCResults:
    """Processa e exibe resultados do teste"""

    def __init__(self, scores: Dict[str, int]):
        """Inicializa com pontuações DISC"""

    def display_results(self):
        """Exibe resultados formatados"""

    def save_to_file(self, filename: str = "disc_results.txt"):
        """Salva resultados em arquivo TXT"""

    def export_json(self, filename: str = "disc_results.json"):
        """Exporta resultados como JSON"""

    def get_summary(self) -> Dict:
        """Retorna resumo dos resultados"""

    def is_combination_profile(self) -> bool:
        """Verifica se é um perfil combinado"""
```

## 🎯 Aplicações do Teste DISC

- **Autoconhecimento**: Compreender melhor seu estilo comportamental
- **Desenvolvimento Pessoal**: Identificar áreas de melhoria
- **Seleção e Recrutamento**: Avaliar adequação a funções
- **Desenvolvimento de Lideranças**: Identificar estilos de liderança
- **Formação de Equipes**: Criar equipes balanceadas
- **Melhoria da Comunicação**: Adaptar comunicação aos diferentes perfis
- **Resolução de Conflitos**: Compreender diferentes perspectivas

## 📈 Recursos Futuros

- [ ] Interface web com Flask/Django
- [ ] Suporte a múltiplos idiomas
- [ ] Banco de dados para armazenar resultados
- [ ] Comparação entre múltiplos perfis
- [ ] Relatórios em PDF
- [ ] Gráficos interativos
- [ ] Integração com APIs REST
- [ ] Suporte a testes em equipe

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor:

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 👥 Autores

- **PromptMaster Development Team**

## 🙏 Agradecimentos

- William Moulton Marston - Criador do modelo DISC
- Todos os contribuidores do projeto

## 📞 Suporte

Para questões ou suporte, por favor abra uma issue no GitHub ou entre em contato através de:
- Email: support@example.com
- Website: https://example.com

---

⭐ Se este projeto foi útil para você, considere dar uma estrela no GitHub!
