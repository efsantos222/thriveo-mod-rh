export interface MbtiResponse {
  id: string;
  nome: string;
  respostas: number[];
}

export interface MbtiScores {
  E: number;
  S: number;
  T: number;
  J: number;
}

export interface ZScores {
  zE: number;
  zS: number;
  zT: number;
  zJ: number;
}

export interface MbtiResult extends MbtiResponse, MbtiScores, ZScores {
  tipo: string;
  intensidades: {
    E: string;
    S: string;
    T: string;
    J: string;
  };
}

export interface GroupStats {
  mean: number;
  std: number;
  min: number;
  max: number;
}

export function calculateStats(values: number[]): GroupStats {
  const mean = values.reduce((a, b) => a + b, 0) / values.length;
  const variance = values.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / values.length;
  const std = Math.sqrt(variance);
  return { 
    mean, 
    std, 
    min: Math.min(...values), 
    max: Math.max(...values) 
  };
}

export function getIntensity(zScore: number): string {
  const abs = Math.abs(zScore);
  if (abs >= 2.0) return 'Muito Forte';
  if (abs >= 1.5) return 'Forte';
  if (abs >= 1.0) return 'Moderada';
  if (abs >= 0.5) return 'Leve';
  return 'Muito Leve';
}

export function calculateMbtiScores(respostas: number[]): MbtiScores {
  return {
    E: respostas.slice(0, 15).reduce((a, b) => a + b, 0),
    S: respostas.slice(15, 30).reduce((a, b) => a + b, 0),
    T: respostas.slice(30, 45).reduce((a, b) => a + b, 0),
    J: respostas.slice(45, 60).reduce((a, b) => a + b, 0)
  };
}

export function calculateZScores(
  personalScores: MbtiScores,
  groupStats: { E: GroupStats; S: GroupStats; T: GroupStats; J: GroupStats }
): ZScores {
  return {
    zE: (personalScores.E - groupStats.E.mean) / groupStats.E.std,
    zS: (personalScores.S - groupStats.S.mean) / groupStats.S.std,
    zT: (personalScores.T - groupStats.T.mean) / groupStats.T.std,
    zJ: (personalScores.J - groupStats.J.mean) / groupStats.J.std
  };
}

export function determineMbtiType(zScores: ZScores): string {
  const e_i = zScores.zE > 0 ? 'E' : 'I';
  const s_n = zScores.zS > 0 ? 'S' : 'N';
  const t_f = zScores.zT > 0 ? 'T' : 'F';
  const j_p = zScores.zJ > 0 ? 'J' : 'P';
  
  return e_i + s_n + t_f + j_p;
}

export function processExcelData(data: any[][]): MbtiResponse[] {
  // Remove header row if exists
  const dataRows = data.slice(1).filter(row => row.length > 2);
  
  if (dataRows.length === 0) {
    throw new Error('Nenhum dado encontrado na planilha');
  }

  // Validate structure
  const expectedColumns = 62; // ID + Nome + 60 respostas
  const validRows = dataRows.filter(row => row.length >= expectedColumns);
  
  if (validRows.length === 0) {
    throw new Error(`Formato inválido. Esperado ${expectedColumns} colunas (ID + Nome + 60 respostas)`);
  }

  return validRows.map(row => {
    const id = String(row[0]);
    const nome = String(row[1]) || `Pessoa ${id}`;
    const respostas = row.slice(2, 62).map(r => {
      const parsed = parseInt(String(r)) || 0;
      if (parsed < 1 || parsed > 5) {
        console.warn(`Linha ${id}: Resposta fora do range 1-5: ${parsed}`);
      }
      return Math.max(1, Math.min(5, parsed)); // Clamp to 1-5 range
    });
    
    if (respostas.length !== 60) {
      throw new Error(`Linha ${id}: Esperado 60 respostas, encontrado ${respostas.length}`);
    }

    return { id, nome, respostas };
  });
}

export function processAllResults(responses: MbtiResponse[]): MbtiResult[] {
  // Calculate individual scores
  const scoresData = responses.map(response => ({
    ...response,
    scores: calculateMbtiScores(response.respostas)
  }));

  // Calculate group statistics
  const allE = scoresData.map(d => d.scores.E);
  const allS = scoresData.map(d => d.scores.S);
  const allT = scoresData.map(d => d.scores.T);
  const allJ = scoresData.map(d => d.scores.J);

  const groupStats = {
    E: calculateStats(allE),
    S: calculateStats(allS),
    T: calculateStats(allT),
    J: calculateStats(allJ)
  };

  // Calculate Z-scores and final results
  return scoresData.map(data => {
    const zScores = calculateZScores(data.scores, groupStats);
    const tipo = determineMbtiType(zScores);

    return {
      id: data.id,
      nome: data.nome,
      respostas: data.respostas,
      E: data.scores.E,
      S: data.scores.S,
      T: data.scores.T,
      J: data.scores.J,
      zE: zScores.zE,
      zS: zScores.zS,
      zT: zScores.zT,
      zJ: zScores.zJ,
      tipo,
      intensidades: {
        E: getIntensity(zScores.zE),
        S: getIntensity(zScores.zS),
        T: getIntensity(zScores.zT),
        J: getIntensity(zScores.zJ)
      }
    };
  });
}

export function getTypeDistribution(results: MbtiResult[]): Record<string, number> {
  return results.reduce((acc, result) => {
    acc[result.tipo] = (acc[result.tipo] || 0) + 1;
    return acc;
  }, {} as Record<string, number>);
}

export function getMostCommonType(results: MbtiResult[]): { tipo: string; count: number } {
  const distribution = getTypeDistribution(results);
  const mostCommon = Object.entries(distribution).reduce((a, b) => a[1] > b[1] ? a : b);
  return { tipo: mostCommon[0], count: mostCommon[1] };
}
