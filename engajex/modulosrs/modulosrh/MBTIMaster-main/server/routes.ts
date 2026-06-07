import type { Express } from "express";
import { createServer, type Server } from "http";
import { storage } from "./storage";
import { insertMbtiResultSchema } from "@shared/schema";
import { z } from "zod";

export async function registerRoutes(app: Express): Promise<Server> {
  
  // Get all MBTI results
  app.get("/api/mbti", async (req, res) => {
    try {
      const results = await storage.getAllMbtiResults();
      res.json(results);
    } catch (error) {
      console.error("Error fetching MBTI results:", error);
      res.status(500).json({ message: "Erro interno do servidor" });
    }
  });

  // Get single MBTI result
  app.get("/api/mbti/:id", async (req, res) => {
    try {
      const result = await storage.getMbtiResult(req.params.id);
      if (!result) {
        return res.status(404).json({ message: "Resultado não encontrado" });
      }
      res.json(result);
    } catch (error) {
      console.error("Error fetching MBTI result:", error);
      res.status(500).json({ message: "Erro interno do servidor" });
    }
  });

  // Create single MBTI result
  app.post("/api/mbti", async (req, res) => {
    try {
      const validatedData = insertMbtiResultSchema.parse(req.body);
      const result = await storage.createMbtiResult(validatedData);
      res.status(201).json(result);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ 
          message: "Dados inválidos", 
          errors: error.errors 
        });
      }
      console.error("Error creating MBTI result:", error);
      res.status(500).json({ message: "Erro interno do servidor" });
    }
  });

  // Bulk create MBTI results
  app.post("/api/mbti/bulk-create", async (req, res) => {
    try {
      if (!Array.isArray(req.body)) {
        return res.status(400).json({ message: "Esperado um array de resultados" });
      }

      // Validate each result
      const validatedResults = req.body.map((item, index) => {
        try {
          return insertMbtiResultSchema.parse(item);
        } catch (error) {
          throw new Error(`Item ${index + 1}: ${error instanceof z.ZodError ? error.errors.map(e => e.message).join(', ') : 'Dados inválidos'}`);
        }
      });

      const results = await storage.createMbtiResults(validatedResults);
      res.status(201).json({
        message: `${results.length} resultados criados com sucesso`,
        results
      });
    } catch (error) {
      console.error("Error bulk creating MBTI results:", error);
      res.status(400).json({ 
        message: error instanceof Error ? error.message : "Erro ao processar dados" 
      });
    }
  });

  // Delete single MBTI result
  app.delete("/api/mbti/:id", async (req, res) => {
    try {
      const deleted = await storage.deleteMbtiResult(req.params.id);
      if (!deleted) {
        return res.status(404).json({ message: "Resultado não encontrado" });
      }
      res.json({ message: "Resultado deletado com sucesso" });
    } catch (error) {
      console.error("Error deleting MBTI result:", error);
      res.status(500).json({ message: "Erro interno do servidor" });
    }
  });

  // Delete all MBTI results
  app.delete("/api/mbti", async (req, res) => {
    try {
      await storage.deleteAllMbtiResults();
      res.json({ message: "Todos os resultados foram deletados" });
    } catch (error) {
      console.error("Error deleting all MBTI results:", error);
      res.status(500).json({ message: "Erro interno do servidor" });
    }
  });

  // Get MBTI statistics
  app.get("/api/mbti/stats", async (req, res) => {
    try {
      const results = await storage.getAllMbtiResults();
      
      const stats = {
        totalParticipants: results.length,
        typeDistribution: results.reduce((acc, result) => {
          acc[result.mbtiType] = (acc[result.mbtiType] || 0) + 1;
          return acc;
        }, {} as Record<string, number>),
        averageZScores: {
          zE: results.reduce((sum, r) => sum + r.zE, 0) / results.length || 0,
          zS: results.reduce((sum, r) => sum + r.zS, 0) / results.length || 0,
          zT: results.reduce((sum, r) => sum + r.zT, 0) / results.length || 0,
          zJ: results.reduce((sum, r) => sum + r.zJ, 0) / results.length || 0,
        },
        scoreRanges: {
          eRange: { 
            min: Math.min(...results.map(r => r.eScore)), 
            max: Math.max(...results.map(r => r.eScore)) 
          },
          sRange: { 
            min: Math.min(...results.map(r => r.sScore)), 
            max: Math.max(...results.map(r => r.sScore)) 
          },
          tRange: { 
            min: Math.min(...results.map(r => r.tScore)), 
            max: Math.max(...results.map(r => r.tScore)) 
          },
          jRange: { 
            min: Math.min(...results.map(r => r.jScore)), 
            max: Math.max(...results.map(r => r.jScore)) 
          }
        }
      };

      res.json(stats);
    } catch (error) {
      console.error("Error fetching MBTI statistics:", error);
      res.status(500).json({ message: "Erro interno do servidor" });
    }
  });

  const httpServer = createServer(app);
  return httpServer;
}
