<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserProfileForm; 
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;
use App\Entity\User;

class UserController extends AbstractController
{
    #[Route('/profil/edit', name: 'user_profile_edit')]
    #[IsGranted('ROLE_USER')] 
    public function editProfile(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserProfileForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('app_produit');
        }

        return $this->render('user/edit_profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }
    #[Route('/profil', name: 'app_profil')]
    public function profil(): Response
    {
        $user = $this->getUser();
        return $this->render('user/profil.html.twig', [
            'notifications' => $user->getNotifications(),
        ]);
    }
    #[Route('/user/list', name: 'admin_user_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/user_list.html.twig', [
            'users' => $users,
        ]);
    }
}
