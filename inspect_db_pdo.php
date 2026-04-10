<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=rh_tesst", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $table = 'participation_evenement';
    $stmt = $pdo->query("DESCRIBE `$table`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns of '$table':\n";
    foreach ($columns as $column) {
        echo "  - " . $column['Field'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
