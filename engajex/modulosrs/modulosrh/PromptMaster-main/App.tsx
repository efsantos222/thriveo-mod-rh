import React, { useState, useEffect } from 'react';
import Layout from './components/Layout';
import Login from './components/Login';
import Register from './components/Register';
import AdminPanel from './components/AdminPanel';
import Generator from './components/Generator';
import LandingPage from './components/LandingPage';
import { User, UserStatus, ViewState } from './types';
import { getCurrentUser, setCurrentUser } from './services/storageService';

const App: React.FC = () => {
  const [user, setUser] = useState<User | null>(null);
  const [currentView, setCurrentView] = useState<ViewState>('LANDING');
  const [initialized, setInitialized] = useState(false);

  useEffect(() => {
    const storedUser = getCurrentUser();
    if (storedUser) {
      setUser(storedUser);
      if (storedUser.status === UserStatus.ADMIN) {
        setCurrentView('ADMIN_PANEL');
      } else {
        setCurrentView('DASHBOARD'); 
      }
    } else {
      setCurrentView('LANDING');
    }
    setInitialized(true);
  }, []);

  const handleLogin = (loggedInUser: User) => {
    setUser(loggedInUser);
    setCurrentUser(loggedInUser);
    if (loggedInUser.status === UserStatus.ADMIN) {
      setCurrentView('ADMIN_PANEL');
    } else {
      setCurrentView('DASHBOARD');
    }
  };

  const handleLogout = () => {
    setUser(null);
    setCurrentUser(null);
    setCurrentView('LANDING');
  };

  // Access Control Helper
  const renderContent = () => {
    if (!initialized) return <div className="flex h-screen items-center justify-center text-slate-500">Carregando...</div>;

    if (!user) {
      if (currentView === 'REGISTER') {
        return <Register onSwitchToLogin={() => setCurrentView('LOGIN')} />;
      }
      if (currentView === 'LOGIN') {
        return <Login onLogin={handleLogin} onSwitchToRegister={() => setCurrentView('REGISTER')} />;
      }
      // Default to Landing Page if not logged in and not on auth screens
      return <LandingPage onNavigate={setCurrentView} />;
    }

    // User is logged in logic
    if (user.status === UserStatus.PENDING) {
      return (
        <div className="flex items-center justify-center min-h-[60vh]">
          <div className="bg-slate-800 border border-yellow-500/30 p-8 rounded-xl text-center max-w-md shadow-2xl">
             <div className="w-16 h-16 bg-yellow-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-8 h-8 text-yellow-500">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
             </div>
             <h2 className="text-2xl font-bold text-white mb-2">Pagamento em Análise</h2>
             <p className="text-slate-400 mb-6">
               Recebemos seu cadastro. O administrador irá validar o pagamento do Pix e liberar seu acesso em instantes.
             </p>
             <div className="bg-slate-900/50 p-3 rounded text-xs text-slate-500 mb-6">
                Status: <span className="text-yellow-500 font-bold">PENDENTE</span>
             </div>
             <button onClick={handleLogout} className="text-purple-400 hover:text-white text-sm underline">Sair e voltar depois</button>
          </div>
        </div>
      );
    }

    if (user.status === UserStatus.REJECTED) {
        return (
          <div className="flex items-center justify-center min-h-[60vh]">
            <div className="bg-slate-800 border border-red-500/30 p-8 rounded-xl text-center max-w-md shadow-2xl">
               <div className="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-8 h-8 text-red-500">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                  </svg>
               </div>
               <h2 className="text-2xl font-bold text-white mb-2">Acesso Negado</h2>
               <p className="text-slate-400 mb-6">
                 Sua conta foi suspensa ou rejeitada pelo administrador. Entre em contato com o suporte.
               </p>
               <button onClick={handleLogout} className="text-purple-400 hover:text-white text-sm underline">Sair</button>
            </div>
          </div>
        );
    }

    if (currentView === 'ADMIN_PANEL' && user.status === UserStatus.ADMIN) {
      return <AdminPanel />;
    }

    // Default view for approved user
    return <Generator user={user} />;
  };

  return (
    <Layout 
      user={user} 
      onLogout={handleLogout} 
      currentView={currentView} 
      onNavigate={(view) => setCurrentView(view)}
    >
      {renderContent()}
    </Layout>
  );
};

export default App;