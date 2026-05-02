<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Notification;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testNotificationDefaultsAndSetters(): void
    {
        $notification = new Notification();
        $user = new User();
        $createdAt = new \DateTimeImmutable('2026-04-26 10:00:00');

        $notification
            ->setUser($user)
            ->setTitre('Nouveau projet disponible')
            ->setMessage('Un nouveau projet "ERP" a ete ajoute.')
            ->setLien('/employe/projets')
            ->setIsRead(true)
            ->setCreatedAt($createdAt);

        self::assertSame($user, $notification->getUser());
        self::assertSame('Nouveau projet disponible', $notification->getTitre());
        self::assertSame('Un nouveau projet "ERP" a ete ajoute.', $notification->getMessage());
        self::assertSame('/employe/projets', $notification->getLien());
        self::assertTrue($notification->isRead());
        self::assertSame($createdAt, $notification->getCreatedAt());
    }

    public function testCreatedAtIsInitializedOnConstruct(): void
    {
        $before = new \DateTimeImmutable('-1 second');
        $notification = new Notification();
        $after = new \DateTimeImmutable('+1 second');

        self::assertNotNull($notification->getCreatedAt());
        self::assertGreaterThanOrEqual($before->getTimestamp(), $notification->getCreatedAt()->getTimestamp());
        self::assertLessThanOrEqual($after->getTimestamp(), $notification->getCreatedAt()->getTimestamp());
        self::assertFalse($notification->isRead());
    }
}

