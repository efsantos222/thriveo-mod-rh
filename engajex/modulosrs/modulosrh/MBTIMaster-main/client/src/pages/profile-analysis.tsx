import { useState } from 'react';
import { useLocation } from 'wouter';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ScrollArea } from '@/components/ui/scroll-area';
import { getMbtiProfile, type MbtiProfile } from '@/lib/mbti-profiles';
import { 
  Brain, 
  Target, 
  TrendingUp, 
  Lightbulb, 
  Users, 
  Briefcase, 
  MessageCircle, 
  Crown,
  Heart,
  Star,
  ChevronLeft,
  Download,
  Share2
} from 'lucide-react';

interface ProfileAnalysisProps {
  mbtiType?: string;
  participantName?: string;
  participantId?: string;
}

export default function ProfileAnalysis() {
  const [, setLocation] = useLocation();
  const urlParams = new URLSearchParams(window.location.search);
  const mbtiType = urlParams.get('type');
  const participantName = urlParams.get('name');
  const participantId = urlParams.get('id');

  const profile = mbtiType ? getMbtiProfile(mbtiType) : null;

  if (!profile) {
    return (
      <div className="min-h-screen gradient-bg p-5">
        <div className="max-w-4xl mx-auto bg-white rounded-2xl shadow-2xl p-8 text-center">
          <Brain className="h-16 w-16 text-gray-400 mx-auto mb-4" />
          <h1 className="text-2xl font-bold text-gray-800 mb-2">Perfil não encontrado</h1>
          <p className="text-gray-600 mb-6">Tipo MBTI inválido ou não especificado.</p>
          <Button onClick={() => setLocation('/')} className="gradient-bg text-white">
            <ChevronLeft className="h-4 w-4 mr-2" />
            Voltar para Análise
          </Button>
        </div>
      </div>
    );
  }

  const generateReport = () => {
    const reportContent = `
ANÁLISE DE PERFIL MBTI - ${profile.tipo}

PARTICIPANTE: ${participantName || 'Não informado'}
ID: ${participantId || 'Não informado'}
TIPO: ${profile.tipo} - ${profile.nome}
DATA: ${new Date().toLocaleDateString('pt-BR')}

DESCRIÇÃO GERAL:
${profile.descricao}

CARACTERÍSTICAS GERAIS:
${profile.caracteristicasGerais.map(item => `• ${item}`).join('\n')}

PONTOS FORTES:
${profile.pontoFortes.map(item => `• ${item}`).join('\n')}

OPORTUNIDADES DE MELHORIA:
${profile.oportunidadesMelhoria.map(item => `• ${item}`).join('\n')}

PONTOS A DESENVOLVER:
${profile.pontosDesenvolver.map(item => `• ${item}`).join('\n')}

AMBIENTES IDEAIS:
${profile.ambientesIdeais.map(item => `• ${item}`).join('\n')}

ESTILO DE TRABALHO:
${profile.estilosTrabalho.map(item => `• ${item}`).join('\n')}

RELACIONAMENTOS:
${profile.relacionamentos.map(item => `• ${item}`).join('\n')}

COMUNICAÇÃO:
${profile.comunicacao}

${profile.lideranca ? `LIDERANÇA:\n${profile.lideranca}` : ''}

CARREIRAS SUGERIDAS:
${profile.carreirasSugeridas.map(item => `• ${item}`).join('\n')}

---
Relatório gerado pelo Sistema MBTI Z-Score
    `;

    const blob = new Blob([reportContent], { type: 'text/plain;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `Perfil_MBTI_${profile.tipo}_${participantName || participantId || 'Analise'}.txt`;
    link.click();
  };

  const shareProfile = () => {
    if (navigator.share) {
      navigator.share({
        title: `Perfil MBTI - ${profile.tipo}`,
        text: `Análise de perfil ${profile.tipo} - ${profile.nome}: ${profile.descricao}`,
        url: window.location.href
      });
    } else {
      // Fallback para navegadores que não suportam Web Share API
      navigator.clipboard.writeText(window.location.href);
      alert('Link copiado para a área de transferência!');
    }
  };

  return (
    <div className="min-h-screen gradient-bg p-5">
      <div className="max-w-6xl mx-auto">
        
        {/* Header */}
        <div className="bg-white rounded-2xl shadow-2xl mb-6 overflow-hidden animate-fade-in">
          <div className="gradient-header text-white p-6">
            <div className="flex items-center justify-between mb-4">
              <Button 
                variant="ghost" 
                onClick={() => setLocation('/')}
                className="text-white hover:bg-white/20 transition-colors"
              >
                <ChevronLeft className="h-4 w-4 mr-2" />
                Voltar
              </Button>
              
              <div className="flex gap-2">
                <Button 
                  variant="ghost" 
                  onClick={shareProfile}
                  className="text-white hover:bg-white/20 transition-colors"
                >
                  <Share2 className="h-4 w-4 mr-2" />
                  Compartilhar
                </Button>
                <Button 
                  variant="ghost" 
                  onClick={generateReport}
                  className="text-white hover:bg-white/20 transition-colors"
                >
                  <Download className="h-4 w-4 mr-2" />
                  Baixar Relatório
                </Button>
              </div>
            </div>
            
            <div className="text-center">
              <div className="flex items-center justify-center gap-4 mb-4">
                <Brain className="h-16 w-16" />
                <div>
                  <h1 className="text-4xl font-bold mb-2">
                    {profile.tipo} - {profile.nome}
                  </h1>
                  {participantName && (
                    <p className="text-xl opacity-90">
                      Análise para: {participantName}
                      {participantId && ` (ID: ${participantId})`}
                    </p>
                  )}
                </div>
              </div>
              <p className="text-lg opacity-95 max-w-3xl mx-auto">
                {profile.descricao}
              </p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          
          {/* Características Gerais */}
          <Card className="animate-slide-up">
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-xl">
                <Star className="h-6 w-6 text-primary" />
                Características Gerais do Perfil
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {profile.caracteristicasGerais.map((caracteristica, index) => (
                  <div key={index} className="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                    <div className="w-2 h-2 bg-primary rounded-full mt-2 flex-shrink-0"></div>
                    <p className="text-gray-700">{caracteristica}</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Pontos Fortes */}
          <Card className="animate-slide-up" style={{ animationDelay: '0.1s' }}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-xl">
                <Target className="h-6 w-6 text-green-600" />
                Pontos Fortes
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {profile.pontoFortes.map((ponto, index) => (
                  <div key={index} className="flex items-start gap-3 p-3 bg-green-50 rounded-lg border-l-4 border-green-500">
                    <div className="w-2 h-2 bg-green-600 rounded-full mt-2 flex-shrink-0"></div>
                    <p className="text-green-800">{ponto}</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Oportunidades de Melhoria */}
          <Card className="animate-slide-up" style={{ animationDelay: '0.2s' }}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-xl">
                <TrendingUp className="h-6 w-6 text-blue-600" />
                Oportunidades de Melhoria
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {profile.oportunidadesMelhoria.map((oportunidade, index) => (
                  <div key={index} className="flex items-start gap-3 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                    <div className="w-2 h-2 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
                    <p className="text-blue-800">{oportunidade}</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Pontos a Desenvolver */}
          <Card className="animate-slide-up" style={{ animationDelay: '0.3s' }}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-xl">
                <Lightbulb className="h-6 w-6 text-orange-600" />
                Pontos a Desenvolver
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {profile.pontosDesenvolver.map((ponto, index) => (
                  <div key={index} className="flex items-start gap-3 p-3 bg-orange-50 rounded-lg border-l-4 border-orange-500">
                    <div className="w-2 h-2 bg-orange-600 rounded-full mt-2 flex-shrink-0"></div>
                    <p className="text-orange-800">{ponto}</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

        </div>

        {/* Seções Adicionais */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
          
          {/* Ambientes e Estilo de Trabalho */}
          <Card className="animate-slide-up" style={{ animationDelay: '0.4s' }}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-xl">
                <Briefcase className="h-6 w-6 text-purple-600" />
                Ambiente de Trabalho Ideal
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div>
                  <h4 className="font-semibold text-gray-800 mb-2">Ambientes Ideais:</h4>
                  <div className="grid grid-cols-1 gap-2">
                    {profile.ambientesIdeais.map((ambiente, index) => (
                      <Badge key={index} variant="outline" className="justify-start p-2 text-sm">
                        {ambiente}
                      </Badge>
                    ))}
                  </div>
                </div>
                
                <Separator />
                
                <div>
                  <h4 className="font-semibold text-gray-800 mb-2">Estilo de Trabalho:</h4>
                  <div className="space-y-2">
                    {profile.estilosTrabalho.map((estilo, index) => (
                      <div key={index} className="flex items-center gap-2 text-sm text-gray-600">
                        <div className="w-1.5 h-1.5 bg-purple-600 rounded-full"></div>
                        {estilo}
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Relacionamentos e Comunicação */}
          <Card className="animate-slide-up" style={{ animationDelay: '0.5s' }}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-xl">
                <Users className="h-6 w-6 text-pink-600" />
                Relacionamentos e Comunicação
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div>
                  <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                    <Heart className="h-4 w-4 text-pink-600" />
                    Relacionamentos:
                  </h4>
                  <div className="space-y-2">
                    {profile.relacionamentos.map((rel, index) => (
                      <div key={index} className="flex items-center gap-2 text-sm text-gray-600">
                        <div className="w-1.5 h-1.5 bg-pink-600 rounded-full"></div>
                        {rel}
                      </div>
                    ))}
                  </div>
                </div>
                
                <Separator />
                
                <div>
                  <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                    <MessageCircle className="h-4 w-4 text-pink-600" />
                    Estilo de Comunicação:
                  </h4>
                  <p className="text-gray-700 text-sm bg-pink-50 p-3 rounded-lg">
                    {profile.comunicacao}
                  </p>
                </div>

                {profile.lideranca && (
                  <>
                    <Separator />
                    <div>
                      <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <Crown className="h-4 w-4 text-pink-600" />
                        Estilo de Liderança:
                      </h4>
                      <p className="text-gray-700 text-sm bg-pink-50 p-3 rounded-lg">
                        {profile.lideranca}
                      </p>
                    </div>
                  </>
                )}
              </div>
            </CardContent>
          </Card>

        </div>

        {/* Carreiras Sugeridas */}
        <Card className="mt-6 animate-slide-up" style={{ animationDelay: '0.6s' }}>
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-xl">
              <Briefcase className="h-6 w-6 text-indigo-600" />
              Carreiras e Áreas Profissionais Recomendadas
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              {profile.carreirasSugeridas.map((carreira, index) => (
                <div 
                  key={index} 
                  className="bg-indigo-50 border border-indigo-200 rounded-lg p-4 text-center hover:shadow-md transition-shadow"
                >
                  <span className="text-indigo-800 font-medium">{carreira}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Resumo e Ações */}
        <Card className="mt-6 animate-slide-up" style={{ animationDelay: '0.7s' }}>
          <CardContent className="p-6">
            <div className="text-center space-y-4">
              <h3 className="text-2xl font-bold text-gray-800">
                Análise Completa do Perfil {profile.tipo}
              </h3>
              <p className="text-gray-600 max-w-4xl mx-auto">
                Esta análise oferece insights profundos sobre suas características, pontos fortes e áreas de desenvolvimento. 
                Use essas informações para maximizar seu potencial pessoal e profissional, focando no crescimento contínuo 
                e no alinhamento com ambientes que potencializem suas habilidades naturais.
              </p>
              
              <div className="flex flex-col sm:flex-row gap-4 justify-center pt-4">
                <Button onClick={generateReport} className="gradient-export text-white">
                  <Download className="h-4 w-4 mr-2" />
                  Baixar Relatório Completo
                </Button>
                <Button onClick={shareProfile} variant="outline">
                  <Share2 className="h-4 w-4 mr-2" />
                  Compartilhar Análise
                </Button>
                <Button onClick={() => setLocation('/')} variant="outline">
                  <ChevronLeft className="h-4 w-4 mr-2" />
                  Nova Análise
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

      </div>
    </div>
  );
}