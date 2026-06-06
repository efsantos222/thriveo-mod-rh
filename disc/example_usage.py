#!/usr/bin/env python3
"""
Exemplos de uso do Sistema de Avaliação DISC

Este arquivo demonstra diferentes formas de usar o sistema DISC programaticamente.
"""

import sys
import os

# Adicionar o diretório pai ao path para importar os módulos
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from disc_test import DISCTest, InteractiveDISCTest
from disc_results import DISCResults


def example_1_quick_test():
    """Exemplo 1: Teste rápido com respostas pré-definidas"""
    print("\n" + "="*70)
    print("EXEMPLO 1: Teste Rápido com Respostas Pré-definidas")
    print("="*70)

    # Criar instância do teste
    test = DISCTest()

    # Simular respostas de um perfil Dominante
    respostas = ['D'] * 15 + ['I'] * 3 + ['S'] * 1 + ['C'] * 1

    # Executar teste rápido
    resultados = test.quick_test(respostas)

    # Exibir resultados
    resultados.display_results()


def example_2_get_summary():
    """Exemplo 2: Obter resumo dos resultados"""
    print("\n" + "="*70)
    print("EXEMPLO 2: Obter Resumo dos Resultados")
    print("="*70)

    test = DISCTest()
    respostas = ['I'] * 10 + ['D'] * 5 + ['S'] * 3 + ['C'] * 2
    resultados = test.quick_test(respostas)

    # Obter resumo como dicionário
    resumo = resultados.get_summary()

    print("\nResumo dos Resultados:")
    print(f"  Perfil Primário: {resumo['primary_profile']} ({resumo['primary_name']})")
    print(f"  Perfil Secundário: {resumo['secondary_profile']}")
    print(f"  É Perfil Combinado? {resumo['is_combination']}")
    print(f"\nPercentuais:")
    for trait, percentage in resumo['percentages'].items():
        print(f"    {trait}: {percentage:.1f}%")


def example_3_save_results():
    """Exemplo 3: Salvar e exportar resultados"""
    print("\n" + "="*70)
    print("EXEMPLO 3: Salvar e Exportar Resultados")
    print("="*70)

    test = DISCTest()
    respostas = ['C'] * 12 + ['S'] * 5 + ['D'] * 2 + ['I'] * 1
    resultados = test.quick_test(respostas)

    # Salvar em TXT
    resultados.save_to_file("exemplo_resultado.txt")

    # Exportar para JSON
    resultados.export_json("exemplo_resultado.json")

    print("\n✅ Arquivos salvos:")
    print("   - exemplo_resultado.txt")
    print("   - exemplo_resultado.json")


def example_4_check_combination():
    """Exemplo 4: Verificar perfil combinado"""
    print("\n" + "="*70)
    print("EXEMPLO 4: Verificar Perfil Combinado")
    print("="*70)

    test = DISCTest()
    # Criar perfil balanceado entre D e I
    respostas = ['D'] * 9 + ['I'] * 8 + ['S'] * 2 + ['C'] * 1
    resultados = test.quick_test(respostas)

    if resultados.is_combination_profile():
        combo_key = resultados.get_profile_combination_key()
        print(f"\n✅ Perfil Combinado Detectado: {combo_key}")
        print(f"   Primário: {resultados.primary_profile} ({resultados.percentages[resultados.primary_profile]:.1f}%)")
        print(f"   Secundário: {resultados.secondary_profile} ({resultados.percentages[resultados.secondary_profile]:.1f}%)")
    else:
        print(f"\n❌ Perfil único: {resultados.primary_profile}")


def example_5_compare_profiles():
    """Exemplo 5: Comparar múltiplos perfis"""
    print("\n" + "="*70)
    print("EXEMPLO 5: Comparar Múltiplos Perfis")
    print("="*70)

    perfis = {
        "João (Executor)": ['D'] * 15 + ['I'] * 3 + ['S'] * 1 + ['C'] * 1,
        "Maria (Comunicadora)": ['I'] * 14 + ['D'] * 4 + ['S'] * 1 + ['C'] * 1,
        "Pedro (Apoiador)": ['S'] * 13 + ['C'] * 4 + ['D'] * 2 + ['I'] * 1,
        "Ana (Analista)": ['C'] * 14 + ['S'] * 4 + ['D'] * 1 + ['I'] * 1,
    }

    print("\nComparação de Perfis da Equipe:")
    print("-" * 70)

    for nome, respostas in perfis.items():
        test = DISCTest()
        resultados = test.quick_test(respostas)
        resumo = resultados.get_summary()

        print(f"\n{nome}:")
        print(f"  Perfil: {resumo['primary_profile']} ({resumo['percentages'][resumo['primary_profile']]:.1f}%)")

        # Mostrar distribuição
        distribuicao = " | ".join([
            f"{trait}: {resumo['percentages'][trait]:4.1f}%"
            for trait in ['D', 'I', 'S', 'C']
        ])
        print(f"  Distribuição: {distribuicao}")


def example_6_custom_scoring():
    """Exemplo 6: Trabalhar diretamente com pontuações"""
    print("\n" + "="*70)
    print("EXEMPLO 6: Trabalhar com Pontuações Customizadas")
    print("="*70)

    # Criar resultados diretamente com pontuações
    pontuacoes_customizadas = {
        'D': 8,
        'I': 6,
        'S': 4,
        'C': 2
    }

    resultados = DISCResults(pontuacoes_customizadas)

    print("\nPontuações Customizadas:")
    for trait, score in pontuacoes_customizadas.items():
        print(f"  {trait}: {score} pontos")

    print(f"\nPerfil identificado: {resultados.primary_profile}")
    print(f"Percentual do perfil primário: {resultados.percentages[resultados.primary_profile]:.1f}%")


def example_7_all_profiles():
    """Exemplo 7: Demonstrar todos os perfis puros"""
    print("\n" + "="*70)
    print("EXEMPLO 7: Demonstração de Todos os Perfis Puros")
    print("="*70)

    perfis_puros = {
        'D': ['D'] * 20,
        'I': ['I'] * 20,
        'S': ['S'] * 20,
        'C': ['C'] * 20
    }

    for trait, respostas in perfis_puros.items():
        print(f"\n{'='*70}")
        print(f"Perfil {trait} Puro (100%)")
        print('='*70)

        test = DISCTest()
        resultados = test.quick_test(respostas)
        resumo = resultados.get_summary()

        print(f"Perfil: {resumo['primary_name']}")
        print(f"Percentual: {resumo['percentages'][trait]:.1f}%")


def main():
    """Executar todos os exemplos"""
    print("\n" + "="*70)
    print(" "*15 + "EXEMPLOS DE USO DO SISTEMA DISC")
    print("="*70)

    exemplos = [
        ("Teste Rápido", example_1_quick_test),
        ("Obter Resumo", example_2_get_summary),
        ("Salvar Resultados", example_3_save_results),
        ("Perfil Combinado", example_4_check_combination),
        ("Comparar Perfis", example_5_compare_profiles),
        ("Pontuações Customizadas", example_6_custom_scoring),
        ("Todos os Perfis", example_7_all_profiles),
    ]

    print("\nExemplos disponíveis:")
    for i, (nome, _) in enumerate(exemplos, 1):
        print(f"  {i}. {nome}")
    print("  0. Executar todos")

    try:
        escolha = input("\nEscolha um exemplo (0-7): ").strip()

        if escolha == '0':
            for nome, func in exemplos:
                func()
                input("\nPressione ENTER para continuar...")
        elif escolha.isdigit() and 1 <= int(escolha) <= len(exemplos):
            exemplos[int(escolha) - 1][1]()
        else:
            print("\n❌ Opção inválida!")

    except KeyboardInterrupt:
        print("\n\n👋 Exemplos encerrados.")
    except Exception as e:
        print(f"\n❌ Erro: {e}")


if __name__ == "__main__":
    main()
