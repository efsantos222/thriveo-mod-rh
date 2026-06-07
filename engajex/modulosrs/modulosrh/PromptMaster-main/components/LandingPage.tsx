import React from 'react';

interface LandingPageProps {
  onNavigate: (view: any) => void;
}

const LandingPage: React.FC<LandingPageProps> = ({ onNavigate }) => {
  return (
    <div className="space-y-20 pb-10">
      {/* Hero Section */}
      <section className="relative pt-10 pb-20 lg:pt-20 lg:pb-28 overflow-hidden">
        <div className="absolute top-0 left-1/2 w-full -translate-x-1/2 h-full z-0 pointer-events-none">
          <div className="absolute top-20 left-1/4 w-72 h-72 bg-purple-600/20 rounded-full blur-3xl mix-blend-screen animate-pulse"></div>
          <div className="absolute bottom-10 right-1/4 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl mix-blend-screen"></div>
        </div>
        
        <div className="relative z-10 text-center max-w-5xl mx-auto px-4">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-900/30 border border-purple-500/30 text-purple-300 text-sm font-medium mb-8 backdrop-blur-sm">
            <span className="relative flex h-2 w-2">
              <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
              <span className="relative inline-flex rounded-full h-2 w-2 bg-purple-500"></span>
            </span>
            Tecnologia Gemini 2.5 Flash Integration
          </div>
          
          <h1 className="text-5xl md:text-7xl font-bold text-white tracking-tight mb-6 leading-[1.1]">
            Engenharia de Prompts <br />
            <span className="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-400 to-indigo-400">Profissional & Segura</span>
          </h1>
          
          <p className="text-xl text-slate-400 mb-10 max-w-2xl mx-auto leading-relaxed">
            Não gaste horas testando comandos. Nossa plataforma gera, otimiza e armazena prompts de alta performance para IA, garantindo conformidade e resultados superiores.
          </p>
          
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button 
              onClick={() => onNavigate('REGISTER')}
              className="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-purple-500/25 transition-all transform hover:-translate-y-1 text-lg"
            >
              Liberar Acesso Agora
            </button>
            <button 
              onClick={() => onNavigate('LOGIN')}
              className="w-full sm:w-auto px-8 py-4 bg-slate-800/50 hover:bg-slate-800 text-white font-semibold rounded-xl border border-slate-700 hover:border-slate-600 transition-all backdrop-blur-sm"
            >
              Área de Membros
            </button>
          </div>
        </div>
      </section>

      {/* Mockup Showcase */}
      <section className="max-w-6xl mx-auto px-4 -mt-10 relative z-10">
          <div className="relative rounded-2xl bg-slate-900/50 border border-slate-800 shadow-2xl overflow-hidden backdrop-blur-sm group">
              <div className="absolute top-0 w-full h-1 bg-gradient-to-r from-purple-500 via-pink-500 to-indigo-500"></div>
              <div className="p-4 border-b border-slate-800 flex gap-2">
                  <div className="w-3 h-3 rounded-full bg-red-500/50"></div>
                  <div className="w-3 h-3 rounded-full bg-yellow-500/50"></div>
                  <div className="w-3 h-3 rounded-full bg-green-500/50"></div>
              </div>
              <div className="p-8 md:p-12 grid md:grid-cols-2 gap-8 items-center">
                  <div className="space-y-4">
                      <div className="h-4 bg-slate-800 rounded w-1/4"></div>
                      <div className="h-8 bg-slate-700 rounded w-3/4"></div>
                      <div className="h-32 bg-slate-800/50 rounded w-full border border-slate-700/50 p-4">
                          <p className="text-slate-500 font-mono text-sm">
                              > Atue como um especialista em Marketing Digital...<br/>
                              > Objetivo: Criar copy para Instagram...<br/>
                              > Tom: Persuasivo e Urgente...
                          </p>
                      </div>
                      <div className="flex gap-2">
                          <div className="h-10 bg-purple-600 rounded w-1/3"></div>
                          <div className="h-10 bg-slate-700 rounded w-1/4"></div>
                      </div>
                  </div>
                  <div className="bg-slate-950 rounded-lg p-6 border border-slate-800 shadow-inner">
                      <div className="flex justify-between items-center mb-4">
                          <span className="text-xs font-bold text-green-400 uppercase tracking-wider">Prompt Gerado</span>
                          <svg className="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                      </div>
                      <div className="space-y-2">
                          <div className="h-2 bg-slate-800 rounded w-full"></div>
                          <div className="h-2 bg-slate-800 rounded w-11/12"></div>
                          <div className="h-2 bg-slate-800 rounded w-full"></div>
                          <div className="h-2 bg-slate-800 rounded w-4/5"></div>
                          <div className="h-2 bg-slate-800 rounded w-full"></div>
                      </div>
                  </div>
              </div>
          </div>
      </section>

      {/* Features Grid */}
      <section className="max-w-7xl mx-auto px-4 pt-10">
        <h2 className="text-3xl md:text-4xl font-bold text-center text-white mb-16">
            Potencialize seus Resultados
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {[
            {
              title: "Geração Assistida",
              desc: "Não sabe por onde começar? Insira apenas o tópico e nossa IA constrói a estrutura complexa do prompt para você.",
              icon: (
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-6 h-6 text-white">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                </svg>
              ),
              color: "bg-purple-500"
            },
            {
              title: "Gestão de Biblioteca",
              desc: "Chega de bloco de notas. Salve, organize e encontre seus melhores prompts em segundos. Sua base de conhecimento pessoal.",
              icon: (
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-6 h-6 text-white">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
              ),
              color: "bg-blue-500"
            },
            {
              title: "Adaptação de Contexto",
              desc: "Altere o tom de voz, formato de saída (Tabela, Código, Texto) e especificidade com cliques simples.",
              icon: (
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-6 h-6 text-white">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                </svg>
              ),
              color: "bg-pink-500"
            }
          ].map((feature, idx) => (
            <div key={idx} className="p-8 rounded-2xl bg-slate-800/40 border border-slate-700 hover:border-slate-600 transition-all hover:-translate-y-1 group relative overflow-hidden">
              <div className={`w-12 h-12 ${feature.color} rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-${feature.color}/20`}>
                {feature.icon}
              </div>
              <h3 className="text-xl font-bold text-white mb-3">{feature.title}</h3>
              <p className="text-slate-400 leading-relaxed">{feature.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Tutorials / How it Works */}
      <section className="bg-slate-900/80 py-20 border-y border-slate-800">
        <div className="max-w-7xl mx-auto px-4">
            <div className="text-center mb-16">
                <h2 className="text-3xl md:text-4xl font-bold text-white mb-4">Como Funciona</h2>
                <p className="text-slate-400">Três passos simples para a excelência em IA.</p>
            </div>
            
            <div className="grid md:grid-cols-3 gap-8 relative">
                {/* Connecting Line (Desktop only) */}
                <div className="hidden md:block absolute top-8 left-[16%] right-[16%] h-0.5 bg-gradient-to-r from-purple-900 via-slate-700 to-purple-900 -z-10"></div>

                <div className="text-center">
                    <div className="w-16 h-16 mx-auto bg-slate-800 border-4 border-slate-900 rounded-full flex items-center justify-center text-xl font-bold text-white mb-6 relative z-10 shadow-xl">1</div>
                    <h3 className="text-xl font-bold text-white mb-2">Defina</h3>
                    <p className="text-slate-400 text-sm px-4">Insira o tópico e o contexto do seu problema. Quanto mais detalhes, melhor.</p>
                </div>
                <div className="text-center">
                    <div className="w-16 h-16 mx-auto bg-purple-600 border-4 border-slate-900 rounded-full flex items-center justify-center text-xl font-bold text-white mb-6 relative z-10 shadow-xl shadow-purple-500/20">2</div>
                    <h3 className="text-xl font-bold text-white mb-2">Personalize</h3>
                    <p className="text-slate-400 text-sm px-4">Escolha o tom de voz (Profissional, Criativo, etc.) e o formato de entrega da IA.</p>
                </div>
                <div className="text-center">
                    <div className="w-16 h-16 mx-auto bg-slate-800 border-4 border-slate-900 rounded-full flex items-center justify-center text-xl font-bold text-white mb-6 relative z-10 shadow-xl">3</div>
                    <h3 className="text-xl font-bold text-white mb-2">Use & Salve</h3>
                    <p className="text-slate-400 text-sm px-4">Copie o prompt gerado instantaneamente e salve na sua biblioteca segura.</p>
                </div>
            </div>
        </div>
      </section>

      {/* LGPD Section */}
      <section className="max-w-4xl mx-auto px-4">
        <div className="bg-gradient-to-br from-slate-800 to-slate-900 p-8 md:p-12 rounded-3xl border border-slate-700 flex flex-col md:flex-row gap-10 items-center">
            <div className="flex-1">
                <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-900/30 border border-green-500/30 text-green-400 text-xs font-bold mb-4 uppercase tracking-wider">
                    Segurança em 1º Lugar
                </div>
                <h2 className="text-2xl md:text-3xl font-bold text-white mb-4">Compliance LGPD & Privacidade</h2>
                <p className="text-slate-300 mb-6 leading-relaxed">
                    Entendemos que prompts podem conter estratégias de negócio sensíveis. 
                    Nossa arquitetura garante que seus dados salvos sejam persistidos de forma segura e isolada.
                    Não utilizamos seus prompts privados para treinar modelos públicos.
                </p>
                <ul className="space-y-2">
                    <li className="flex items-center gap-2 text-slate-400 text-sm">
                        <svg className="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                        Dados isolados por usuário
                    </li>
                    <li className="flex items-center gap-2 text-slate-400 text-sm">
                        <svg className="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                        Sem compartilhamento com terceiros
                    </li>
                </ul>
            </div>
            <div className="flex-shrink-0">
                 <div className="w-48 h-48 bg-slate-800 rounded-full flex items-center justify-center border-4 border-slate-700 shadow-2xl">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={0.8} stroke="currentColor" className="w-24 h-24 text-green-500/80">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                     </svg>
                 </div>
            </div>
        </div>
      </section>

      {/* Pricing CTA */}
      <section className="max-w-5xl mx-auto px-4 py-10">
         <div className="relative bg-indigo-950 border border-indigo-500/30 rounded-3xl p-10 md:p-20 overflow-hidden text-center shadow-2xl shadow-indigo-900/50">
            {/* Decorative background */}
            <div className="absolute top-0 left-0 w-full h-full opacity-20 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-400 via-slate-900 to-slate-900"></div>
            
            <div className="relative z-10">
                <h2 className="text-3xl md:text-5xl font-bold text-white mb-6">Acesso Vitalício Profissional</h2>
                <p className="text-xl text-indigo-200 mb-10 max-w-2xl mx-auto">
                    Invista na sua produtividade. Acesso ilimitado ao gerador e biblioteca de prompts.
                </p>
                
                <div className="flex flex-col items-center justify-center mb-12">
                    <div className="flex items-end gap-2">
                        <span className="text-6xl font-bold text-white">R$ 29,90</span>
                        <span className="text-indigo-300 mb-2 font-medium">/ único</span>
                    </div>
                    <p className="text-sm text-indigo-400 mt-2 bg-indigo-900/50 px-3 py-1 rounded-full">Sem mensalidades</p>
                </div>
                
                <button 
                  onClick={() => onNavigate('REGISTER')}
                  className="w-full md:w-auto px-12 py-5 bg-white hover:bg-indigo-50 text-indigo-900 font-bold rounded-xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all text-lg flex items-center justify-center gap-2 mx-auto"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" className="w-5 h-5">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                  </svg>
                  Quero Acesso Imediato
                </button>
                
                <div className="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-indigo-300 max-w-lg mx-auto border-t border-indigo-800/50 pt-6">
                    <div className="flex items-center justify-center gap-1">
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                        Pagamento Seguro Pix
                    </div>
                    <div className="flex items-center justify-center gap-1">
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                        Ativação Rápida
                    </div>
                    <div className="flex items-center justify-center gap-1">
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                        Suporte Dedicado
                    </div>
                </div>
            </div>
         </div>
      </section>
    </div>
  );
};

export default LandingPage;