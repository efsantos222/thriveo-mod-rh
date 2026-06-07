import { User, UserStatus, SavedPrompt } from '../types';

// Chaves de armazenamento local
const USERS_KEY = 'promptmaster_users_db';
const PROMPTS_KEY = 'promptmaster_prompts_db';
const CURRENT_USER_KEY = 'promptmaster_current_user';

// Utilitário para simular delay de rede (sensação de app real/banco de dados)
const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

// --- SESSION MANAGEMENT ---
export const getCurrentUser = (): User | null => {
  const stored = localStorage.getItem(CURRENT_USER_KEY);
  return stored ? JSON.parse(stored) : null;
};

export const setCurrentUser = (user: User | null): void => {
  if (user) {
    localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(user));
  } else {
    localStorage.removeItem(CURRENT_USER_KEY);
  }
};

// --- USERS (MOCK DB) ---

const getLocalUsers = (): User[] => {
  const stored = localStorage.getItem(USERS_KEY);
  return stored ? JSON.parse(stored) : [];
};

const saveLocalUsers = (users: User[]) => {
  localStorage.setItem(USERS_KEY, JSON.stringify(users));
};

export const getUsers = async (): Promise<User[]> => {
  await delay(500); // Simula tempo de busca no banco
  let users = getLocalUsers();

  // Garante que o Admin existe (Seed)
  const adminExists = users.find(u => u.email === 'admin@proftest.com.br');
  if (!adminExists) {
    const admin: User = {
      id: 'admin-1',
      email: 'admin@proftest.com.br',
      name: 'Administrador Sistema',
      password: 'admin',
      status: UserStatus.ADMIN,
      createdAt: new Date().toISOString()
    };
    users.push(admin);
    saveLocalUsers(users);
  }

  return users;
};

export const saveUser = async (user: User): Promise<void> => {
  await delay(800); // Simula escrita no banco
  const users = getLocalUsers();
  // Update or Create
  const index = users.findIndex(u => u.id === user.id);
  if (index >= 0) {
    users[index] = user;
  } else {
    users.push(user);
  }
  saveLocalUsers(users);
};

export const updateUserStatus = async (userId: string, status: UserStatus): Promise<void> => {
  await delay(400);
  const users = getLocalUsers();
  const user = users.find(u => u.id === userId);
  if (user) {
    user.status = status;
    saveLocalUsers(users);
  }
};

// --- PROMPTS (MOCK DB) ---

const getLocalPrompts = (): SavedPrompt[] => {
  const stored = localStorage.getItem(PROMPTS_KEY);
  return stored ? JSON.parse(stored) : [];
};

const saveLocalPrompts = (prompts: SavedPrompt[]) => {
  localStorage.setItem(PROMPTS_KEY, JSON.stringify(prompts));
};

export const getPrompts = async (userId: string): Promise<SavedPrompt[]> => {
  await delay(600);
  const allPrompts = getLocalPrompts();
  // Filtra prompts apenas do usuário atual
  const userPrompts = allPrompts.filter(p => p.userId === userId);
  
  return userPrompts.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
};

export const savePrompt = async (prompt: SavedPrompt): Promise<void> => {
  await delay(600);
  const prompts = getLocalPrompts();
  prompts.push(prompt);
  saveLocalPrompts(prompts);
};

export const deletePrompt = async (promptId: string): Promise<void> => {
  await delay(400);
  let prompts = getLocalPrompts();
  prompts = prompts.filter(p => p.id !== promptId);
  saveLocalPrompts(prompts);
};