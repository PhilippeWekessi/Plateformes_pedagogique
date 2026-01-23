<?php
session_start();

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Test Session Simple</h1>";

// Premier chargement : on crée la session
if (!isset($_SESSION['compteur'])) {
    $_SESSION['compteur'] = 1;
    $_SESSION['test_nom'] = 'Philippe';
    echo "<p style='color: blue;'>Session créée ! Rechargez cette page.</p>";
} else {
    // Rechargement : on incrémente
    $_SESSION['compteur']++;
    echo "<p style='color: green;'>Session fonctionne ! Compteur: " . $_SESSION['compteur'] . "</p>";
    echo "<p>Nom: " . $_SESSION['test_nom'] . "</p>";
}

echo "<hr>";
echo "<h3>Détails de la session :</h3>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session path: " . session_save_path() . "\n";
echo "\nContenu complet:\n";
print_r($_SESSION);
echo "</pre>";
?>
