<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(ProductRepository $repo): Response
    {
        $products = $repo->findAll();
        return $this->render('home/index.html.twig', [
            'title' => 'Bienvenue sur notre Boutique',
            'products' => $products
        ]);
    }

    #[Route('/product/show/{id}', name: 'product_show')]
    public function show(ProductRepository $repo, $id)
    {
        /*
        Pour sélectionner un produit dans la BDD, nous utilisons le principe de route paramétrée
        Dans la route, on définit un paramètre de type {id}
        Lorsque nous transmettons dans l'URL par exemple une route '/blog/9', on envoie un id conne en BDD dans l'URL
        Symfony va automatiquement récupérer ce paramètre et le transmettre en argument de la méthode show()
        */
        $produit = $repo->find($id);
        return $this->render('product/show.html.twig', [
            'produit' => $produit
        ]);
    }

    #[Route('/profil', name: 'profil')]
    public function profil(Security $security): Response
    {
        $user = $security->getUser();
        dump($user);
        return $this->render('home/profil.html.twig', [
            'user' => $user
        ]);
    }

}
