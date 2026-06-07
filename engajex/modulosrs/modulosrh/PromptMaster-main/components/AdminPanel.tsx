import React, { useState, useEffect } from 'react';
import { getUsers, updateUserStatus } from '../services/storageService';
import { User, UserStatus } from '../types';

const AdminPanel: React.FC = () => {
  const [users, setUsers] = useState<User[]>([]);
  const [filter, setFilter] = useState<'ALL' | 'PENDING'>('ALL');
  const [loading, setLoading] = useState(true);

  const loadUsers = async () => {
    setLoading(true);
    try {
      const allUsers = await getUsers();
      // Sort pending first, then by date
      const sorted = allUsers.sort((a, b) => {
          if (a.status === UserStatus.PENDING && b.status !== UserStatus.PENDING) return -1;
          if (a.status !== UserStatus.PENDING && b.status === UserStatus.PENDING) return 1;
          return new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime();
      });
      setUsers(sorted);
    } catch (err) {
      console.error("Erro ao carregar usuários", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadUsers();
  }, []);

  const handleStatusChange = async (userId: string, newStatus: UserStatus) => {
    // Otimistic Update ou Loading local seria ideal, aqui usamos refresh simples
    await updateUserStatus(userId, newStatus);
    loadUsers();
  };

  const filteredUsers = filter === 'ALL' ? users : users.filter(u => u.status === UserStatus.PENDING);

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-white">Administração de Usuários</h2>
        <div className="bg-slate-800 p-1 rounded-lg flex">
          <button
            onClick={() => setFilter('ALL')}
            className={`px-4 py-1.5 rounded-md text-sm font-medium transition-colors ${filter === 'ALL' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white'}`}
          >
            Todos
          </button>
          <button
            onClick={() => setFilter('PENDING')}
            className={`px-4 py-1.5 rounded-md text-sm font-medium transition-colors ${filter === 'PENDING' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white'}`}
          >
            Pendentes
          </button>
        </div>
      </div>

      <div className="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden shadow-xl">
        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead className="bg-slate-900/50 border-b border-slate-700">
              <tr>
                <th className="p-4 text-sm font-semibold text-slate-400">Usuário</th>
                <th className="p-4 text-sm font-semibold text-slate-400">Email</th>
                <th className="p-4 text-sm font-semibold text-slate-400">Data Cadastro</th>
                <th className="p-4 text-sm font-semibold text-slate-400">Status</th>
                <th className="p-4 text-sm font-semibold text-slate-400 text-right">Ações</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-700">
              {loading ? (
                 <tr>
                    <td colSpan={5} className="p-8 text-center text-slate-500">Carregando...</td>
                 </tr>
              ) : filteredUsers.map((user) => (
                <tr key={user.id} className="hover:bg-slate-700/30 transition-colors">
                  <td className="p-4 text-white font-medium">{user.name}</td>
                  <td className="p-4 text-slate-300">{user.email}</td>
                  <td className="p-4 text-slate-400 text-sm">{new Date(user.createdAt).toLocaleDateString()}</td>
                  <td className="p-4">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                      ${user.status === UserStatus.APPROVED ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 
                        user.status === UserStatus.PENDING ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' :
                        user.status === UserStatus.ADMIN ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' :
                        'bg-red-500/10 text-red-400 border border-red-500/20'}`}>
                      {user.status === UserStatus.PENDING ? 'Pendente Pgto' : 
                       user.status === UserStatus.APPROVED ? 'Aprovado' : 
                       user.status === UserStatus.ADMIN ? 'Admin' : 'Rejeitado'}
                    </span>
                  </td>
                  <td className="p-4 text-right space-x-2">
                    {user.status !== UserStatus.ADMIN && (
                      <>
                        {user.status !== UserStatus.APPROVED && (
                          <button
                            onClick={() => handleStatusChange(user.id, UserStatus.APPROVED)}
                            className="text-xs bg-green-600 hover:bg-green-500 text-white px-3 py-1.5 rounded shadow transition-colors"
                          >
                            Aprovar
                          </button>
                        )}
                         {user.status === UserStatus.APPROVED && (
                          <button
                            onClick={() => handleStatusChange(user.id, UserStatus.REJECTED)}
                            className="text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 px-3 py-1.5 rounded shadow transition-colors"
                          >
                            Bloquear
                          </button>
                        )}
                         {user.status === UserStatus.PENDING && (
                          <button
                            onClick={() => handleStatusChange(user.id, UserStatus.REJECTED)}
                            className="text-xs bg-red-600 hover:bg-red-500 text-white px-3 py-1.5 rounded shadow transition-colors"
                          >
                            Rejeitar
                          </button>
                        )}
                      </>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          {!loading && filteredUsers.length === 0 && (
            <div className="p-8 text-center text-slate-500">
              Nenhum usuário encontrado.
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default AdminPanel;