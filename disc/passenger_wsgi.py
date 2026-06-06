import sys
import os

def application(environ, start_response):
    start_response('200 OK', [('Content-Type', 'text/plain; charset=utf-8')])
    
    # Informações básicas para diagnóstico
    output = []
    output.append("✅ O servidor Python está rodando!")
    output.append(f"🐍 Python Version: {sys.version}")
    output.append(f"📂 Current Directory: {os.getcwd()}")
    output.append(f"🔧 Script Path: {__file__}")
    output.append("-" * 20)
    
    # Testando importação da App
    output.append("🔄 Tentando importar web_app...")
    try:
        # Tenta adicionar o diretório atual ao path explicitamente
        sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
        import web_app
        output.append("✅ 'import web_app' funcionou!")
        
        if hasattr(web_app, 'app'):
            output.append("✅ Objeto 'app' encontrado em web_app.")
        else:
            output.append("❌ Objeto 'app' NÃO encontrado em web_app.")
            
    except Exception as e:
        import traceback
        output.append("❌ ERRO FATAL AO IMPORTAR web_app:")
        output.append(traceback.format_exc())
    
    # Juntando tudo
    response_text = "\n".join(output)
    return [response_text.encode('utf-8')]
