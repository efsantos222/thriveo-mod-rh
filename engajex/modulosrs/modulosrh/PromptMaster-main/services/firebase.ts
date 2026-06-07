import { initializeApp } from 'firebase/app';
import { getFirestore } from 'firebase/firestore';

// --- CONFIGURAÇÃO DO FIREBASE ---
// Substitua as strings abaixo pelas chaves do seu projeto Firebase
// Você encontra essas chaves em: Project Settings > General > Your apps
const firebaseConfig = {
  apiKey: process.env.FIREBASE_API_KEY || "SUA_API_KEY_AQUI",
  authDomain: process.env.FIREBASE_AUTH_DOMAIN || "seu-projeto.firebaseapp.com",
  projectId: process.env.FIREBASE_PROJECT_ID || "seu-projeto-id",
  storageBucket: process.env.FIREBASE_STORAGE_BUCKET || "seu-projeto.appspot.com",
  messagingSenderId: process.env.FIREBASE_MESSAGING_SENDER_ID || "123456789",
  appId: process.env.FIREBASE_APP_ID || "1:123456789:web:abc123def456"
};

// Inicializa o Firebase
const app = initializeApp(firebaseConfig);

// Inicializa e exporta o Firestore (Banco de Dados)
export const db = getFirestore(app);