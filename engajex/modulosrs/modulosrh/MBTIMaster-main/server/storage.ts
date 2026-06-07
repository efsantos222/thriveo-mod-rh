import { type User, type InsertUser, type MbtiResult, type InsertMbtiResult } from "@shared/schema";
import { randomUUID } from "crypto";

export interface IStorage {
  getUser(id: string): Promise<User | undefined>;
  getUserByUsername(username: string): Promise<User | undefined>;
  createUser(user: InsertUser): Promise<User>;
  
  // MBTI Results methods
  getAllMbtiResults(): Promise<MbtiResult[]>;
  getMbtiResult(id: string): Promise<MbtiResult | undefined>;
  createMbtiResult(result: InsertMbtiResult): Promise<MbtiResult>;
  createMbtiResults(results: InsertMbtiResult[]): Promise<MbtiResult[]>;
  deleteMbtiResult(id: string): Promise<boolean>;
  deleteAllMbtiResults(): Promise<boolean>;
}

export class MemStorage implements IStorage {
  private users: Map<string, User>;
  private mbtiResults: Map<string, MbtiResult>;

  constructor() {
    this.users = new Map();
    this.mbtiResults = new Map();
  }

  async getUser(id: string): Promise<User | undefined> {
    return this.users.get(id);
  }

  async getUserByUsername(username: string): Promise<User | undefined> {
    return Array.from(this.users.values()).find(
      (user) => user.username === username,
    );
  }

  async createUser(insertUser: InsertUser): Promise<User> {
    const id = randomUUID();
    const user: User = { ...insertUser, id };
    this.users.set(id, user);
    return user;
  }

  async getAllMbtiResults(): Promise<MbtiResult[]> {
    return Array.from(this.mbtiResults.values()).sort((a, b) => 
      (a.createdAt?.getTime() || 0) - (b.createdAt?.getTime() || 0)
    );
  }

  async getMbtiResult(id: string): Promise<MbtiResult | undefined> {
    return this.mbtiResults.get(id);
  }

  async createMbtiResult(insertResult: InsertMbtiResult): Promise<MbtiResult> {
    const id = randomUUID();
    const result: MbtiResult = { 
      ...insertResult, 
      id,
      createdAt: new Date()
    };
    this.mbtiResults.set(id, result);
    return result;
  }

  async createMbtiResults(insertResults: InsertMbtiResult[]): Promise<MbtiResult[]> {
    const results: MbtiResult[] = [];
    const now = new Date();
    
    for (const insertResult of insertResults) {
      const id = randomUUID();
      const result: MbtiResult = { 
        ...insertResult, 
        id,
        createdAt: now
      };
      this.mbtiResults.set(id, result);
      results.push(result);
    }
    
    return results;
  }

  async deleteMbtiResult(id: string): Promise<boolean> {
    return this.mbtiResults.delete(id);
  }

  async deleteAllMbtiResults(): Promise<boolean> {
    this.mbtiResults.clear();
    return true;
  }
}

export const storage = new MemStorage();
