<?php
class BatchManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Inicia uma nova rodada de testes para um candidato
     */
    public function startNewBatch($candidateId, $notes = '') {
        try {
            $stmt = $this->db->prepare("CALL start_new_test_batch(?, ?)");
            $stmt->execute([$candidateId, $notes]);
            
            // Retorna o ID do novo batch
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erro ao criar novo batch: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca todos os batches de um candidato
     */
    public function getCandidateBatches($candidateId) {
        $stmt = $this->db->prepare("
            SELECT 
                b.id,
                b.created_at,
                b.notes,
                COUNT(tr.id) as completed_tests
            FROM test_batches b
            LEFT JOIN test_results tr ON b.id = tr.batch_id AND tr.candidate_id = b.candidate_id
            WHERE b.candidate_id = ?
            GROUP BY b.id
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca resultados de um batch específico
     */
    public function getBatchResults($batchId, $candidateId) {
        $stmt = $this->db->prepare("
            SELECT 
                tr.*,
                b.created_at as batch_date,
                b.notes as batch_notes
            FROM test_results tr
            INNER JOIN test_batches b ON tr.batch_id = b.id
            WHERE tr.batch_id = ? AND tr.candidate_id = ?
            ORDER BY tr.completed_at
        ");
        $stmt->execute([$batchId, $candidateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se um candidato pode iniciar uma nova rodada de testes
     */
    public function canStartNewBatch($candidateId) {
        // Verifica se todos os testes do último batch foram concluídos
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(ta.id) as total_assignments,
                COUNT(tr.id) as completed_tests
            FROM test_batches b
            INNER JOIN test_assignments ta ON b.candidate_id = ta.candidate_id
            LEFT JOIN test_results tr ON ta.candidate_id = tr.candidate_id 
                AND ta.test_type = tr.test_type 
                AND tr.batch_id = b.id
            WHERE b.candidate_id = ?
            AND b.id = (
                SELECT MAX(id) 
                FROM test_batches 
                WHERE candidate_id = ?
            )
        ");
        $stmt->execute([$candidateId, $candidateId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total_assignments'] === $result['completed_tests'];
    }
}
