<?php
session_start();

if(!isset($_SESSION['etudiant_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}
?>
