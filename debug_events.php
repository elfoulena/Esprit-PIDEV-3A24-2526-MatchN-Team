<?php
require_once __DIR__.'/vendor/autoload.php';
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$events = $em->getRepository(\App\Entity\Evenement::class)->findAll();

echo "Total events: " . count($events) . "\n";
$now = new \DateTime();
echo "Current Time: " . $now->format('Y-m-d H:i:s') . "\n";

foreach ($events as $e) {
    echo "ID: " . $e->getId_evenement() . " | Title: " . $e->getTitre() . " | End Date: " . ($e->getDate_fin() ? $e->getDate_fin()->format('Y-m-d H:i:s') : 'NULL') . "\n";
    if ($e->getDate_fin() < $now) {
        echo "  -> FINISHED\n";
    } else {
        echo "  -> UPCOMING/ONGOING\n";
    }
}
