"""
DISC Assessment Results Processing
Calculates and displays DISC assessment results
"""

from typing import Dict, List, Tuple
from disc_data import PROFILE_DESCRIPTIONS, COMBINATION_PROFILES


class DISCResults:
    """Process and display DISC assessment results"""

    def __init__(self, scores: Dict[str, int]):
        """
        Initialize with DISC scores

        Args:
            scores: Dictionary with D, I, S, C scores
        """
        self.scores = scores
        self.total = sum(scores.values())
        self.percentages = self._calculate_percentages()
        self.primary_profile = self._get_primary_profile()
        self.secondary_profile = self._get_secondary_profile()

    def _calculate_percentages(self) -> Dict[str, float]:
        """Calculate percentage for each DISC dimension"""
        if self.total == 0:
            return {trait: 0.0 for trait in self.scores.keys()}
        return {
            trait: (score / self.total) * 100
            for trait, score in self.scores.items()
        }

    def _get_primary_profile(self) -> str:
        """Get the dominant DISC profile"""
        return max(self.scores, key=self.scores.get)

    def _get_secondary_profile(self) -> str:
        """Get the secondary DISC profile"""
        sorted_profiles = sorted(self.scores.items(), key=lambda x: x[1], reverse=True)
        return sorted_profiles[1][0] if len(sorted_profiles) > 1 else None

    def is_combination_profile(self) -> bool:
        """Check if this is a combination profile (two traits close in score)"""
        if not self.secondary_profile:
            return False

        primary_score = self.scores[self.primary_profile]
        secondary_score = self.scores[self.secondary_profile]

        # If secondary is within 80% of primary, it's a combination
        return secondary_score >= (primary_score * 0.8)

    def get_profile_combination_key(self) -> str:
        """Get the combination profile key (e.g., 'DI', 'SC')"""
        if not self.is_combination_profile():
            return None

        profiles = [self.primary_profile, self.secondary_profile]
        # Sort to match COMBINATION_PROFILES keys
        return ''.join(sorted(profiles, key=lambda x: ['D', 'I', 'S', 'C'].index(x)))

    def display_results(self):
        """Display formatted results"""
        print("\n" + "="*70)
        print(" "*20 + "RESULTADOS DO TESTE DISC")
        print("="*70)

        # Display scores
        print("\n📊 PONTUAÇÃO POR DIMENSÃO:")
        print("-" * 70)
        for trait in ['D', 'I', 'S', 'C']:
            score = self.scores[trait]
            percentage = self.percentages[trait]
            bar = self._create_bar(percentage)
            print(f"  {trait}: {bar} {score:2d} pontos ({percentage:5.1f}%)")

        print("-" * 70)
        print(f"  Total: {self.total} pontos")

        # Display primary profile
        print("\n" + "="*70)
        print(f"🎯 PERFIL PRIMÁRIO: {self.primary_profile} - {PROFILE_DESCRIPTIONS[self.primary_profile]['name']}")
        print("="*70)

        # Check for combination profile
        if self.is_combination_profile():
            combo_key = self.get_profile_combination_key()
            if combo_key in COMBINATION_PROFILES:
                print(f"\n💡 PERFIL COMBINADO: {combo_key}")
                print(f"   {COMBINATION_PROFILES[combo_key]}")
                print(f"\n   Você apresenta características equilibradas de:")
                print(f"   • {self.primary_profile} ({self.percentages[self.primary_profile]:.1f}%) - {PROFILE_DESCRIPTIONS[self.primary_profile]['name']}")
                print(f"   • {self.secondary_profile} ({self.percentages[self.secondary_profile]:.1f}%) - {PROFILE_DESCRIPTIONS[self.secondary_profile]['name']}")

        # Display profile details
        self._display_profile_details(self.primary_profile)

        # If combination, show secondary profile too
        if self.is_combination_profile():
            print("\n" + "="*70)
            print(f"🎯 PERFIL SECUNDÁRIO: {self.secondary_profile} - {PROFILE_DESCRIPTIONS[self.secondary_profile]['name']}")
            print("="*70)
            self._display_profile_details(self.secondary_profile)

        # Display comparison chart
        self._display_comparison()

    def _create_bar(self, percentage: float, width: int = 30) -> str:
        """Create a visual bar for percentage"""
        filled = int((percentage / 100) * width)
        return '█' * filled + '░' * (width - filled)

    def _display_profile_details(self, profile: str):
        """Display detailed information about a profile"""
        details = PROFILE_DESCRIPTIONS[profile]

        print(f"\n📋 CARACTERÍSTICAS PRINCIPAIS:")
        for char in details['characteristics']:
            print(f"   • {char}")

        print(f"\n💪 PONTOS FORTES:")
        for strength in details['strengths']:
            print(f"   ✓ {strength}")

        print(f"\n⚠️  ÁREAS DE ATENÇÃO:")
        for weakness in details['weaknesses']:
            print(f"   • {weakness}")

        print(f"\n🏢 AMBIENTE IDEAL:")
        print(f"   {details['ideal_environment']}")

        print(f"\n💬 ESTILO DE COMUNICAÇÃO:")
        print(f"   {details['communication_style']}")

        print(f"\n🎯 MOTIVAÇÕES:")
        print(f"   {details['motivation']}")

        print(f"\n😰 RESPOSTA AO ESTRESSE:")
        print(f"   {details['stress_response']}")

    def _display_comparison(self):
        """Display comparison of all four dimensions"""
        print("\n" + "="*70)
        print(" "*20 + "COMPARAÇÃO DE PERFIS")
        print("="*70)

        # Create visual comparison
        max_score = max(self.scores.values())

        for trait in ['D', 'I', 'S', 'C']:
            score = self.scores[trait]
            percentage = self.percentages[trait]

            # Create bar relative to max score
            bar_length = int((score / max_score) * 40) if max_score > 0 else 0
            bar = '█' * bar_length

            name = PROFILE_DESCRIPTIONS[trait]['name']
            marker = " ← DOMINANTE" if trait == self.primary_profile else ""

            print(f"\n{trait} - {name}")
            print(f"   {bar} {percentage:.1f}%{marker}")

        print("\n" + "="*70)

    def save_to_file(self, filename: str = "disc_results.txt"):
        """Save results to a text file"""
        import sys
        from io import StringIO

        # Capture print output
        old_stdout = sys.stdout
        sys.stdout = StringIO()

        self.display_results()

        output = sys.stdout.getvalue()
        sys.stdout = old_stdout

        # Save to file
        with open(filename, 'w', encoding='utf-8') as f:
            f.write(output)

        print(f"\n✅ Resultados salvos em: {filename}")

    def get_summary(self) -> Dict:
        """Get a summary of results as a dictionary"""
        return {
            'scores': self.scores,
            'percentages': self.percentages,
            'primary_profile': self.primary_profile,
            'secondary_profile': self.secondary_profile,
            'is_combination': self.is_combination_profile(),
            'combination_key': self.get_profile_combination_key(),
            'primary_name': PROFILE_DESCRIPTIONS[self.primary_profile]['name']
        }

    def export_json(self, filename: str = "disc_results.json"):
        """Export results as JSON"""
        import json

        export_data = {
            'summary': self.get_summary(),
            'detailed_profiles': {
                trait: PROFILE_DESCRIPTIONS[trait]
                for trait in [self.primary_profile]
            }
        }

        if self.is_combination_profile():
            export_data['detailed_profiles'][self.secondary_profile] = \
                PROFILE_DESCRIPTIONS[self.secondary_profile]

        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(export_data, f, ensure_ascii=False, indent=2)

        print(f"✅ Resultados exportados para: {filename}")


def create_comparison_report(results_list: List[DISCResults]):
    """Create a comparison report for multiple DISC assessments"""
    print("\n" + "="*70)
    print(" "*20 + "RELATÓRIO DE COMPARAÇÃO")
    print("="*70)

    for i, result in enumerate(results_list, 1):
        print(f"\nAssessment {i}:")
        print(f"  Primary: {result.primary_profile} ({result.percentages[result.primary_profile]:.1f}%)")
        if result.is_combination_profile():
            print(f"  Secondary: {result.secondary_profile} ({result.percentages[result.secondary_profile]:.1f}%)")
