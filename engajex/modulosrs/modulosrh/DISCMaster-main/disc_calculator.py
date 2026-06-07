import pandas as pd
import numpy as np
from collections import Counter

class DISCCalculator:
    def __init__(self):
        # DISC profile configuration
        self.profiles = ['D', 'I', 'S', 'C']
        self.questions_per_profile = 7
        self.total_questions = 28
        
    def process_excel_file(self, filepath):
        """Process Excel file and calculate DISC profiles"""
        try:
            # Read Excel file
            df = pd.read_excel(filepath)
            
            # Validate data structure
            self._validate_dataframe(df)
            
            # Process each person
            individuals = []
            for _, row in df.iterrows():
                person_data = self._calculate_person_disc(row)
                if person_data:
                    individuals.append(person_data)
            
            # Calculate statistics
            statistics = self._calculate_statistics(individuals)
            
            return {
                'individuals': individuals,
                'statistics': statistics
            }
            
        except Exception as e:
            raise Exception(f"Erro ao processar planilha: {str(e)}")
    
    def _validate_dataframe(self, df):
        """Validate the DataFrame structure"""
        if df.empty:
            raise Exception("Planilha vazia ou sem dados")
        
        columns = df.columns.tolist()
        
        if len(columns) < 30:  # ID + Nome + 28 questões
            raise Exception(f"Planilha deve ter pelo menos 30 colunas (ID, Nome + 28 questões). Encontradas: {len(columns)}")
        
        # Check for ID and Name columns
        id_found = any('id' in str(col).lower() for col in columns[:2])
        name_found = any('nome' in str(col).lower() or 'name' in str(col).lower() for col in columns[:2])
        
        if not id_found or not name_found:
            raise Exception("Planilha deve conter colunas 'ID' e 'Nome' nas primeiras posições")
        
        # Validate question values (should be 1-5)
        question_columns = columns[2:30]  # First 28 question columns
        for col in question_columns:
            values = df[col].dropna()
            if not values.empty:
                if not all(isinstance(v, (int, float)) and 1 <= v <= 5 for v in values):
                    raise Exception(f"Valores das questões devem estar entre 1 e 5. Verifique a coluna: {col}")
    
    def _calculate_person_disc(self, row):
        """Calculate DISC profile for a single person"""
        try:
            # Get basic info
            person_id = row.iloc[0]
            person_name = row.iloc[1]
            
            # Get question responses (28 questions starting from column 2)
            responses = row.iloc[2:30].values
            
            # Check if we have enough responses
            if len(responses) < self.total_questions:
                return None
            
            # Convert to numeric, skip if invalid
            try:
                responses = [float(x) for x in responses if pd.notna(x)]
                if len(responses) < self.total_questions:
                    return None
            except:
                return None
            
            # Calculate scores for each profile
            scores = {}
            for i, profile in enumerate(self.profiles):
                start_idx = i * self.questions_per_profile
                end_idx = start_idx + self.questions_per_profile
                profile_responses = responses[start_idx:end_idx]
                
                # Calculate percentage (sum of responses / max possible * 100)
                max_possible = self.questions_per_profile * 5  # 7 questions * 5 max points
                actual_sum = sum(profile_responses)
                percentage = (actual_sum / max_possible) * 100
                scores[profile] = percentage
            
            # Find dominant profile
            dominant_profile = max(scores.keys(), key=lambda k: scores[k])
            
            return {
                'id': person_id,
                'name': person_name,
                'scores': scores,
                'dominant_profile': dominant_profile
            }
            
        except Exception as e:
            print(f"Error processing person {row.iloc[0] if len(row) > 0 else 'unknown'}: {str(e)}")
            return None
    
    def _calculate_statistics(self, individuals):
        """Calculate general statistics"""
        if not individuals:
            return []
        
        total_people = len(individuals)
        
        # Count dominant profiles
        dominant_counts = Counter(person['dominant_profile'] for person in individuals)
        
        # Calculate averages for each profile
        profile_averages = {}
        for profile in self.profiles:
            avg_score = np.mean([person['scores'][profile] for person in individuals])
            profile_averages[profile] = avg_score
        
        # Build statistics array
        statistics = [
            {'label': 'Total de Pessoas', 'value': total_people}
        ]
        
        # Add dominant profile counts
        for profile in self.profiles:
            count = dominant_counts.get(profile, 0)
            percentage = (count / total_people) * 100 if total_people > 0 else 0
            statistics.append({
                'label': f'Perfil {profile} Dominante',
                'value': f'{count} ({percentage:.1f}%)'
            })
        
        # Add average scores
        for profile in self.profiles:
            avg = profile_averages[profile]
            statistics.append({
                'label': f'Média Perfil {profile}',
                'value': f'{avg:.1f}%'
            })
        
        return statistics
