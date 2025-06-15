<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProduitType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProduitRepository;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\SendMessage;
use App\Entity\User;


final class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/produit/list', name: 'list_produit')]
    #[IsGranted('ROLE_ADMIN')]
    public function list(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        return $this->render('admin/produit_list.html.twig', [
            'produits' => $produits,
        ]);
        
    }

    #[Route('/produit/{id}/edit', name: 'produit_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager, MessageBusInterface $bus, UserRepository $userRepo ): Response 
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $adminUsers = $userRepo->findAdmins();
            foreach ($adminUsers as $admin) {
                $bus->dispatch(new SendMessage($admin->getId(), "Produit modifié : {$produit->getNom()}"));
            }

            $this->addFlash('success', 'Produit modifié');
            return $this->redirectToRoute('list_produit');
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/produit/{id}/delete', name: 'produit_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Produit $produit, EntityManagerInterface $em): Response
    {
        $em->remove($produit);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé.');
        return $this->redirectToRoute('list_produit');
    }


    #[Route('/produit/create', name: 'produit_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('list_produit');
        }

        return $this->render('admin/produit_form.html.twig', [
            'form' => $form->createView(),
            'editMode' => true,
        ]);
    }

    
    #[Route('/produit/{id}/acheter', name: 'produit_acheter')]
    public function acheter(Produit $produit, EntityManagerInterface $em, MessageBusInterface $bus,  UserRepository $userRepo ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour acheter un produit.');
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isActif()) {
            $this->addFlash('error', 'Votre compte est désactivé, vous ne pouvez pas acheter de produits.');

            $bus->dispatch(new SendMessage(
                $user->getId(),
                'Votre compte est désactivé. Vous ne pouvez pas effectuer d\'achat.'
            ));

            return $this->redirectToRoute('app_produit');
        }


        if ($user->getPoints() < $produit->getPrix()) {
            $this->addFlash('error', 'Vous n\'avez pas assez de points pour acheter ce produit.');
            return $this->redirectToRoute('app_produit');
        }

        $user->setPoints($user->getPoints() - $produit->getPrix());
        $em->persist($user);
        $em->flush();

        $notifMessage = new SendMessage(
            $user->getId(),
            sprintf('Vous avez acheté le produit "%s".', $produit->getNom())
        );
        $bus->dispatch($notifMessage);

        $admins = $userRepo->findByRole('ROLE_ADMIN');
        foreach ($admins as $admin) {
            $bus->dispatch(new SendMessage($admin->getId(), $user->getEmail().' a acheté '.$produit->getNom()));
        }

        $this->addFlash('success', 'Achat effectué avec succès !');
        return $this->redirectToRoute('app_produit');

    }

    #[Route('/produit/{id}', name: 'produit_show')]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

}

