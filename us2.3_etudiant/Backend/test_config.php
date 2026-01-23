<?php
// test_config.php - Test de la configuration de la base de données

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Configuration</h1>";

$config_path = "../../config/database.php";

echo "<h2>1. Recherche du fichier de configuration</h2>";
echo "Chemin: " . realpath($config_path) . "<br>";

if (!file_exists($config_path)) {
    echo "<span style='color: red;'>❌ Fichier introuvable</span><br>";
    echo "Chemin absolu: " . __DIR__ . "<br>";
    echo "Chemin relatif: " . $config_path . "<br>";
    
    // Essayer différents chemins
    $possible_paths = [
        "../../config/database.php",
        "../../../config/database.php",
        "../config/database.php"
    ];
    
    echo "<h3>Recherche dans les chemins alternatifs:</h3>";
    foreach ($possible_paths as $path) {
        $absolute = realpath(dirname(__FILE__) . '/' . $path);
        echo "$path → " . ($absolute ? "✅ Trouvé: $absolute" : "❌ Introuvable") . "<br>";
    }
    
    exit;
}

echo "<span style='color: green;'>✅ Fichier trouvé</span><br>";

echo "<h2>2. Inclusion du fichier</h2>";
require_once $config_path;

echo "<span style='color: green;'>✅ Fichier inclus</span><br>";

echo "<h2>3. Test de la connexion PDO</h2>";
if (!isset($pdo)) {
    echo "<span style='color: red;'>❌ Variable \$pdo non définie</span><br>";
    exit;
}

echo "<span style='color: green;'>✅ Variable \$pdo définie</span><br>";

try {
    // Test de la connexion
    $pdo->query("SELECT 1");
    echo "<span style='color: green;'>✅ Connexion à la base de données réussie</span><br>";
    
    echo "<h2>4. Test des tables</h2>";
    
    // Test table users
    try {
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Table users: <span style='color: green;'>✅ Existe</span><br>";
        echo "Colonnes: " . implode(", ", $columns) . "<br>";
        
        // Vérifier promotion_id
        if (in_array('promotion_id', $columns)) {
            echo "Colonne promotion_id: <span style='color: green;'>✅ Présente</span><br>";
        } else {
            echo "Colonne promotion_id: <span style='color: red;'>❌ Absente</span><br>";
            echo "Pour l'ajouter: ALTER TABLE users ADD COLUMN promotion_id INT NULL AFTER role;<br>";
        }
        
    } catch (Exception $e) {
        echo "Table users: <span style='color: red;'>❌ Erreur: " . $e->getMessage() . "</span><br>";
    }
    
    // Test table promotion
    try {
        $stmt = $pdo->query("SELECT * FROM promotion LIMIT 5");
        $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Table promotion: <span style='color: green;'>✅ Existe</span><br>";
        echo "Nombre de promotions: " . count($promotions) . "<br>";
        
        if (count($promotions) > 0) {
            echo "<h3>Promotions disponibles:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Année</th><th>Date création</th></tr>";
            foreach ($promotions as $promo) {
                echo "<tr>";
                echo "<td>" . ($promo['id_promotion'] ?? 'N/A') . "</td>";
                echo "<td>" . ($promo['nom_promotion'] ?? 'N/A') . "</td>";
                echo "<td>" . ($promo['annee_academique'] ?? 'N/A') . "</td>";
                echo "<td>" . ($promo['date_creation'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<span style='color: orange;'>⚠ Aucune promotion trouvée</span><br>";
            echo "Pour ajouter des promotions:<br>";
            echo "INSERT INTO promotion (nom_promotion, annee_academique) VALUES ('Licence 1', '2025-2026');<br>";
        }
        
    } catch (Exception $e) {
        echo "Table promotion: <span style='color: red;'>❌ Erreur: " . $e->getMessage() . "</span><br>";
    }
    
    echo "<h2>5. Test insertion manuelle</h2>";
    $test_email = "test_" . time() . "@test.com";
    $test_password = password_hash("Test123!", PASSWORD_DEFAULT);
    
    try {
        $sql = "INSERT INTO users (nom, prenom, email, password, role, promotion_id, statut) 
                VALUES ('Test', 'Test', ?, ?, 'etudiant', 1, 'actif')";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$test_email, $test_password]);
        
        if ($result) {
            echo "<span style='color: green;'>✅ Insertion test réussie</span><br>";
            echo "ID: " . $pdo->lastInsertId() . "<br>";
            
            // Nettoyer
            $pdo->query("DELETE FROM users WHERE email = '$test_email'");
            echo "Données de test nettoyées<br>";
        } else {
            echo "<span style='color: red;'>❌ Échec insertion test</span><br>";
        }
        
    } catch (Exception $e) {
        echo "<span style='color: red;'>❌ Erreur insertion: " . $e->getMessage() . "</span><br>";
        echo "SQL: $sql<br>";
    }
    
} catch (PDOException $e) {
    echo "<span style='color: red;'>❌ Erreur de connexion: " . $e->getMessage() . "</span><br>";
    echo "Code d'erreur: " . $e->getCode() . "<br>";
}

echo "<h2>6. Tests des endpoints</h2>";
echo "<ul>";
echo "<li><a href='get_promotions.php' target='_blank'>Test get_promotions.php</a></li>";
echo "<li><a href='test_form.html' target='_blank'>Test formulaire</a></li>";
echo "</ul>";

echo "<h2>7. Création du formulaire de test</h2>";

// Créer un fichier HTML de test
$test_form = "
<!DOCTYPE html>
<html>
<head><title>Test API</title></head>
<body>
    <h1>Test API Création Étudiant</h1>
    <form id='testForm'>
        <input type='text' name='nom' placeholder='Nom' value='Dupont'><br>
        <input type='text' name='prenom' placeholder='Prénom' value='Jean'><br>
        <input type='email' name='email' placeholder='Email' value='jean.dupont@test.com'><br>
        <input type='number' name='promotion_id' placeholder='Promotion ID' value='1'><br>
        <button type='submit'>Tester</button>
    </form>
    <div id='result'></div>
    
    <script>
    document.getElementById('testForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        console.log('Données:', data);
        
        try {
            const response = await fetch('creer_etudiant.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            });
            
            const result = await response.json();
            console.log('Résultat:', result);
            
            document.getElementById('result').innerHTML = 
                '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
                
        } catch (error) {
            console.error('Erreur:', error);
            document.getElementById('result').innerHTML = 'Erreur: ' + error.message;
        }
    });
    </script>
</body>
</html>
";

file_put_contents(dirname(__FILE__) . '/test_form.html', $test_form);
echo "<span style='color: green;'>✅ Formulaire de test créé</span><br>";
echo "<a href='test_form.html' target='_blank'>Ouvrir le formulaire de test</a>";
?>