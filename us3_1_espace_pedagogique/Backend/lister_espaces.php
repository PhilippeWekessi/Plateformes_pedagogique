<?php
require_once "../config/database.php";

$stmt = $pdo->query("SELECT * FROM espaces_pedagogiques ORDER BY created_at DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
