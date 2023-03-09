<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cs): Response
    {
        $cartWithData = $cs->getCartWithData();
        $total = $cs->getTotal();


        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add($id, CartService $cs) 
    {
      
        $cs->add($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove($id, CartService $cs)
    {
       
        $cs->remove($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/decrement/{id}', name: 'cart_decrement')]
    public function decrement($id, CartService $cs)
    {
        
        $cs->decrement($id);
        return $this->redirectToRoute('app_cart');
    }
    
    #[Route('/cart/empty', name: 'cart_empty')]
    public function empty(CartService $cs)
    {
        // // solution 1 : remplacer le panier en session par un tableau vide
        // $session->set('cart', []);
        $cs->empty();
        return $this->redirectToRoute('app_cart');
        
        // solution 2 : utiliser remove() pour supprimer un attribut de session
        // $session->remove('cart');
        
        // solution 3 : utiliser clear() pour supprimer TOUS LES ATTRIBUTS de session
        // $session->clear();
    }
    
    // #[Route('/cart/order', name: 'cart_order')]
    // public function validation(CartService $cs, EntityManagerInterface $em)
    // {
    //     $cs->validation($em);
    //     $this->addFlash('success', '✅ Votre commande à bien été valider !');
    //     $this->addFlash('info', '💬 Vous trouverez le détails de votre commande depuis votre page profil.');
    //     return $this->redirectToRoute('app_cart');
    // }

    #[Route('/cart/order', name: 'cart_order')]
    public function validation(CartService $cs, EntityManagerInterface $em, RequestStack $rs)
    {
        $cartWithData = $cs->getCartWithData();
        $total = $cs->getTotal();
        
        for($i = 0; $i < count($cartWithData ); $i++) 
        {
            if($cartWithData[$i]['product']->getStock() < $cartWithData[$i]['quantity'])
            {
                $nomProduit = $cartWithData[$i]['product']->getBrand() . ' ' . $cartWithData[$i]['product']->getTitle();
                if($cartWithData[$i]['product']->getStock() > 0)
                {
                    $session = $rs->getSession();
                    $cart = $session->get('cart', []);
                    // dd($cart);
                    $cart[$cartWithData[$i]['product']->getId()] = $cartWithData[$i]['product']->getStock();

                    // dd($cart);
                    // dd($cart[$cartWithData[$i]['product']->getId()]);
                    // $cartWithData[$i]['quantity'] = $cartWithData[$i]['product']->getStock();
                    // $cartWithData[$i]['quantity'] = $cartWithData[$i]['product']->getStock();
                    // dd($cart);
                    $session->set('cart', $cart);
                    // dd($session->get('cart'));
                    $this->addFlash('info', "💬 La qantité du produit : $nomProduit à été réduite car notre stock est insuffisant, vérifier votre panier à nouveau svp.");
                }
                else
                {
                    // dd($rs->getSession()->get('cart'));
                    $cs->remove($cartWithData[$i]['product']->getId());
                    // dd($rs->getSession()->get('cart'));
                    $this->addFlash('info', "💬 Le produit : $nomProduit à été retirer de votre panier car ce produit est en rupture de stock, vérifier votre panier à nouveau svp.");
                }
                $erreur = true;
            }    
        }
        if(isset($erreur))
        {
            // dd($rs->getSession()->get('cart'));
            return $this->redirectToRoute('app_cart');
        }
        else
        {
            // dd($rs->getSession()->get('cart'));
            // dd($cartWithData);
            for($i = 0; $i < count($cartWithData ); $i++) 
            {
                $commande = new Order();
                $commande->addProduct($cartWithData[$i]['product']);
                $commande->setQuantity($cartWithData[$i]['quantity']);
                $commande->setTotal($cartWithData[$i]['product']->getPrice() * $cartWithData[$i]['quantity']);
                $commande->setStatut('en cours de traitement');
                $commande->setUser($cs->user->getUser());
                $commande->setCreatedAt(new \DateTime());
                $product = $cartWithData[$i]['product'];
                // $product->setStock();
                // dd($commande);
                $em->persist($commande);
                //$em->persist($product);
            }
            $em->flush();
            $cs->empty();
            $this->addFlash('success', '✅ Votre commande à bien été valider !');
            $this->addFlash('info', '💬 Vous trouverez le détails de votre commande depuis votre page profil.');
        }
        return $this->redirectToRoute('app_cart');
        // dd($commande);
    }
}
