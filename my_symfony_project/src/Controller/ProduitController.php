<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProduitRepository;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;


final class ProduitController extends AbstractController
{
    #[Route('/produits', name: 'app_produit')]
    public function index(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/produits/{id}', name: 'produit_show')]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/produits/{id}/edit', name: 'produit_edit')]
    public function edit(Produit $produit): Response
    {
        // Logic to edit the product would go here
        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/produits/{id}/delete', name: 'produit_delete')]
    public function delete(Produit $produit): Response
    {
        // Logic to delete the product would go here
        return $this->redirectToRoute('app_produit');
    }

    #[Route('/produits/create', name: 'produit_create')]
    public function create(): Response
    {
        // Logic to create a new product would go here
        return $this->render('produit/create.html.twig');
    }

    #[Route('/produits', name: 'produit_list')]
    public function list(): Response
    {
        // Logic to list all products would go here
        return $this->render('produit/list.html.twig');
    }

    #[Route('/produits/{id}/acheter', name: 'produit_acheter')]
    public function acheter(Produit $produit, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour acheter un produit.');
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isActif()) {
            $this->addFlash('error', 'Votre compte est désactivé, vous ne pouvez pas acheter de produits.');
            return $this->redirectToRoute('app_produit');
        }

        if ($user->getPoints() < $produit->getPrix()) {
            $this->addFlash('error', 'Vous n\'avez pas assez de points pour acheter ce produit.');
            return $this->redirectToRoute('app_produit');
        }

        // Déduire les points
        $user->setPoints($user->getPoints() - $produit->getPrix());
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Achat effectué avec succès !');

        return $this->redirectToRoute('app_produit');
    }

    

}

