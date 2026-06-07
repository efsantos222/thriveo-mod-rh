import { useCallback, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import { Upload, FileSpreadsheet, AlertCircle } from 'lucide-react';
import { cn } from '@/lib/utils';

interface FileUploadProps {
  onFileSelect: (file: File) => void;
  className?: string;
  accept?: Record<string, string[]>;
  maxSize?: number;
}

export function FileUpload({ 
  onFileSelect, 
  className, 
  accept = {
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx'],
    'application/vnd.ms-excel': ['.xls']
  },
  maxSize = 10 * 1024 * 1024 // 10MB
}: FileUploadProps) {
  const [error, setError] = useState<string>('');

  const onDrop = useCallback((acceptedFiles: File[], rejectedFiles: any[]) => {
    setError('');
    
    if (rejectedFiles.length > 0) {
      const rejection = rejectedFiles[0];
      if (rejection.errors[0]?.code === 'file-too-large') {
        setError('Arquivo muito grande. Máximo permitido: 10MB');
      } else if (rejection.errors[0]?.code === 'file-invalid-type') {
        setError('Tipo de arquivo inválido. Apenas arquivos Excel (.xlsx, .xls) são permitidos');
      } else {
        setError('Erro ao processar arquivo');
      }
      return;
    }

    if (acceptedFiles.length > 0) {
      onFileSelect(acceptedFiles[0]);
    }
  }, [onFileSelect]);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept,
    maxSize,
    multiple: false
  });

  return (
    <div className={cn("w-full", className)}>
      <div
        {...getRootProps()}
        className={cn(
          "border-2 border-dashed rounded-xl p-12 text-center transition-all duration-300 cursor-pointer group",
          isDragActive 
            ? "border-primary bg-primary/5 scale-105" 
            : "border-gray-300 bg-gray-50 hover:border-primary hover:bg-primary/5"
        )}
      >
        <input {...getInputProps()} />
        
        <div className="space-y-4">
          <div className="flex justify-center">
            {isDragActive ? (
              <Upload className="h-16 w-16 text-primary animate-bounce" />
            ) : (
              <FileSpreadsheet className="h-16 w-16 text-gray-400 group-hover:text-primary transition-colors duration-300" />
            )}
          </div>
          
          <div>
            <h3 className="text-2xl font-semibold text-gray-700 mb-2">
              📁 Importar Planilha Excel
            </h3>
            <p className="text-gray-600 text-lg">
              {isDragActive 
                ? "Solte o arquivo aqui..." 
                : "Arraste e solte seu arquivo Excel aqui ou clique para selecionar"
              }
            </p>
          </div>
          
          <button 
            type="button"
            className="gradient-bg text-white px-8 py-4 rounded-full text-lg font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg inline-flex items-center gap-2"
          >
            <FileSpreadsheet className="h-5 w-5" />
            Selecionar Arquivo Excel
          </button>
          
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6 text-left max-w-2xl mx-auto">
            <h4 className="font-semibold text-blue-800 mb-2 flex items-center gap-2">
              <AlertCircle className="h-4 w-4" />
              Formato esperado:
            </h4>
            <div className="text-sm text-blue-700 space-y-1">
              <p><strong>Coluna A:</strong> ID | <strong>Coluna B:</strong> Nome | <strong>Colunas C-BR:</strong> 60 respostas (1-5)</p>
              <div className="grid grid-cols-2 gap-2 mt-2">
                <p>• <strong>E/I:</strong> Colunas C-Q (15 perguntas)</p>
                <p>• <strong>S/N:</strong> Colunas R-AF (15 perguntas)</p>
                <p>• <strong>T/F:</strong> Colunas AG-AU (15 perguntas)</p>
                <p>• <strong>J/P:</strong> Colunas AV-BJ (15 perguntas)</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {error && (
        <div className="mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center gap-2">
          <AlertCircle className="h-4 w-4" />
          <span>{error}</span>
        </div>
      )}
    </div>
  );
}
