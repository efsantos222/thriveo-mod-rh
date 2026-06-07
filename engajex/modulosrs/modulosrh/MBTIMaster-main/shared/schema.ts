import { sql } from "drizzle-orm";
import { pgTable, text, varchar, integer, real, timestamp } from "drizzle-orm/pg-core";
import { createInsertSchema } from "drizzle-zod";
import { z } from "zod";

export const users = pgTable("users", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  username: text("username").notNull().unique(),
  password: text("password").notNull(),
});

export const mbtiResults = pgTable("mbti_results", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  participantId: text("participant_id").notNull(),
  participantName: text("participant_name").notNull(),
  eScore: integer("e_score").notNull(),
  sScore: integer("s_score").notNull(),
  tScore: integer("t_score").notNull(),
  jScore: integer("j_score").notNull(),
  zE: real("z_e").notNull(),
  zS: real("z_s").notNull(),
  zT: real("z_t").notNull(),
  zJ: real("z_j").notNull(),
  mbtiType: text("mbti_type").notNull(),
  intensityEI: text("intensity_ei").notNull(),
  intensitySN: text("intensity_sn").notNull(),
  intensityTF: text("intensity_tf").notNull(),
  intensityJP: text("intensity_jp").notNull(),
  createdAt: timestamp("created_at").defaultNow(),
});

export const insertUserSchema = createInsertSchema(users).pick({
  username: true,
  password: true,
});

export const insertMbtiResultSchema = createInsertSchema(mbtiResults).omit({
  id: true,
  createdAt: true,
});

export type InsertUser = z.infer<typeof insertUserSchema>;
export type User = typeof users.$inferSelect;
export type MbtiResult = typeof mbtiResults.$inferSelect;
export type InsertMbtiResult = z.infer<typeof insertMbtiResultSchema>;
