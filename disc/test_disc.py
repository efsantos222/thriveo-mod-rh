#!/usr/bin/env python3
"""
Testes Unitários para o Sistema de Avaliação DISC

Execute com: python test_disc.py
Ou com pytest: pytest test_disc.py -v
"""

import sys
import os

# Adicionar o diretório atual ao path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from disc_test import DISCTest
from disc_results import DISCResults
from disc_data import DISC_QUESTIONS, PROFILE_DESCRIPTIONS


class TestDISCData:
    """Testes para disc_data.py"""

    def test_questions_count(self):
        """Verifica se há 20 perguntas"""
        assert len(DISC_QUESTIONS) == 20, "Deve haver exatamente 20 perguntas"

    def test_questions_structure(self):
        """Verifica estrutura das perguntas"""
        for i, q in enumerate(DISC_QUESTIONS):
            assert 'question' in q, f"Pergunta {i} deve ter campo 'question'"
            assert 'options' in q, f"Pergunta {i} deve ter campo 'options'"
            assert len(q['options']) == 4, f"Pergunta {i} deve ter 4 opções"
            assert all(k in q['options'] for k in ['D', 'I', 'S', 'C']), \
                f"Pergunta {i} deve ter opções D, I, S, C"

    def test_profile_descriptions(self):
        """Verifica descrições dos perfis"""
        for trait in ['D', 'I', 'S', 'C']:
            assert trait in PROFILE_DESCRIPTIONS, f"Perfil {trait} deve existir"
            profile = PROFILE_DESCRIPTIONS[trait]
            assert 'name' in profile
            assert 'characteristics' in profile
            assert 'strengths' in profile
            assert 'weaknesses' in profile


class TestDISCTest:
    """Testes para disc_test.py"""

    def test_initialization(self):
        """Testa inicialização do teste"""
        test = DISCTest()
        assert test.scores == {'D': 0, 'I': 0, 'S': 0, 'C': 0}
        assert test.current_question == 0
        assert test.total_questions == 20

    def test_quick_test(self):
        """Testa execução rápida do teste"""
        test = DISCTest()
        answers = ['D'] * 20
        results = test.quick_test(answers)

        assert isinstance(results, DISCResults)
        assert results.scores['D'] == 20
        assert results.scores['I'] == 0
        assert results.scores['S'] == 0
        assert results.scores['C'] == 0

    def test_quick_test_invalid_count(self):
        """Testa erro com número incorreto de respostas"""
        test = DISCTest()
        try:
            test.quick_test(['D'] * 10)  # Apenas 10 respostas
            assert False, "Deveria lançar ValueError"
        except ValueError as e:
            assert "Expected 20 answers" in str(e)

    def test_quick_test_invalid_answer(self):
        """Testa erro com resposta inválida"""
        test = DISCTest()
        answers = ['D'] * 19 + ['X']  # Resposta inválida
        try:
            test.quick_test(answers)
            assert False, "Deveria lançar ValueError"
        except ValueError as e:
            assert "Invalid answer" in str(e)

    def test_reset(self):
        """Testa reset do teste"""
        test = DISCTest()
        test.scores['D'] = 10
        test.current_question = 5
        test.answers = ['D'] * 5

        test.reset()

        assert test.scores == {'D': 0, 'I': 0, 'S': 0, 'C': 0}
        assert test.current_question == 0
        assert test.answers == []


class TestDISCResults:
    """Testes para disc_results.py"""

    def test_initialization(self):
        """Testa inicialização dos resultados"""
        scores = {'D': 10, 'I': 5, 'S': 3, 'C': 2}
        results = DISCResults(scores)

        assert results.scores == scores
        assert results.total == 20
        assert results.primary_profile == 'D'

    def test_percentage_calculation(self):
        """Testa cálculo de percentagens"""
        scores = {'D': 10, 'I': 5, 'S': 3, 'C': 2}
        results = DISCResults(scores)

        assert results.percentages['D'] == 50.0
        assert results.percentages['I'] == 25.0
        assert results.percentages['S'] == 15.0
        assert results.percentages['C'] == 10.0

    def test_primary_profile(self):
        """Testa identificação do perfil primário"""
        scores = {'D': 5, 'I': 15, 'S': 0, 'C': 0}
        results = DISCResults(scores)
        assert results.primary_profile == 'I'

    def test_secondary_profile(self):
        """Testa identificação do perfil secundário"""
        scores = {'D': 10, 'I': 8, 'S': 1, 'C': 1}
        results = DISCResults(scores)
        assert results.secondary_profile == 'I'

    def test_combination_profile(self):
        """Testa detecção de perfil combinado"""
        # Perfil com D e I próximos (I >= 80% de D)
        scores = {'D': 10, 'I': 9, 'S': 1, 'C': 0}
        results = DISCResults(scores)
        assert results.is_combination_profile() == True

        # Perfil dominante claro
        scores = {'D': 15, 'I': 3, 'S': 1, 'C': 1}
        results = DISCResults(scores)
        assert results.is_combination_profile() == False

    def test_get_summary(self):
        """Testa obtenção de resumo"""
        scores = {'D': 10, 'I': 5, 'S': 3, 'C': 2}
        results = DISCResults(scores)
        summary = results.get_summary()

        assert 'scores' in summary
        assert 'percentages' in summary
        assert 'primary_profile' in summary
        assert summary['primary_profile'] == 'D'

    def test_zero_scores(self):
        """Testa com pontuações zero"""
        scores = {'D': 0, 'I': 0, 'S': 0, 'C': 0}
        results = DISCResults(scores)

        assert results.total == 0
        assert all(p == 0.0 for p in results.percentages.values())

    def test_all_profiles(self):
        """Testa identificação de cada perfil puro"""
        for trait in ['D', 'I', 'S', 'C']:
            scores = {t: 20 if t == trait else 0 for t in ['D', 'I', 'S', 'C']}
            results = DISCResults(scores)
            assert results.primary_profile == trait
            assert results.percentages[trait] == 100.0


class TestIntegration:
    """Testes de integração"""

    def test_full_workflow(self):
        """Testa fluxo completo do teste"""
        # Criar teste
        test = DISCTest()

        # Simular respostas
        answers = ['D'] * 10 + ['I'] * 5 + ['S'] * 3 + ['C'] * 2

        # Executar teste
        results = test.quick_test(answers)

        # Verificar resultados
        assert results.primary_profile == 'D'
        assert results.total == 20
        assert results.scores['D'] == 10

        # Obter resumo
        summary = results.get_summary()
        assert summary['primary_profile'] == 'D'

    def test_balanced_profile(self):
        """Testa perfil perfeitamente balanceado"""
        test = DISCTest()
        answers = ['D', 'I', 'S', 'C'] * 5  # 5 de cada
        results = test.quick_test(answers)

        # Todas as pontuações devem ser iguais
        assert results.scores['D'] == 5
        assert results.scores['I'] == 5
        assert results.scores['S'] == 5
        assert results.scores['C'] == 5

        # Percentagens devem ser 25% cada
        for percentage in results.percentages.values():
            assert percentage == 25.0


def run_all_tests():
    """Executa todos os testes manualmente"""
    print("="*70)
    print(" "*20 + "EXECUTANDO TESTES UNITÁRIOS")
    print("="*70)

    test_classes = [
        ("TestDISCData", TestDISCData()),
        ("TestDISCTest", TestDISCTest()),
        ("TestDISCResults", TestDISCResults()),
        ("TestIntegration", TestIntegration()),
    ]

    total_tests = 0
    passed_tests = 0
    failed_tests = 0

    for class_name, test_instance in test_classes:
        print(f"\n{class_name}:")
        print("-" * 70)

        # Obter todos os métodos de teste
        test_methods = [m for m in dir(test_instance) if m.startswith('test_')]

        for method_name in test_methods:
            total_tests += 1
            try:
                method = getattr(test_instance, method_name)
                method()
                print(f"  ✓ {method_name}")
                passed_tests += 1
            except AssertionError as e:
                print(f"  ✗ {method_name}: {e}")
                failed_tests += 1
            except Exception as e:
                print(f"  ✗ {method_name}: Erro inesperado - {e}")
                failed_tests += 1

    print("\n" + "="*70)
    print(f"RESULTADOS: {passed_tests}/{total_tests} testes passaram")
    if failed_tests > 0:
        print(f"⚠️  {failed_tests} teste(s) falharam")
    else:
        print("✅ Todos os testes passaram!")
    print("="*70)

    return failed_tests == 0


if __name__ == "__main__":
    success = run_all_tests()
    sys.exit(0 if success else 1)
