#!/usr/bin/env python3
"""
Quick Demo - Comprehensive test of all DISC profiles
"""

from disc_test import DISCTest
from disc_results import DISCResults

print("\n" + "="*70)
print(" "*15 + "DEMONSTRAÇÃO COMPLETA DO SISTEMA DISC")
print("="*70)

# Test 1: Pure Dominant Profile
print("\n\n🔴 TESTE 1: PERFIL DOMINANTE PURO")
print("="*70)
test1 = DISCTest()
results1 = test1.quick_test(['D'] * 20)
print(f"\nPerfil: {results1.primary_profile} ({results1.percentages['D']:.1f}%)")
print(f"Descrição: {results1.get_summary()['primary_name']}")

# Test 2: Pure Influencer Profile
print("\n\n🟡 TESTE 2: PERFIL INFLUENTE PURO")
print("="*70)
test2 = DISCTest()
results2 = test2.quick_test(['I'] * 20)
print(f"\nPerfil: {results2.primary_profile} ({results2.percentages['I']:.1f}%)")
print(f"Descrição: {results2.get_summary()['primary_name']}")

# Test 3: Combined Profile (D+I)
print("\n\n💡 TESTE 3: PERFIL COMBINADO (D+I)")
print("="*70)
test3 = DISCTest()
results3 = test3.quick_test(['D'] * 10 + ['I'] * 9 + ['S'] * 1)
print(f"\nPerfil Primário: {results3.primary_profile} ({results3.percentages[results3.primary_profile]:.1f}%)")
print(f"Perfil Secundário: {results3.secondary_profile} ({results3.percentages[results3.secondary_profile]:.1f}%)")
print(f"É Combinado? {results3.is_combination_profile()}")
if results3.is_combination_profile():
    print(f"Chave de Combinação: {results3.get_profile_combination_key()}")

# Test 4: Balanced Profile
print("\n\n⚖️  TESTE 4: PERFIL EQUILIBRADO")
print("="*70)
test4 = DISCTest()
results4 = test4.quick_test(['D', 'I', 'S', 'C'] * 5)
print(f"\nDistribuição:")
for trait in ['D', 'I', 'S', 'C']:
    print(f"  {trait}: {results4.percentages[trait]:.1f}%")

# Test 5: Save and Export
print("\n\n💾 TESTE 5: EXPORTAÇÃO DE RESULTADOS")
print("="*70)
test5 = DISCTest()
results5 = test5.quick_test(['C'] * 12 + ['S'] * 6 + ['D'] * 2)
results5.save_to_file("/tmp/disc_test_result.txt")
results5.export_json("/tmp/disc_test_result.json")
print("✅ Arquivos exportados:")
print("   - /tmp/disc_test_result.txt")
print("   - /tmp/disc_test_result.json")

# Display detailed results for one profile
print("\n\n📊 TESTE 6: RESULTADO COMPLETO DETALHADO")
print("="*70)
print("Mostrando resultado completo para um perfil Analítico (C)...")
test6 = DISCTest()
results6 = test6.quick_test(['C'] * 15 + ['S'] * 4 + ['D'] * 1)
results6.display_results()

print("\n\n" + "="*70)
print(" "*20 + "DEMONSTRAÇÃO CONCLUÍDA!")
print("="*70)
print("\n✅ Todos os testes foram executados com sucesso!")
print("📚 Consulte o README.md para mais informações")
print("🚀 Execute 'python main.py' para usar o sistema interativo")
