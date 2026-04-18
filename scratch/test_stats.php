<?php
// scratch/test_stats.php

use App\Kernel;
use App\Entity\Equipe;
use App\Service\AITeamAnalyticsService;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

$em = $container->get('doctrine.orm.entity_manager');
$aiService = $container->get(AITeamAnalyticsService::class);

$equipe = $em->getRepository(Equipe::class)->findOneBy([]);

if (!$equipe) {
    echo "No team found.\n";
    exit;
}

echo "Testing stats for team: " . $equipe->getNomEquipe() . " (ID: " . $equipe->getIdEquipe() . ")\n";

$start = microtime(true);
$stats = $aiService->getTeamAdvancedStats($equipe);
$end = microtime(true);

echo "Execution time: " . ($end - $start) . " seconds\n";
echo "Stats size: " . strlen(serialize($stats)) . " bytes\n";
echo "Stats structure keys: " . implode(', ', array_keys($stats)) . "\n";
