export enum UserStatus {
  PENDING = 'PENDING',
  APPROVED = 'APPROVED',
  REJECTED = 'REJECTED',
  ADMIN = 'ADMIN'
}

export interface User {
  id: string;
  email: string;
  name: string;
  password?: string; // In a real app, this would be hashed. stored in localstorage for demo
  status: UserStatus;
  createdAt: string;
}

export interface SavedPrompt {
  id: string;
  title: string;
  content: string;
  tags: string[];
  createdAt: string;
  userId: string;
}

export interface PromptRequest {
  topic: string;
  context: string;
  tone: string;
  format: string;
}

export type ViewState = 'LANDING' | 'LOGIN' | 'REGISTER' | 'DASHBOARD' | 'ADMIN_PANEL';