import React from 'react';
import { User, UserStatus } from '../types';

interface LayoutProps {
  children: React.ReactNode;
  user: User | null;
  onLogout: () => void;
  currentView: string;
  onNavigate: (view: any) => void;
}

const Layout: React.FC<LayoutProps> = ({ children, user, onLogout, currentView, onNavigate }) => {
  const handleLogoClick = () => {
    if (user) {
      onNavigate('DASHBOARD');
    } else {
      onNavigate('LANDING');
    }
  };

  return (
    <div className="min-h-screen bg-slate-900 text-white flex flex-col font-sans">
      {/* Header */}
      <header className={`border-b border-slate-800/60 sticky top-0 z-50 transition-all ${currentView === 'LANDING' ? 'bg-slate-900/80 backdrop-blur-md' : 'bg-slate-900'}`}>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
          <div className="flex items-center gap-3 cursor-pointer group" onClick={handleLogoClick}>
            <div className="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/20 group-hover:shadow-purple-500/40 transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-6 h-6 text-white">
                <path strokeLinecap="round" strokeLinejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
              </svg>
            </div>
            <span className="font-bold text-xl tracking-tight text-white">
              PromptMaster<span className="text-purple-500">.AI</span>
            </span>
          </div>

          {user ? (
            <div className="flex items-center gap-4">
              <div className="hidden md:flex items-center gap-1 mr-2">
                 {user.status === UserStatus.ADMIN && (
                    <button 
                      onClick={() => onNavigate('ADMIN_PANEL')}
                      className={`px-4 py-2 rounded-lg text-sm font-medium transition-all ${currentView === 'ADMIN_PANEL' ? 'bg-purple-600/10 text-purple-400' : 'text-slate-400 hover:text-white hover:bg-slate-800'}`}
                    >
                      Administração
                    </button>
                 )}
                 {user.status === UserStatus.APPROVED && (
                    <button 
                      onClick={() => onNavigate('DASHBOARD')}
                      className={`px-4 py-2 rounded-lg text-sm font-medium transition-all ${currentView === 'DASHBOARD' ? 'bg-purple-600/10 text-purple-400' : 'text-slate-400 hover:text-white hover:bg-slate-800'}`}
                    >
                      Gerador
                    </button>
                 )}
              </div>
              
              <div className="flex items-center gap-3 pl-4 border-l border-slate-700/50">
                <div className="text-right hidden sm:block leading-tight">
                  <p className="text-sm font-bold text-white">{user.name.split(' ')[0]}</p>
                  <p className="text-[10px] text-slate-400 uppercase tracking-wider">{user.status === 'ADMIN' ? 'Admin' : 'PRO'}</p>
                </div>
                <button 
                  onClick={onLogout}
                  className="p-2 hover:bg-slate-800 rounded-full transition-colors text-slate-400 hover:text-red-400"
                  title="Sair"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                  </svg>
                </button>
              </div>
            </div>
          ) : (
            <div className="flex items-center gap-3 sm:gap-6">
              {currentView !== 'LOGIN' && (
                <button 
                  onClick={() => onNavigate('LOGIN')}
                  className="text-sm font-semibold text-slate-300 hover:text-white transition-colors"
                >
                  Entrar
                </button>
              )}
              {currentView !== 'REGISTER' && (
                 <button 
                    onClick={() => onNavigate('REGISTER')}
                    className="px-5 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-sm font-bold rounded-lg transition-all shadow-lg shadow-purple-500/20 hover:shadow-purple-500/30 hover:-translate-y-0.5"
                  >
                    Começar Agora
                  </button>
              )}
            </div>
          )}
        </div>
      </header>

      {/* Main Content */}
      <main className="flex-grow flex flex-col">
        <div className={`w-full flex-grow mx-auto ${currentView === 'LANDING' ? '' : 'max-w-7xl px-4 sm:px-6 lg:px-8 py-8'}`}>
          {children}
        </div>
      </main>

      {/* Footer */}
      <footer className="border-t border-slate-800/60 py-10 bg-slate-950">
        <div className="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
          <div className="text-center md:text-left">
              <div className="flex items-center justify-center md:justify-start gap-2 mb-2">
                <span className="font-bold text-lg text-slate-200">PromptMaster.AI</span>
              </div>
              <p className="text-slate-500 text-sm">&copy; {new Date().getFullYear()} Todos os direitos reservados.</p>
          </div>
          <div className="flex gap-6 text-sm text-slate-400">
            <a href="#" className="hover:text-purple-400 transition-colors">Termos de Uso</a>
            <a href="#" className="hover:text-purple-400 transition-colors">Política de Privacidade</a>
            <a href="#" className="hover:text-purple-400 transition-colors">Suporte</a>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Layout;