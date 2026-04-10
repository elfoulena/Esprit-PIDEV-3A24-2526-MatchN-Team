<?php
require 'vendor/autoload.php';
use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();
$sm = $conn->createSchemaManager();

echo "Table: participation_evenement\n";
foreach ($sm->listTableColumns('participation_evenement') as $col) {
    echo "  - " . $col->getName() . "\n";
}

echo "\nTable: evenement\n";
foreach ($sm->listTableColumns('evenement') as $col) {
    echo "  - " . $col->getName() . "\n";
}
