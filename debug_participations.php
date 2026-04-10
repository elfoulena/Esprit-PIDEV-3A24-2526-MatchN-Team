<?php
require_once __DIR__.'/vendor/autoload.php';
use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

$participations = $em->getRepository(\App\Entity\ParticipationEvenement::class)->findAll();
echo "Total participations in DB: " . count($participations) . "\n";

foreach ($participations as $p) {
    echo "ID: " . $p->getId_participation() . 
         " | Event: " . ($p->getEvenement() ? $p->getEvenement()->getTitre() : 'NONE') . 
         " | User ID: " . ($p->getUtilisateur() ? $p->getUtilisateur()->getId() : 'NONE') . "\n";
}

$events = $em->getRepository(\App\Entity\Evenement::class)->findAll();
foreach ($events as $e) {
    echo "Event: " . $e->getTitre() . " | Nombre Actuel: " . $e->getNombre_actuel() . "\n";
}
