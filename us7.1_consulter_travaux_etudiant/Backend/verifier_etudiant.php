<?php
function verifierEtudiant($pdo, $etudiant_id) {
    try {
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE id_user = ? AND role = 'etudiant'");
        $stmt->execute([$etudiant_id]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Erreur vérification étudiant: " . $e->getMessage());
        return false;
    }
}
?>
