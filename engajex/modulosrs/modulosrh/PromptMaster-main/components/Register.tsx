import React, { useState } from 'react';
import { saveUser, getUsers } from '../services/storageService';
import { UserStatus } from '../types';
import { v4 as uuidv4 } from 'uuid';

interface RegisterProps {
  onSwitchToLogin: () => void;
}

const Register: React.FC<RegisterProps> = ({ onSwitchToLogin }) => {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [step, setStep] = useState<'FORM' | 'PAYMENT'>('FORM');
  const [loading, setLoading] = useState(false);

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    
    try {
      // Verifica se email já existe no DB
      const users = await getUsers();
      if (users.find(u => u.email === email)) {
        alert('Este email já está cadastrado.');
        setLoading(false);
        return;
      }
      setStep('PAYMENT');
    } catch (err) {
      alert('Erro de conexão.');
    } finally {
      setLoading(false);
    }
  };

  const confirmRegistration = async () => {
    setLoading(true);
    try {
      const newUser = {
        id: uuidv4(),
        name,
        email,
        password,
        status: UserStatus.PENDING,
        createdAt: new Date().toISOString()
      };
      await saveUser(newUser);
      alert('Cadastro realizado! Aguarde a aprovação do administrador após a confirmação do pagamento.');
      onSwitchToLogin();
    } catch (err) {
      alert('Erro ao salvar usuário. Tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  if (step === 'PAYMENT') {
    return (
      <div className="flex items-center justify-center min-h-[80vh]">
        <div className="w-full max-w-lg bg-slate-800/50 backdrop-blur border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
          <div className="p-8 text-center">
            <div className="w-16 h-16 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-8 h-8 text-green-500">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            
            <h2 className="text-2xl font-bold text-white mb-2">Pagamento via Pix</h2>
            <p className="text-slate-400 mb-6">Para liberar seu acesso, realize o pagamento e aguarde aprovação.</p>
            
            <div className="bg-slate-900 p-6 rounded-xl border border-slate-700 mb-6">
              <p className="text-sm text-slate-500 mb-2">Chave Pix (E-mail)</p>
              <p className="text-lg font-mono text-purple-300 select-all">efsantos@proftest.com.br</p>
              <p className="text-xs text-slate-500 mt-4">Valor de adesão: <span className="text-white font-bold">R$ 29,90</span></p>
            </div>

            <div className="space-y-4">
              <p className="text-sm text-slate-400 bg-yellow-500/10 p-3 rounded border border-yellow-500/20">
                ⚠️ Após o pagamento, o administrador liberará seu acesso manualmente.
              </p>

              <button
                onClick={confirmRegistration}
                disabled={loading}
                className="w-full py-3 px-4 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-lg shadow-lg shadow-green-500/20 transition-all flex justify-center"
              >
                 {loading ? 'Processando...' : 'Já realizei o pagamento'}
              </button>
              
              <button
                onClick={() => setStep('FORM')}
                disabled={loading}
                className="text-slate-400 hover:text-white text-sm"
              >
                Voltar
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="flex items-center justify-center min-h-[80vh]">
      <div className="w-full max-w-md bg-slate-800/50 backdrop-blur border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
        <div className="p-8">
          <div className="text-center mb-8">
            <h2 className="text-3xl font-bold text-white mb-2">Criar Conta</h2>
            <p className="text-slate-400">Junte-se ao PromptMaster AI.</p>
          </div>

          <form onSubmit={handleRegister} className="space-y-5">
            <div>
              <label className="block text-sm font-medium text-slate-300 mb-2">Nome Completo</label>
              <input
                type="text"
                required
                value={name}
                onChange={(e) => setName(e.target.value)}
                className="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white placeholder-slate-500 outline-none transition-all"
                placeholder="João Silva"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-300 mb-2">Email</label>
              <input
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white placeholder-slate-500 outline-none transition-all"
                placeholder="seu@email.com"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-300 mb-2">Senha</label>
              <input
                type="password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white placeholder-slate-500 outline-none transition-all"
                placeholder="••••••••"
              />
            </div>
            <button
              type="submit"
              disabled={loading}
              className="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold rounded-lg shadow-lg shadow-purple-500/25 transition-all transform hover:scale-[1.02] disabled:opacity-50 flex justify-center"
            >
              {loading ? 'Verificando...' : 'Continuar para Pagamento'}
            </button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-slate-400 text-sm">
              Já tem uma conta?{' '}
              <button onClick={onSwitchToLogin} className="text-purple-400 hover:text-purple-300 font-medium transition-colors">
                Entrar
              </button>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Register;