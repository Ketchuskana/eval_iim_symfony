<?php

namespace App\Controller;

use App\Entity\User;
use App\Message\AddPointsToUsers;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/users', name: 'admin_user_index')]
    public function list(UserRepository $repo): Response
    {
        return $this->render('admin/user_list.html.twig', [
            'users' => $repo->findAll(),
        ]);
    }

    #[Route('/admin/users/{id}/toggle', name: 'admin_user_toggle')]
    public function toggleUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setActif(!$user->isActif());
        $em->flush();

        $this->addFlash('success', 'Statut de l’utilisateur mis à jour.');
        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/admin/bonus', name: 'admin_add_bonus')]
    public function addBonus(MessageBusInterface $bus): Response
    {
        $bus->dispatch(new AddPointsToUsers());

        $this->addFlash('success', '1000 points seront ajoutés à tous les utilisateurs actifs.');
        return $this->redirectToRoute('admin_user_index');
    }

}
