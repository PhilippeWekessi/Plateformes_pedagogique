<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../../config/database.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        exit;
    }

    if (!isset($_POST['id_travail']) || !isset($_FILES['fichier'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
        exit;
    }

    $id_travail = $_POST['id_travail'];
    $fichier = $_FILES['fichier'];
    $id_etudiant = 7; // ID étudiant fixe pour le développement

    // Vérification des extensions autorisées
    $ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx', 'zip'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Format de fichier non autorisé']);
        exit;
    }

    // Vérifier que le travail est assigné à cet étudiant
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM assigner WHERE id_travail = ? AND id_etudiant = ?");
    $stmt->execute([$id_travail, $id_etudiant]);

    if ($stmt->fetchColumn() === 0) {
        echo json_encode(['success' => false, 'message' => 'Ce travail ne vous est pas assigné']);
        exit;
    }

    // Vérification si le travail a déjà été livré
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM livraison WHERE id_travail = ? AND id_etudiant = ?");
    $stmt->execute([$id_travail, $id_etudiant]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Travail déjà livré']);
        exit;
    }

    // Upload du fichier
    $uploadDir = __DIR__ . '/../../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = uniqid() . '_' . $id_etudiant . '_' . $id_travail . '.' . $ext;
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($fichier['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Échec du téléversement du fichier']);
        exit;
    }

    // Insertion dans la base de données
    $stmt = $pdo->prepare("INSERT INTO livraison (id_travail, id_etudiant, fichier, date_livraison) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$id_travail, $id_etudiant, $fileName]);

    echo json_encode(['success' => true, 'message' => 'Travail livré avec succès']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
