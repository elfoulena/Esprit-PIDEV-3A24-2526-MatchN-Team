<?php
require_once __DIR__.'/vendor/autoload.php';
// No Kernel needed, just DBAL
use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();

$conn = $kernel->getContainer()->get('doctrine')->getConnection();
$schemaManager = $conn->createSchemaManager();
$columns = $schemaManager->listTableColumns('utilisateur');

echo "Columns of 'utilisateur':\n";
foreach ($columns as $column) {
    echo "  - " . $column->getName() . " (" . $column->getType()->getName() . ")\n";
}
