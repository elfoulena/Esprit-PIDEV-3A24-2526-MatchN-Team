<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notifications')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    #[Route('/{id}/read', name: 'app_notification_mark_read', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function markRead(int $id, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $notification = $em->getRepository(Notification::class)->find($id);
        if (!$notification) {
            throw $this->createNotFoundException('Notification introuvable.');
        }

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $notification->getUser()?->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('Acces refuse.');
        }

        if (!$this->isCsrfTokenValid('mark_notification_' . $notification->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        if (!$notification->isRead()) {
            $notification->setIsRead(true);
            $em->flush();
        }

        return $this->redirectBack($request);
    }

    #[Route('/read-all', name: 'app_notification_mark_all_read', methods: ['POST'])]
    public function markAllRead(Request $request, NotificationRepository $notificationRepository): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('mark_all_notifications', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if ($currentUser) {
            $notificationRepository->markAllAsReadForUser($currentUser);
        }

        return $this->redirectBack($request);
    }

    private function redirectBack(Request $request): RedirectResponse
    {
        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?: $this->generateUrl('app_login'));
    }
}
