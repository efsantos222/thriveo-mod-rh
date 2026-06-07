import { useState, useCallback } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import { useLocation } from 'wouter';
import * as XLSX from 'xlsx';
import { FileUpload } from '@/components/ui/file-upload';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { useToast } from '@/hooks/use-toast';
import { apiRequest, queryClient } from '@/lib/queryClient';
import { 
  processExcelData, 
  processAllResults, 
  getTypeDistribution, 
  getMostCommonType,
  type MbtiResult 
} from '@/lib/mbti-calculations';
import { Brain, Users, FileDown, FileSpreadsheet, BarChart3, CheckCircle, Loader2, Eye, UserCheck } from 'lucide-react';

export default function MbtiAnalysis() {
  const [results, setResults] = useState<MbtiResult[]>([]);
  const [isProcessing, setIsProcessing] = useState(false);
  const [, setLocation] = useLocation();
  const { toast } = useToast();

  const processMutation = useMutation({
    mutationFn: async (data: MbtiResult[]) => {
      const response = await apiRequest('POST', '/api/mbti/bulk-create', data);
      return response.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['/api/mbti'] });
      toast({
        title: "✅ Sucesso!",
        description: "Resultados salvos com sucesso no sistema.",
      });
    },
    onError: (error) => {
      toast({
        title: "Erro ao salvar",
        description: error.message,
        variant: "destructive",
      });
    }
  });

  const handleFileSelect = useCallback(async (file: File) => {
    setIsProcessing(true);
    
    try {
      const arrayBuffer = await file.arrayBuffer();
      const data = new Uint8Array(arrayBuffer);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[firstSheetName];
      const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 }) as any[][];
      
      const responses = processExcelData(jsonData);
      const processedResults = processAllResults(responses);
      
      setResults(processedResults);
      
      // Save to backend
      const backendData = processedResults.map(result => ({
        participantId: result.id,
        participantName: result.nome,
        eScore: result.E,
        sScore: result.S,
        tScore: result.T,
        jScore: result.J,
        zE: result.zE,
        zS: result.zS,
        zT: result.zT,
        zJ: result.zJ,
        mbtiType: result.tipo,
        intensityEI: `${result.intensidades.E} ${result.zE > 0 ? 'E' : 'I'}`,
        intensitySN: `${result.intensidades.S} ${result.zS > 0 ? 'S' : 'N'}`,
        intensityTF: `${result.intensidades.T} ${result.zT > 0 ? 'T' : 'F'}`,
        intensityJP: `${result.intensidades.J} ${result.zJ > 0 ? 'J' : 'P'}`
      }));
      
      await processMutation.mutateAsync(backendData);
      
      toast({
        title: "✅ Arquivo processado!",
        description: `${processedResults.length} participantes analisados com sucesso.`,
      });
      
    } catch (error) {
      console.error('Error processing file:', error);
      toast({
        title: "Erro ao processar arquivo",
        description: error instanceof Error ? error.message : "Erro desconhecido",
        variant: "destructive",
      });
    } finally {
      setIsProcessing(false);
    }
  }, [toast, processMutation]);

  const exportToExcel = useCallback(() => {
    if (results.length === 0) return;
    
    const exportData = results.map(result => ({
      'ID': result.id,
      'Nome': result.nome,
      'E/I_Pontuacao': result.E,
      'S/N_Pontuacao': result.S,
      'T/F_Pontuacao': result.T,
      'J/P_Pontuacao': result.J,
      'Z_Score_E': result.zE.toFixed(3),
      'Z_Score_S': result.zS.toFixed(3),
      'Z_Score_T': result.zT.toFixed(3),
      'Z_Score_J': result.zJ.toFixed(3),
      'Tipo_MBTI': result.tipo,
      'Intensidade_E/I': `${result.intensidades.E} ${result.zE > 0 ? 'E' : 'I'}`,
      'Intensidade_S/N': `${result.intensidades.S} ${result.zS > 0 ? 'S' : 'N'}`,
      'Intensidade_T/F': `${result.intensidades.T} ${result.zT > 0 ? 'T' : 'F'}`,
      'Intensidade_J/P': `${result.intensidades.J} ${result.zJ > 0 ? 'J' : 'P'}`
    }));

    const ws = XLSX.utils.json_to_sheet(exportData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Resultados_MBTI");
    XLSX.writeFile(wb, "Analise_MBTI_ZScore.xlsx");
    
    toast({
      title: "Excel exportado!",
      description: "Arquivo salvo como Analise_MBTI_ZScore.xlsx",
    });
  }, [results, toast]);

  const exportToCSV = useCallback(() => {
    if (results.length === 0) return;
    
    const headers = ['ID', 'Nome', 'E/I', 'S/N', 'T/F', 'J/P', 'Z_E', 'Z_S', 'Z_T', 'Z_J', 'Tipo_MBTI', 'Int_E/I', 'Int_S/N', 'Int_T/F', 'Int_J/P'];
    const csvContent = [
      headers.join(','),
      ...results.map(result => [
        result.id,
        `"${result.nome}"`,
        result.E,
        result.S,
        result.T,
        result.J,
        result.zE.toFixed(3),
        result.zS.toFixed(3),
        result.zT.toFixed(3),
        result.zJ.toFixed(3),
        result.tipo,
        `"${result.intensidades.E} ${result.zE > 0 ? 'E' : 'I'}"`,
        `"${result.intensidades.S} ${result.zS > 0 ? 'S' : 'N'}"`,
        `"${result.intensidades.T} ${result.zT > 0 ? 'T' : 'F'}"`,
        `"${result.intensidades.J} ${result.zJ > 0 ? 'J' : 'P'}"`
      ].join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'Analise_MBTI_ZScore.csv';
    link.click();
    
    toast({
      title: "CSV exportado!",
      description: "Arquivo salvo como Analise_MBTI_ZScore.csv",
    });
  }, [results, toast]);

  const statistics = results.length > 0 ? {
    totalParticipants: results.length,
    uniqueTypes: Array.from(new Set(results.map(r => r.tipo))).length,
    diversity: (((Array.from(new Set(results.map(r => r.tipo))).length / results.length) * 100).toFixed(1)),
    mostCommon: getMostCommonType(results)
  } : null;

  const typeDistribution = results.length > 0 ? getTypeDistribution(results) : {};

  const getZScoreClass = (score: number) => {
    if (score > 0.5) return 'z-positive';
    if (score < -0.5) return 'z-negative';
    return 'z-neutral';
  };

  const getIntensityClass = (intensity: string) => {
    return `intensity-${intensity.toLowerCase().replace(' ', '-')}`;
  };

  return (
    <div className="min-h-screen gradient-bg p-5">
      <div className="max-w-7xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden animate-fade-in">
        
        {/* Header */}
        <div className="gradient-header text-white p-8 text-center">
          <h1 className="text-4xl font-bold mb-3 flex items-center justify-center gap-3">
            <Brain className="h-12 w-12" />
            Sistema MBTI Z-Score
          </h1>
          <p className="text-xl opacity-90">Análise Completa de Personalidade com Importação de Excel</p>
        </div>

        <div className="p-8">
          {/* File Upload Section */}
          <FileUpload 
            onFileSelect={handleFileSelect}
            className="mb-8"
          />

          {/* Loading State */}
          {isProcessing && (
            <div className="text-center py-12">
              <Loader2 className="h-16 w-16 animate-spin text-primary mx-auto mb-4" />
              <p className="text-xl text-gray-600">Processando dados e calculando Z-Scores...</p>
              <div className="w-full bg-gray-200 rounded-full h-2 mt-4 max-w-md mx-auto">
                <div className="bg-primary h-2 rounded-full animate-pulse" style={{ width: '65%' }}></div>
              </div>
            </div>
          )}

          {/* Results Section */}
          {results.length > 0 && !isProcessing && (
            <div className="animate-slide-up space-y-8">
              
              {/* Statistics Grid */}
              {statistics && (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                  <Card className="gradient-bg text-white">
                    <CardContent className="p-6 text-center">
                      <div className="text-3xl font-bold mb-2">{statistics.totalParticipants}</div>
                      <div className="text-white/90">Total de Participantes</div>
                      <Users className="h-8 w-8 opacity-50 mt-2 mx-auto" />
                    </CardContent>
                  </Card>
                  
                  <Card className="gradient-bg text-white">
                    <CardContent className="p-6 text-center">
                      <div className="text-3xl font-bold mb-2">{statistics.uniqueTypes}</div>
                      <div className="text-white/90">Tipos Únicos</div>
                      <BarChart3 className="h-8 w-8 opacity-50 mt-2 mx-auto" />
                    </CardContent>
                  </Card>
                  
                  <Card className="gradient-bg text-white">
                    <CardContent className="p-6 text-center">
                      <div className="text-3xl font-bold mb-2">{statistics.diversity}%</div>
                      <div className="text-white/90">Diversidade</div>
                      <CheckCircle className="h-8 w-8 opacity-50 mt-2 mx-auto" />
                    </CardContent>
                  </Card>
                  
                  <Card className="gradient-bg text-white">
                    <CardContent className="p-6 text-center">
                      <div className="text-3xl font-bold mb-2">{statistics.mostCommon.tipo}</div>
                      <div className="text-white/90">Mais Comum ({statistics.mostCommon.count})</div>
                      <Brain className="h-8 w-8 opacity-50 mt-2 mx-auto" />
                    </CardContent>
                  </Card>
                </div>
              )}

              {/* Analysis Summary */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <BarChart3 className="h-5 w-5 text-primary" />
                    Distribuição dos Tipos MBTI
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
                    {Object.entries(typeDistribution).map(([tipo, count]) => {
                      const percentage = ((count / results.length) * 100).toFixed(1);
                      return (
                        <div key={tipo} className="bg-gray-50 rounded-lg p-3 text-center hover:shadow-md transition-shadow">
                          <Badge className={`mbti-${tipo.toLowerCase()} text-white text-sm font-bold mb-2`}>
                            {tipo}
                          </Badge>
                          <div className="text-2xl font-bold text-gray-800">{count}</div>
                          <div className="text-xs text-gray-600">{percentage}%</div>
                        </div>
                      );
                    })}
                  </div>
                </CardContent>
              </Card>

              {/* Results Table */}
              <Card>
                <CardContent className="p-0">
                  <div className="overflow-x-auto">
                    <Table>
                      <TableHeader>
                        <TableRow className="bg-gray-800 hover:bg-gray-800">
                          <TableHead className="text-white text-center">ID</TableHead>
                          <TableHead className="text-white text-center">Nome</TableHead>
                          <TableHead className="text-white text-center">E/I</TableHead>
                          <TableHead className="text-white text-center">S/N</TableHead>
                          <TableHead className="text-white text-center">T/F</TableHead>
                          <TableHead className="text-white text-center">J/P</TableHead>
                          <TableHead className="text-white text-center">Z-E</TableHead>
                          <TableHead className="text-white text-center">Z-S</TableHead>
                          <TableHead className="text-white text-center">Z-T</TableHead>
                          <TableHead className="text-white text-center">Z-J</TableHead>
                          <TableHead className="text-white text-center">Tipo MBTI</TableHead>
                          <TableHead className="text-white text-center">Int. E/I</TableHead>
                          <TableHead className="text-white text-center">Int. S/N</TableHead>
                          <TableHead className="text-white text-center">Int. T/F</TableHead>
                          <TableHead className="text-white text-center">Int. J/P</TableHead>
                          <TableHead className="text-white text-center">Ações</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {results.map((result) => (
                          <TableRow key={result.id} className="hover:bg-gray-50">
                            <TableCell className="text-center font-medium">{result.id}</TableCell>
                            <TableCell className="text-center">{result.nome}</TableCell>
                            <TableCell className="text-center">{result.E}/75</TableCell>
                            <TableCell className="text-center">{result.S}/75</TableCell>
                            <TableCell className="text-center">{result.T}/75</TableCell>
                            <TableCell className="text-center">{result.J}/75</TableCell>
                            <TableCell className="text-center">
                              <Badge className={`${getZScoreClass(result.zE)} text-xs`}>
                                {result.zE.toFixed(2)}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-center">
                              <Badge className={`${getZScoreClass(result.zS)} text-xs`}>
                                {result.zS.toFixed(2)}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-center">
                              <Badge className={`${getZScoreClass(result.zT)} text-xs`}>
                                {result.zT.toFixed(2)}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-center">
                              <Badge className={`${getZScoreClass(result.zJ)} text-xs`}>
                                {result.zJ.toFixed(2)}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-center">
                              <Badge className={`mbti-${result.tipo.toLowerCase()} text-white`}>
                                {result.tipo}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-center">
                              <Badge className={`${getIntensityClass(result.intensidades.E)} text-xs`}>
                                {result.intensidades.E} {result.zE > 0 ? 'E' : 'I'}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-center">
                              <Badge className={`${getIntensityClass(result.intensidades.S)} text-xs`}>
                                {result.intensidades.S} {result.zS > 0 ? 'S' : 'N'}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-center">
                              <Badge className={`${getIntensityClass(result.intensidades.T)} text-xs`}>
                                {result.intensidades.T} {result.zT > 0 ? 'T' : 'F'}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-center">
                              <Badge className={`${getIntensityClass(result.intensidades.J)} text-xs`}>
                                {result.intensidades.J} {result.zJ > 0 ? 'J' : 'P'}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-center">
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => setLocation(`/profile?type=${result.tipo}&name=${encodeURIComponent(result.nome)}&id=${result.id}`)}
                                className="text-xs hover:bg-primary hover:text-white transition-colors"
                              >
                                <Eye className="h-3 w-3 mr-1" />
                                Ver Perfil
                              </Button>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </div>
                </CardContent>
              </Card>

              {/* Export Section */}
              <Card>
                <CardContent className="p-8 text-center bg-gray-50">
                  <h3 className="text-2xl font-bold text-gray-800 mb-6 flex items-center justify-center gap-2">
                    <FileDown className="h-6 w-6 text-primary" />
                    Exportar Resultados
                  </h3>
                  <div className="flex flex-wrap justify-center gap-4">
                    <Button 
                      onClick={exportToExcel}
                      className="gradient-export text-white hover:scale-105 transition-transform"
                    >
                      <FileSpreadsheet className="h-4 w-4 mr-2" />
                      Exportar para Excel
                    </Button>
                    <Button 
                      onClick={exportToCSV}
                      className="gradient-export text-white hover:scale-105 transition-transform"
                    >
                      <FileDown className="h-4 w-4 mr-2" />
                      Exportar para CSV
                    </Button>
                    <Button 
                      onClick={() => window.print()}
                      className="gradient-export text-white hover:scale-105 transition-transform"
                    >
                      <BarChart3 className="h-4 w-4 mr-2" />
                      Imprimir Relatório
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
