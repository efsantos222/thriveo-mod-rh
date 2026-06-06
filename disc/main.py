#!/usr/bin/env python3
"""
DISC Assessment System - Main Entry Point

Sistema completo de avaliação de personalidade DISC
Desenvolvido para identificar perfis comportamentais em 4 dimensões:
- D (Dominância): Executor
- I (Influência): Comunicador
- S (Estabilidade): Apoiador
- C (Conformidade): Analista

Usage:
    python main.py              # Run interactive test
    python main.py --demo       # Run demo mode
    python main.py --help       # Show help
"""

import sys
import argparse
from disc_test import InteractiveDISCTest, DISCTest


def print_banner():
    """Print application banner"""
    print("""
    ╔════════════════════════════════════════════════════════════════════╗
    ║                                                                    ║
    ║         ██████╗ ██╗███████╗ ██████╗                               ║
    ║         ██╔══██╗██║██╔════╝██╔════╝                               ║
    ║         ██║  ██║██║███████╗██║                                    ║
    ║         ██║  ██║██║╚════██║██║                                    ║
    ║         ██████╔╝██║███████║╚██████╗                               ║
    ║         ╚═════╝ ╚═╝╚══════╝ ╚═════╝                               ║
    ║                                                                    ║
    ║              SISTEMA DE AVALIAÇÃO DE PERSONALIDADE                ║
    ║                                                                    ║
    ╚════════════════════════════════════════════════════════════════════╝

    Identifique seu perfil comportamental em 4 dimensões:
    🔴 D - Dominância (Executor)      🟡 I - Influência (Comunicador)
    🟢 S - Estabilidade (Apoiador)    🔵 C - Conformidade (Analista)
    """)


def run_interactive():
    """Run interactive test mode"""
    interactive_test = InteractiveDISCTest()
    interactive_test.run()


def run_demo():
    """Run demo mode with sample data"""
    print_banner()
    print("\n" + "="*70)
    print(" "*25 + "MODO DEMONSTRAÇÃO")
    print("="*70)
    print("\nExecutando teste com perfil balanceado...")

    test = DISCTest()
    # Create balanced profile
    demo_answers = ['D', 'I', 'S', 'C', 'D'] * 4
    results = test.quick_test(demo_answers)

    results.display_results()
    print("\n" + "="*70)
    print("Demonstração concluída!")
    print("="*70)


def main():
    """Main entry point"""
    parser = argparse.ArgumentParser(
        description='Sistema de Avaliação DISC',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Exemplos de uso:
    python main.py              # Executar teste interativo
    python main.py --demo       # Executar demonstração
    python main.py --version    # Mostrar versão

Para mais informações sobre o teste DISC, visite:
https://en.wikipedia.org/wiki/DISC_assessment
        """
    )

    parser.add_argument(
        '--demo',
        action='store_true',
        help='Execute modo demonstração com perfil de exemplo'
    )

    parser.add_argument(
        '--version',
        action='version',
        version='DISC Assessment System v1.0.0'
    )

    args = parser.parse_args()

    try:
        if args.demo:
            run_demo()
        else:
            print_banner()
            input("Pressione ENTER para começar...")
            run_interactive()

    except KeyboardInterrupt:
        print("\n\n👋 Programa encerrado pelo usuário. Até logo!")
        sys.exit(0)
    except Exception as e:
        print(f"\n❌ Erro inesperado: {e}")
        print("Por favor, reporte este erro aos desenvolvedores.")
        sys.exit(1)


if __name__ == "__main__":
    main()
