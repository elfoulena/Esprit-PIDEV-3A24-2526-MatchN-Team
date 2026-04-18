<?php

namespace App\EventSubscriber;

use App\Entity\LoginHistory;
use App\Service\GeoLocationService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Doctrine\DBAL\Types\Types;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private GeoLocationService $geoService,
        private MailerService $mailer,
        private RouterInterface $router,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLoginSuccess'];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user    = $event->getUser();
        $request = $event->getRequest();
        $ip      = $request->getClientIp();
        $country = ($ip === '127.0.0.1' || $ip === '::1')
            ? 'LOCAL'
            : $this->geoService->getCountry($ip);

        // 1. Enregistrer la connexion
        $history = new LoginHistory();
        $history->setIp($ip);
        $history->setCountry($country);
        $history->setUser($user);
        $this->em->persist($history);
        $this->em->flush();

        // 2. Vérifier les connexions de la dernière heure
        $oneHourAgo   = new \DateTime('-1 hour');
        $recentLogins = $this->em->getRepository(LoginHistory::class)
            ->createQueryBuilder('l')
            ->where('l.user = :user')
            ->andWhere('l.createdAt >= :time')
            ->setParameter('user', $user)
            ->setParameter('time', $oneHourAgo, Types::DATETIME_MUTABLE)            
            ->getQuery()
            ->getResult();

        $countries = [];

    foreach ($recentLogins as $login) {
        if ($login->getCountry() && $login->getCountry() !== 'LOCAL') {
            $countries[] = $login->getCountry();
        }
    }

    $uniqueCountries = array_unique($countries);

         if (count($uniqueCountries) >= 2) {

        $user->setBlockedUntil(new \DateTime('+1 hour'));

        $this->em->flush();

        $this->mailer->sendSecurityAlertEmail(
            $user->getEmail(),
            $user->getNom(),
            $ip,
            $country
        );
        $response = new RedirectResponse(
                $this->router->generate('app_logout')
            );
            $event->setResponse($response);

    }
    }
}