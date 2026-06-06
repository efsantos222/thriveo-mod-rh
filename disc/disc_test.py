"""
DISC Assessment Test
Main test logic and question flow
"""

import os
import sys
from typing import Dict, Optional
from disc_data import DISC_QUESTIONS
from disc_results import DISCResults


class DISCTest:
    """DISC Personality Assessment Test"""

    def __init__(self):
        """Initialize the DISC test"""
        self.scores = {'D': 0, 'I': 0, 'S': 0, 'C': 0}
        self.current_question = 0
        self.total_questions = len(DISC_QUESTIONS)
        self.answers = []

    def clear_screen(self):
        """Clear the terminal screen"""
        os.system('cls' if os.name == 'nt' else 'clear')

    def display_welcome(self):
        """Display welcome message"""
        self.clear_screen()
        print("="*70)
        print(" "*15 + "BEM-VINDO AO TESTE DE PERSONALIDADE DISC")
        print("="*70)
        print("\nO teste DISC é uma ferramenta de avaliação comportamental que identifica")
        print("seu estilo de personalidade em quatro dimensões:")
        print("\n  🔴 D - DOMINÂNCIA (Executor)")
        print("      Pessoas diretas, focadas em resultados e desafios")
        print("\n  🟡 I - INFLUÊNCIA (Comunicador)")
        print("      Pessoas sociáveis, entusiastas e persuasivas")
        print("\n  🟢 S - ESTABILIDADE (Apoiador)")
        print("      Pessoas calmas, consistentes e cooperativas")
        print("\n  🔵 C - CONFORMIDADE (Analista)")
        print("      Pessoas analíticas, precisas e focadas em qualidade")
        print("\n" + "-"*70)
        print(f"\nVocê responderá a {self.total_questions} perguntas.")
        print("Para cada pergunta, escolha a opção que MAIS se identifica com você.")
        print("\n" + "-"*70)
        input("\nPressione ENTER para começar...")

    def display_progress(self):
        """Display progress bar"""
        progress = (self.current_question / self.total_questions) * 100
        filled = int(progress / 2)
        bar = '█' * filled + '░' * (50 - filled)
        print(f"\nProgresso: [{bar}] {progress:.0f}%")
        print(f"Questão {self.current_question + 1} de {self.total_questions}")

    def ask_question(self, question_index: int) -> str:
        """
        Ask a question and get user's answer

        Args:
            question_index: Index of the question in DISC_QUESTIONS

        Returns:
            Selected trait (D, I, S, or C)
        """
        self.clear_screen()

        question_data = DISC_QUESTIONS[question_index]

        print("="*70)
        self.display_progress()
        print("="*70)

        print(f"\n❓ {question_data['question']}\n")

        # Display options
        options = list(question_data['options'].items())
        for i, (trait, description) in enumerate(options, 1):
            print(f"  {i}. {description}")

        print("\n" + "-"*70)

        # Get user input
        while True:
            try:
                choice = input("\nEscolha uma opção (1-4): ").strip()
                choice_num = int(choice)

                if 1 <= choice_num <= 4:
                    selected_trait = options[choice_num - 1][0]
                    return selected_trait
                else:
                    print("❌ Por favor, escolha um número entre 1 e 4.")
            except ValueError:
                print("❌ Por favor, digite um número válido.")
            except KeyboardInterrupt:
                print("\n\n⚠️  Teste interrompido pelo usuário.")
                sys.exit(0)

    def run_test(self) -> DISCResults:
        """
        Run the complete DISC test

        Returns:
            DISCResults object with test results
        """
        self.display_welcome()

        # Ask all questions
        for i in range(self.total_questions):
            self.current_question = i
            answer = self.ask_question(i)
            self.scores[answer] += 1
            self.answers.append(answer)

        # Create and return results
        self.clear_screen()
        print("\n✅ Teste concluído! Processando resultados...\n")
        return DISCResults(self.scores)

    def quick_test(self, answers: list) -> DISCResults:
        """
        Run test with predefined answers (for testing purposes)

        Args:
            answers: List of answers (D, I, S, or C)

        Returns:
            DISCResults object
        """
        if len(answers) != self.total_questions:
            raise ValueError(f"Expected {self.total_questions} answers, got {len(answers)}")

        for answer in answers:
            if answer not in ['D', 'I', 'S', 'C']:
                raise ValueError(f"Invalid answer: {answer}")
            self.scores[answer] += 1

        self.answers = answers
        return DISCResults(self.scores)

    def reset(self):
        """Reset test state"""
        self.scores = {'D': 0, 'I': 0, 'S': 0, 'C': 0}
        self.current_question = 0
        self.answers = []


class InteractiveDISCTest:
    """Enhanced interactive DISC test with additional features"""

    def __init__(self):
        """Initialize interactive test"""
        self.test = DISCTest()
        self.results = None

    def run(self):
        """Run the interactive test with menu"""
        while True:
            self.test.clear_screen()
            print("="*70)
            print(" "*20 + "TESTE DISC - MENU PRINCIPAL")
            print("="*70)
            print("\n1. Iniciar Teste DISC")
            print("2. Sobre o Teste DISC")
            print("3. Teste Rápido (Demo)")
            print("4. Sair")
            print("\n" + "-"*70)

            try:
                choice = input("\nEscolha uma opção: ").strip()

                if choice == '1':
                    self.results = self.test.run_test()
                    self.display_results_menu()
                elif choice == '2':
                    self.show_about()
                elif choice == '3':
                    self.run_demo()
                elif choice == '4':
                    print("\n👋 Obrigado por usar o Teste DISC!")
                    break
                else:
                    print("\n❌ Opção inválida. Pressione ENTER para continuar...")
                    input()
            except KeyboardInterrupt:
                print("\n\n👋 Obrigado por usar o Teste DISC!")
                break

    def display_results_menu(self):
        """Display results with options"""
        self.test.clear_screen()
        self.results.display_results()

        print("\n" + "="*70)
        print("OPÇÕES:")
        print("="*70)
        print("\n1. Salvar resultados em arquivo TXT")
        print("2. Exportar resultados em JSON")
        print("3. Voltar ao menu principal")
        print("\n" + "-"*70)

        choice = input("\nEscolha uma opção: ").strip()

        if choice == '1':
            self.results.save_to_file()
            input("\nPressione ENTER para continuar...")
        elif choice == '2':
            self.results.export_json()
            input("\nPressione ENTER para continuar...")

    def show_about(self):
        """Show information about DISC test"""
        self.test.clear_screen()
        print("="*70)
        print(" "*25 + "SOBRE O TESTE DISC")
        print("="*70)

        print("\n📚 O QUE É O TESTE DISC?")
        print("-"*70)
        print("\nO teste DISC é uma ferramenta de avaliação comportamental desenvolvida")
        print("pelo psicólogo William Moulton Marston. Ele identifica padrões de")
        print("comportamento em quatro dimensões principais:")
        print("\n🔴 DOMINÂNCIA (D) - Executor")
        print("   Como você responde a problemas e desafios")
        print("   Características: Direto, focado em resultados, competitivo")
        print("\n🟡 INFLUÊNCIA (I) - Comunicador")
        print("   Como você influencia outras pessoas")
        print("   Características: Sociável, entusiasta, persuasivo")
        print("\n🟢 ESTABILIDADE (S) - Apoiador")
        print("   Como você responde ao ritmo e mudanças")
        print("   Características: Calmo, paciente, leal")
        print("\n🔵 CONFORMIDADE (C) - Analista")
        print("   Como você responde a regras e procedimentos")
        print("   Características: Preciso, analítico, sistemático")

        print("\n\n🎯 APLICAÇÕES DO TESTE DISC:")
        print("-"*70)
        print("• Autoconhecimento e desenvolvimento pessoal")
        print("• Seleção e recrutamento")
        print("• Desenvolvimento de lideranças")
        print("• Formação de equipes")
        print("• Melhoria da comunicação")
        print("• Resolução de conflitos")

        print("\n" + "="*70)
        input("\nPressione ENTER para voltar ao menu...")

    def run_demo(self):
        """Run a quick demo with sample answers"""
        self.test.clear_screen()
        print("="*70)
        print(" "*25 + "TESTE RÁPIDO (DEMO)")
        print("="*70)
        print("\nEscolha um perfil para demonstração:")
        print("\n1. Perfil Dominante (D)")
        print("2. Perfil Influente (I)")
        print("3. Perfil Estável (S)")
        print("4. Perfil Analítico (C)")
        print("5. Perfil Balanceado")

        choice = input("\nEscolha uma opção: ").strip()

        # Create sample answers based on choice
        demo_answers = {
            '1': ['D'] * 20,
            '2': ['I'] * 20,
            '3': ['S'] * 20,
            '4': ['C'] * 20,
            '5': ['D', 'I', 'S', 'C', 'D'] * 4
        }

        if choice in demo_answers:
            self.test.reset()
            self.results = self.test.quick_test(demo_answers[choice])
            self.display_results_menu()
        else:
            print("\n❌ Opção inválida.")
            input("\nPressione ENTER para continuar...")
