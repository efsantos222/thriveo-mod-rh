import React, { useState } from 'react';
import { generateAiPrompt } from '../services/geminiService';
import { savePrompt, getPrompts, deletePrompt } from '../services/storageService';
import { PromptRequest, SavedPrompt, User } from '../types';
import { v4 as uuidv4 } from 'uuid';

interface GeneratorProps {
  user: User;
}

const Generator: React.FC<GeneratorProps> = ({ user }) => {
  const [loading, setLoading] = useState(false);
  const [generatedPrompt, setGeneratedPrompt] = useState('');
  const [inputs, setInputs] = useState<PromptRequest>({
    topic: '',
    context: '',
    tone: 'Profissional e Direto',
    format: 'Lista estruturada'
  });
  const [activeTab, setActiveTab] = useState<'GENERATE' | 'SAVED'>('GENERATE');
  const [savedPrompts, setSavedPrompts] = useState<SavedPrompt[]>([]);
  const [refreshTrigger, setRefreshTrigger] = useState(0);
  const [loadingSaved, setLoadingSaved] = useState(false);

  const handleGenerate = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      const result = await generateAiPrompt(inputs);
      setGeneratedPrompt(result);
    } catch (error) {
      alert('Erro ao gerar prompt. Verifique se a chave da API está válida no código.');
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    if (!generatedPrompt) return;
    setLoading(true); // Reutiliza loading state visual
    try {
        const newPrompt: SavedPrompt = {
        id: uuidv4(),
        title: inputs.topic || 'Sem título',
        content: generatedPrompt,
        tags: [inputs.tone, inputs.format],
        createdAt: new Date().toISOString(),
        userId: user.id
        };
        await savePrompt(newPrompt);
        alert("Prompt salvo com sucesso!");
        setActiveTab('SAVED');
    } catch (e) {
        alert("Erro ao salvar prompt.");
    } finally {
        setLoading(false);
    }
  };

  const handleCopy = (text: string) => {
    navigator.clipboard.writeText(text);
    alert('Copiado para área de transferência!');
  };

  // Load saved prompts effect
  React.useEffect(() => {
    if (activeTab === 'SAVED') {
      const fetchSaved = async () => {
        setLoadingSaved(true);
        try {
            const prompts = await getPrompts(user.id);
            setSavedPrompts(prompts);
        } catch (e) {
            console.error(e);
        } finally {
            setLoadingSaved(false);
        }
      }
      fetchSaved();
    }
  }, [activeTab, user.id, refreshTrigger]);

  const handleDelete = async (id: string) => {
      if(confirm("Tem certeza que deseja excluir?")) {
          await deletePrompt(id);
          setRefreshTrigger(prev => prev + 1); // Re-run effect
      }
  }

  return (
    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
      {/* Sidebar / Tabs */}
      <div className="lg:col-span-3 space-y-4">
        <div className="bg-slate-800 border border-slate-700 rounded-xl p-2">
          <button
            onClick={() => setActiveTab('GENERATE')}
            className={`w-full text-left px-4 py-3 rounded-lg flex items-center gap-3 transition-all ${activeTab === 'GENERATE' ? 'bg-purple-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-700'}`}
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
              <path strokeLinecap="round" strokeLinejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
            </svg>
            Gerador de Prompt
          </button>
          <button
            onClick={() => setActiveTab('SAVED')}
            className={`w-full text-left px-4 py-3 rounded-lg flex items-center gap-3 transition-all mt-2 ${activeTab === 'SAVED' ? 'bg-purple-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-700'}`}
          >
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
              <path strokeLinecap="round" strokeLinejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
            </svg>
            Prompts Salvos
          </button>
        </div>
        
        {activeTab === 'GENERATE' && (
            <div className="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                <h4 className="text-slate-300 text-sm font-semibold mb-2">Dica Pro</h4>
                <p className="text-xs text-slate-400">
                    Seja específico no campo de contexto para obter resultados mais alinhados com sua necessidade.
                </p>
            </div>
        )}
      </div>

      {/* Main Area */}
      <div className="lg:col-span-9">
        {activeTab === 'GENERATE' ? (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Input Form */}
            <div className="bg-slate-800 border border-slate-700 rounded-xl p-6 shadow-lg h-fit">
              <h3 className="text-xl font-bold text-white mb-6">Configuração</h3>
              <form onSubmit={handleGenerate} className="space-y-5">
                <div>
                  <label className="block text-sm font-medium text-slate-300 mb-2">Tópico Principal</label>
                  <input
                    type="text"
                    required
                    value={inputs.topic}
                    onChange={(e) => setInputs({...inputs, topic: e.target.value})}
                    className="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white outline-none"
                    placeholder="Ex: Marketing Digital, Código Python, Email de Vendas..."
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-slate-300 mb-2">Contexto e Detalhes</label>
                  <textarea
                    rows={4}
                    required
                    value={inputs.context}
                    onChange={(e) => setInputs({...inputs, context: e.target.value})}
                    className="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white outline-none resize-none"
                    placeholder="Descreva o cenário, público-alvo ou restrições..."
                  />
                </div>
                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">Tom de Voz</label>
                        <select
                             value={inputs.tone}
                             onChange={(e) => setInputs({...inputs, tone: e.target.value})}
                             className="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-purple-500 text-white outline-none"
                        >
                            <option>Profissional</option>
                            <option>Criativo</option>
                            <option>Acadêmico</option>
                            <option>Persuasivo</option>
                            <option>Humorístico</option>
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">Formato</label>
                        <select
                             value={inputs.format}
                             onChange={(e) => setInputs({...inputs, format: e.target.value})}
                             className="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-purple-500 text-white outline-none"
                        >
                            <option>Texto Corrido</option>
                            <option>Lista (Bullet Points)</option>
                            <option>Código</option>
                            <option>Tabela</option>
                            <option>Roteiro Passo-a-Passo</option>
                        </select>
                    </div>
                </div>
                <button
                  type="submit"
                  disabled={loading}
                  className="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold rounded-lg shadow-lg shadow-purple-500/25 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                  {loading ? (
                    <>
                      <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Processando...
                    </>
                  ) : (
                    <>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                        </svg>
                        Gerar Prompt
                    </>
                  )}
                </button>
              </form>
            </div>

            {/* Output Area */}
            <div className="flex flex-col h-full">
                 <div className={`bg-slate-800 border border-slate-700 rounded-xl shadow-lg flex flex-col h-full min-h-[400px] relative overflow-hidden ${loading ? 'opacity-50' : ''}`}>
                    <div className="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-900/50">
                        <h3 className="text-lg font-bold text-white">Resultado</h3>
                        {generatedPrompt && (
                            <div className="flex gap-2">
                                <button onClick={() => handleCopy(generatedPrompt)} className="p-2 hover:bg-slate-700 rounded-lg text-slate-400 hover:text-white transition-colors" title="Copiar">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                    </svg>
                                </button>
                                <button onClick={handleSave} className="p-2 hover:bg-slate-700 rounded-lg text-slate-400 hover:text-purple-400 transition-colors" title="Salvar">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                                    </svg>
                                </button>
                            </div>
                        )}
                    </div>
                    <div className="flex-grow p-6 overflow-y-auto max-h-[500px] bg-slate-900/30">
                        {generatedPrompt ? (
                             <pre className="whitespace-pre-wrap font-sans text-slate-300 leading-relaxed">{generatedPrompt}</pre>
                        ) : (
                            <div className="h-full flex flex-col items-center justify-center text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-12 h-12 mb-3 opacity-50">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                                <p>Preencha o formulário para gerar seu prompt.</p>
                            </div>
                        )}
                    </div>
                 </div>
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-4">
             <div className="flex items-center justify-between mb-4">
                 <h3 className="text-2xl font-bold text-white">Meus Prompts</h3>
             </div>
             {loadingSaved ? (
                 <div className="text-center py-12">
                     <p className="text-slate-400">Carregando prompts...</p>
                 </div>
             ) : savedPrompts.length === 0 ? (
                 <div className="text-center py-12 bg-slate-800/50 rounded-xl border border-slate-700">
                     <p className="text-slate-400">Você ainda não tem prompts salvos.</p>
                 </div>
             ) : (
                 savedPrompts.map((prompt) => (
                     <div key={prompt.id} className="bg-slate-800 border border-slate-700 rounded-xl p-6 shadow-md hover:border-purple-500/50 transition-all group">
                         <div className="flex justify-between items-start mb-4">
                             <div>
                                 <h4 className="text-lg font-bold text-white mb-1">{prompt.title}</h4>
                                 <div className="flex gap-2 text-xs">
                                     {prompt.tags.map(tag => (
                                         <span key={tag} className="bg-slate-700 text-slate-300 px-2 py-0.5 rounded">{tag}</span>
                                     ))}
                                     <span className="text-slate-500 py-0.5">{new Date(prompt.createdAt).toLocaleDateString()}</span>
                                 </div>
                             </div>
                             <div className="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                  <button onClick={() => handleCopy(prompt.content)} className="p-2 hover:bg-slate-700 rounded text-slate-400 hover:text-white">
                                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-4 h-4">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                    </svg>
                                  </button>
                                  <button onClick={() => handleDelete(prompt.id)} className="p-2 hover:bg-red-900/20 rounded text-slate-400 hover:text-red-400">
                                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-4 h-4">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                      </svg>
                                  </button>
                             </div>
                         </div>
                         <div className="bg-slate-900/50 p-4 rounded-lg">
                             <p className="text-slate-400 text-sm line-clamp-3 font-mono">{prompt.content}</p>
                         </div>
                     </div>
                 ))
             )}
          </div>
        )}
      </div>
    </div>
  );
};

export default Generator;