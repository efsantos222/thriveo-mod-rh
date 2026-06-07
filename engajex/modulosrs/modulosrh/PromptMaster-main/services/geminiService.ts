import { GoogleGenAI } from "@google/genai";
import { PromptRequest } from "../types";

const apiKey = process.env.API_KEY || '';

// Initialize the client
// Note: In a real production app, backend proxy is preferred to hide API key, 
// but client-side is allowed for this demo structure.
const ai = new GoogleGenAI({ apiKey: apiKey });

export const generateAiPrompt = async (request: PromptRequest): Promise<string> => {
  if (!apiKey) {
    throw new Error("Chave de API não configurada (API_KEY)");
  }

  const modelId = 'gemini-2.5-flash';
  
  const systemInstruction = `
    Você é um engenheiro de prompts especialista (Prompt Engineer).
    Sua tarefa é criar prompts altamente eficazes, detalhados e bem estruturados para LLMs (como ChatGPT, Claude, Gemini).
    O usuário fornecerá um tópico, contexto, tom e formato.
    Você deve retornar APENAS o prompt otimizado, sem introduções ou explicações extras fora do bloco do prompt.
    Use técnicas como Chain-of-Thought ou Role Prompting se apropriado para o contexto.
  `;

  const userMessage = `
    Crie um prompt otimizado com base nestes parâmetros:
    - Tópico/Objetivo: ${request.topic}
    - Contexto Adicional: ${request.context}
    - Tom de Voz: ${request.tone}
    - Formato de Saída Desejado: ${request.format}
    
    O prompt gerado deve ser claro, específico e usar marcadores se necessário.
  `;

  try {
    const response = await ai.models.generateContent({
      model: modelId,
      contents: userMessage,
      config: {
        systemInstruction: systemInstruction,
        temperature: 0.7,
      }
    });

    return response.text || "Não foi possível gerar o prompt. Tente novamente.";
  } catch (error) {
    console.error("Erro ao gerar prompt:", error);
    throw error;
  }
};
