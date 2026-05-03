<?php

namespace App\Tests\Unit\Controller\Admin;

use App\Controller\Admin\ProjetController;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ProjetControllerNotificationTest extends TestCase
{
    public function testCreateProjectNotificationsPersistsOneNotificationPerUser(): void
    {
        $persisted = [];

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::exactly(2))
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persisted): void {
                $persisted[] = $entity;
            });

        $controller = new ProjetController();
        $users = [new User(), new User()];

        $this->invokeCreateProjectNotifications(
            $controller,
            $entityManager,
            $users,
            'Nouveau projet disponible',
            'Un nouveau projet "CRM" a ete ajoute et il est visible pour les employes.',
            '/employe/projets'
        );

        self::assertCount(2, $persisted);
        foreach ($persisted as $index => $notification) {
            self::assertInstanceOf(Notification::class, $notification);
            self::assertSame($users[$index], $notification->getUser());
            self::assertSame('Nouveau projet disponible', $notification->getTitre());
            self::assertSame(
                'Un nouveau projet "CRM" a ete ajoute et il est visible pour les employes.',
                $notification->getMessage()
            );
            self::assertSame('/employe/projets', $notification->getLien());
            self::assertFalse($notification->isRead());
            self::assertInstanceOf(\DateTimeImmutable::class, $notification->getCreatedAt());
        }
    }

    public function testCreateProjectNotificationsWithEmptyUsersPersistsNothing(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::never())
            ->method('persist');

        $controller = new ProjetController();

        $this->invokeCreateProjectNotifications(
            $controller,
            $entityManager,
            [],
            'Nouveau projet disponible',
            'Message',
            '/freelancer/projets'
        );

        self::expectNotToPerformAssertions();
    }

    /**
     * @param User[] $users
     */
    private function invokeCreateProjectNotifications(
        ProjetController $controller,
        EntityManagerInterface $entityManager,
        array $users,
        string $title,
        string $message,
        ?string $link
    ): void {
        $method = new \ReflectionMethod(ProjetController::class, 'createProjectNotifications');
        $method->setAccessible(true);
        $method->invoke($controller, $entityManager, $users, $title, $message, $link);
    }
}

