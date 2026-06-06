<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Avaliação Comportamental</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            background-color: #f0f0f0;
        }

        #cadastroRespondente, #questionario, #resultado {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        #questionario {
            width: 100%; /* Faz com que o container ocupe toda a largura disponível */
    		height: 100%; /* Faz com que o container ocupe toda a altura disponível */
    		overflow-y: auto; /* Mantém a barra de rolagem se o conteúdo exceder a altura */
    		position: absolute; /* Permite o posicionamento exato dentro do body */
    		top: 0; /* Alinha o topo do container com o topo da página */
    		left: 0; /* Alinha a esquerda do container com a esquerda da página */
    		right: 0; /* Garante que o container se estenda até a direita da página */
    		bottom: 0; /* Garante que o container se estenda até o fundo da página */
        }                

        #questionario div {
            margin-bottom: 20px; /* Aumenta o espaçamento entre cada div de questão e os botões */
        }
        
        input[type="text"] {
            padding: 10px;
            margin-bottom: 10px;
            width: 300px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div id="cadastroRespondente">
        <h2>Avaliado</h2>
        <input type="text" id="nomeRespondente" placeholder="Nome do Respondente">
        <button onclick="iniciarQuestionario()">Iniciar Questionário</button>
    </div>

    <div id="questionario" style="display:none;">
        <!-- As questões serão inseridas aqui pelo JavaScript -->
    </div>

    <div id="resultado" style="display:none;">
		<canvas id="graficoResultado"></canvas>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questoes = [
                { pergunta: "Eu sou...", alternativas: ["Idealista, criativo e visionário", "Divertido, espiritual e benéfico", "Confiável, meticuloso e previsível", "Focado, determinado e persistente"], respostas: ["I", "C", "O", "A"] },
                { pergunta: "Eu gosto de...", alternativas: ["Ser piloto", "Conversar com os passageiros", "Planejar a viagem", "Explorar novas rotas"], respostas: ["A", "C", "O", "I"] },
                { pergunta: "Se você quiser se dar bem comigo...", alternativas: ["Me dê liberdade", "Me deixe saber sua expectativa", "Lidere, siga ou saia do caminho", "Seja amigável, carinhoso e compreensivo"], respostas: ["I", "O", "A", "C"] },
                { pergunta: "Para conseguir obter bons resultados é preciso...", alternativas: ["Ter incertezas", "Controlar o essencial", "Diversão e cerebração", "Planejar e obter recursos"], respostas: ["I", "O", "C", "A"] },
                { pergunta: "Eu me divirto quando...", alternativas: ["Estou me exercitando", "Tenho novidades", "Estou com outros", "Determino as regras"], respostas: ["A", "I", "C", "O"] },
                { pergunta: "Eu penso que...", alternativas: ["Unidos venceremos, dividos perderemos", "O ataque é melhor que a defesa", "É bom ser manso, mas andar com um porrete", "Um homem prevenido vale por dois"], respostas: ["C", "A", "I", "O"] },
                { pergunta: "Minha preocupação é...", alternativas: ["Gerar a idéia global", "Fazer com quem as pessoas gostem", "Fazer com que funcione", "Fazer com que aconteça"], respostas: ["I", "C", "O", "A"] },
                { pergunta: "Eu prefiro...", alternativas: ["Perguntas a respostas", "Ter todos os detalhes", "Vantagens a meu favor", "Que todos tenham a chance de ser ouvido"], respostas: ["I", "O", "A", "C"] },
                { pergunta: "Eu gosto de...", alternativas: ["Fazer progresso", "Construir memórias", "Fazer sentido", "Tornar as pessoas confortáveis"], respostas: ["A", "C", "O", "I"] },
                { pergunta: "Eu gosto de chegar...", alternativas: ["Na frente", "Junto", "Na hora", "Em outro lugar"], respostas: ["A", "C", "O", "I"] },
                { pergunta: "Um ótimo dia para mim é quando...", alternativas: ["Consigo fazer muitas coisas", "Me divirto com meus amigos", "Tudo segue conforme planejado", "Desfruto de coisas novas e estimulantes"], respostas: ["A", "C", "O", "I"] },
                { pergunta: "Eu vejo a morte como...", alternativas: ["Uma grande aventura misteriosa", "Oportunidade para rever os falecidos", "Um modo de receber recompensas", "Algo que sempe chega muito cedo"], respostas: ["I", "C", "O", "A"] },
                { pergunta: "Minha filosofia de vida é...", alternativas: ["Há ganhadores e perdedores, e eu acredito ser um ganhador", "Para eu ganhar, niguém precisa perder", "Para ganhar é preciso seguir as regras", "Para ganhar, é necessário inventar novas regras"], respostas: ["A", "C", "O", "I"] },
                { pergunta: "Eu sempre gostei de...", alternativas: ["Explorar", "Evitar surpresas", "Focalizar a meta", "Realizar uma abordagem natural"], respostas: ["I", "O", "A", "C"] },
                { pergunta: "Eu gosto de mudanças se...", alternativas: ["Me der uma vantagem competitiva", "For divertido e puder ser compartilhado", "Me der mais liberdade e variedade", "Melhorar ou me der mais controle"], respostas: ["A", "C", "I", "O"] },
                { pergunta: "Não existe nada de errado em...", alternativas: ["Se colocar na frente", "Colocar os outros na frente", "Mudar de idéia", "Ser consistente"], respostas: ["A", "C", "I", "O"] },
                { pergunta: "Eu gosto de buscar conselhos de...", alternativas: ["Pessoas bem sucedidas", "Anciões e conselheiros", "Autoridades no assunto", "Lugares, os mais estranhos"], respostas: ["A", "C", "O", "I"] },
                { pergunta: "Meu lema é...", alternativas: ["Fazer o que precisa ser feito", "Fazer bem feito", "Fazer junto com o grupo", "Simplesmente fazer"], respostas: ["I", "O", "C", "A"] },
                { pergunta: "Eu gosto de...", alternativas: ["Complexidade, mesmo se confuso", "Ordem e sistematização", "Calor humano e animação", "Coisas claras e simples"], respostas: ["I", "O", "C", "A"] },
                { pergunta: "Tempo para mim é...", alternativas: ["Algo que detesto disperdiçar", "Um grande ciclo", "Uma flecha que leva ao inevitável", "Irrelevante"], respostas: ["A", "C", "O", "I"] },
                { pergunta: "Se eu fosse biblionário...", alternativas: ["Faria doações para muitas entidades", "Criaria uma poupança avantajada", "Faria o que desse na cabeça", "Exibiria bastante com algumas pessoas"], respostas: ["C", "O", "I", "A"] },
                { pergunta: "Eu acredito que...", alternativas: ["O destino é mais importante que a jornada", "A jornada é mais importante que o destino", "Um centavo economizado é um centavo ganho", "Bastam um navio e uma estrela para navegar"], respostas: ["A", "C", "O", "I"] },
                { pergunta: "Eu acredito também que...", alternativas: ["Aquele que hesita está perdido", "De grão em grão a galinha enche o papo", "O que vai, volta", "Um sorriso ou uma careta é o mesmo para quem e cego"], respostas: ["A", "O", "C", "I"] },
                { pergunta: "Eu acredito ainda que...", alternativas: ["É melhor prudência do que arrependimento", "A autoridade deve ser desafiada", "Ganhar é fundamental", "O coletivo é mais importante do que o individual"], respostas: ["O", "I", "A", "C"] },
                { pergunta: "Eu penso que...", alternativas: ["Não é fácil ficar encurralado", "É preferível olhar, antes de pular", "Duas cabeças pensam melhor do que uma", "Se você não tem condições de competir, não compita"], respostas: ["A", "C", "O", "I"] },
                { pergunta: "Quando tomo decisões:", alternativas: ["Confio na minha intuição e experiência.", "Gosto de considerar o impacto nas pessoas envolvidas.", "Prefiro analisar todos os dados e fatos primeiro.", "Busco opções que mantenham a estabilidade e a ordem."], respostas: ["O", "C", "A", "I"] },
                { pergunta: "Em uma discussão, eu tendo a ser:", alternativas: ["Cauteloso e preciso.", "Direto e franco.", "Paciente e conciliador.", "Persuasivo e diplomático."], respostas: ["A", "O", "I", "C"]},
                { pergunta: "Prefiro trabalhar em um ambiente que seja:", alternativas: ["Competitivo e desafiador.", "Organizado e previsível.", "Amigável e colaborativo.", "Calmo e estável."], respostas: ["O", "A", "C", "I"]},
                { pergunta: "Minha abordagem para resolver problemas é mais:", alternativas: ["Assertiva e autônoma.", "Deliberada e metódica.", "Sistemática e analítica.", "Criativa e envolvente."], respostas: ["O", "I", "A", "C"]},
                { pergunta: "Eu me sinto mais motivado quando:", alternativas: ["Estou em um ambiente seguro e harmonioso.", "Posso interagir e trabalhar com outras pessoas.", "Posso trabalhar com precisão e qualidade.", "Tenho metas claras e desafios."], respostas: ["I", "C", "A", "O"]},
                { pergunta: "Quando comunico ideias ou informações, eu prefiro:", alternativas: ["Ser expressivo e persuasivo.", "Ser detalhado e exato.", "Ser breve e direto ao ponto.", "Ser diplomático e sincero."], respostas: ["C", "A", "O", "I"]},
                { pergunta: "Em situações de mudança, eu geralmente:", alternativas: ["Tomo a liderança e direciono os outros.", "Busco inspirar e motivar a equipe.", "Preocupo-me com os processos e detalhes.", "Busco manter ou restaurar a harmonia."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Minha abordagem para o planejamento é:", alternativas: ["Definir objetivos e agir rapidamente.", "Ser flexível e aberto a novas ideias.", "Ser cuidadoso e minucioso.", "Ser consistente e buscar estabilidade."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Quando estou sob pressão, eu tendo a:", alternativas: ["Enfrentar o desafio de frente.", "Procurar apoio e colaboração.", "Focar nos detalhes e na precisão.", "Manter a calma e procurar soluções práticas."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Prefiro tarefas que sejam:", alternativas: ["Desafiadoras e variadas.", "Interativas e dinâmicas.", "Estruturadas e ordenadas.", "Estáveis e previsíveis."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Quando trabalho em equipe, eu:", alternativas: ["Gosto de estabelecer direções e metas.", "Procuro criar um ambiente energético e ativo.", "Foco na precisão e na organização.", "Valorizo a cooperação e o consenso."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Em relação às regras e procedimentos, eu geralmente:", alternativas: ["Questiono e desafio se acho que há uma melhor maneira.", "Sou flexível e aberto a adaptações.", "Sigo rigorosamente e com atenção.", "Sigo, mas estou aberto a ajustes se necessário."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Quando se trata de detalhes e precisão no trabalho, eu:", alternativas: ["Prefiro focar na visão geral e nos resultados.", "Posso perder detalhes se estiver muito envolvido com as pessoas.", "Sou meticuloso e preciso.", "Sou cuidadoso, mas não me perco nos detalhes."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Minha abordagem para conflitos é:", alternativas: ["Enfrentá-los diretamente e resolver rapidamente.", "Tentar negociar e encontrar um meio-termo.", "Analisar os fatos e usar a lógica para resolver.", "Evitar confrontos e buscar harmonia."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Quando preciso aprender algo novo, eu:", alternativas: ["Prefiro aprender fazendo e experimentando.", "Gosto de interagir com outros e compartilhar ideias.", "Sigo instruções precisas e busco entender os detalhes.", "Aprendo melhor em um ambiente estruturado e tranquilo."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Minha abordagem para a gestão do tempo é:", alternativas: ["Focada em eficiência e cumprimento de prazos.", "Flexível, com espaço para ajustes e imprevistos.", "Rigorosa e metódica.", "Equilibrada, priorizando a consistência."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Quando se trata de liderança, eu:", alternativas: ["Sou decisivo e orientado para a ação.", "Sou inspirador e motivador.", "Sou metódico e baseado em regras.", "Sou apoiador e orientado para a equipe."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Minha maneira de lidar com informações novas é:", alternativas: ["Avaliar rapidamente e decidir como usar.", "Discutir com outros e explorar possibilidades.", "Analisar cuidadosamente e verificar a precisão.", "Integrar lentamente e garantir compreensão."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Em relação à tomada de riscos, eu:", alternativas: ["Sou ousado e disposto a correr riscos calculados.", "Estou aberto a riscos se houver potencial de recompensa.", "Prefiro evitar riscos e seguir o que é comprovado.", "Sou cauteloso e prefiro opções seguras." ], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Minha abordagem para inovação e novas ideias é:", alternativas: ["Ser um dos primeiros a experimentar e implementar.", "Apoiar se for emocionante e popular.", "Ser cético até ver dados e análises concretas.", "Aceitar lentamente, após considerar a estabilidade." ], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Quando estou em uma posição de autoridade, eu prefiro:", alternativas: ["Impor metas e expectativas claras.", "Encorajar o entusiasmo e a participação.", "Estabelecer regras claras e procedimentos.", "Criar um ambiente de trabalho seguro e harmonioso." ], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Quando me deparo com um desafio:", alternativas: ["Eu tomo a iniciativa para resolver o problema", "Procuro apoio e sugestões de outras pessoas.", "Sigo procedimentos estabelecidos para lidar com isso.", "Mantenho a calma e busco uma abordagem consistente"], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Em um projeto de equipe, eu geralmente:", alternativas: ["Lidero e tomo decisões importantes.", "Motivo e encorajo os membros da equipe.", "Garanto que as regras e os padrões sejam seguidos.", "Ofereço suporte e ajuda para manter a harmonia."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Meu estilo de trabalho é melhor descrito como:", alternativas: ["Orientado para resultados e eficiente.", "Colaborativo e persuasivo.", "Metódico e orientado para detalhes.", "Paciente e consistente."], respostas: ["O", "C", "A", "I"]},
                { pergunta: "Quando enfrento um problema, eu prefiro:", alternativas: ["Enfrentá-lo diretamente e de forma assertiva.", "Discutir várias opções e ideias com os outros.", "Analisar todas as informações disponíveis antes de decidir.", "Manter a calma e evitar alterações abruptas." ], respostas: ["O", "C", "A", "I"]}
                // Adicione mais questões aqui
            ];

            window.iniciarQuestionario = function() {
                const nomeRespondente = document.getElementById('nomeRespondente').value;
                if (!nomeRespondente) {
                    alert("Por favor, insira o seu nome completo:");
                    return;
                }
                
                // Faz uma solicitação AJAX para verificar se o nome já existe
    			const xhr = new XMLHttpRequest();
    			xhr.open("GET", "verificar_nome.php?nome=" + encodeURIComponent(nomeRespondente), true);
    			xhr.onload = function() {
        			if (this.status == 200) {
            			const resposta = JSON.parse(this.responseText);
            			if (resposta.nomeExiste) {
                			alert("Este nome já existe na pasta CSV. Por favor, insira um nome diferente.");
           				} else {
                			// O nome não existe, pode iniciar o questionário
                document.getElementById('cadastroRespondente').style.display = 'none';
                document.getElementById('questionario').style.display = 'block';
            }
        }
    };
    xhr.send();
};
                

                document.getElementById('cadastroRespondente').style.display = 'none';
                window.scrollTo(0, 0); // Rola a página para o topo
                const containerQuestionario = document.getElementById('questionario');
                containerQuestionario.style.display = 'block';
                
                questoes.forEach((questao, index) => {
                    const divQuestao = document.createElement('div');
                    divQuestao.innerHTML = `
                        <p>${questao.pergunta}</p>
                        ${questao.alternativas.map((alt, i) => 
                            `<label>
                                <input type="radio" name="questao${index}" value="${questao.respostas[i]}"> ${alt}
                            </label>`
                        ).join('<br>')}
                    `;
                    containerQuestionario.appendChild(divQuestao);
                });

                const btnSubmeter = document.createElement('button');
                btnSubmeter.textContent = 'Submeter Respostas';
                btnSubmeter.onclick = processarRespostas;
                containerQuestionario.appendChild(btnSubmeter);
            };

           
            function processarRespostas() {
                const respostas = {};
                let todasRespondidas = true;
                questoes.forEach((_, index) => {
                    const respostaSelecionada = document.querySelector(`input[name="questao${index}"]:checked`);
                    if (!respostaSelecionada) {
                        todasRespondidas = false; // Uma ou mais questões não foram respondidas
                    } else {
                        respostas[respostaSelecionada.value] = (respostas[respostaSelecionada.value] || 0) + 1;
                    }
                });

                if (!todasRespondidas) {
                    alert("Por favor, responda todas as questões antes de submeter.");
                    return; // Interrompe a função se todas as questões não foram respondidas
                }

                enviarDadosServidor(gerarCSV(respostas), document.getElementById('nomeRespondente').value);
                exibirResultados(respostas);
            }

            function gerarCSV(respostas) {
                let dadosCSV = 'Pergunta,Resposta\n';
                questoes.forEach((questao, index) => {
                    const respostaSelecionada = document.querySelector(`input[name="questao${index}"]:checked`);
                    if (respostaSelecionada) {
                        dadosCSV += `"${questao.pergunta}","${respostaSelecionada.value}"\n`;
                    } else {
                        dadosCSV += `"${questao.pergunta}",""\n`;
                    }
                });
                return dadosCSV;
            }

            function enviarDadosServidor(dadosCSV, nomeRespondente) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "salvar_csv.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                        //alert("Arquivo salvo com sucesso!");
                    }
                }
                xhr.send("dados=" + encodeURIComponent(dadosCSV) + "&nome=" + encodeURIComponent(nomeRespondente));
            }

            function exibirResultados(respostas) {
                document.getElementById('questionario').style.display = 'none';
                document.getElementById('resultado').style.display = 'block';

                const ctx = document.getElementById('graficoResultado').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Águia (I)', 'Gato (C)', 'Lobo (O)', 'Tubarão (A)'],
                        datasets: [{
                            data: [respostas['I'], respostas['C'], respostas['O'], respostas['A']],
                            backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56']
                        }]
                    }
                });
            }
            
        });
    </script>
</body>
</html>