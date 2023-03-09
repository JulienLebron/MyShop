<?php 


namespace App\Service;

use App\Entity\Commande;
use App\Entity\Order;
use App\Repository\ProductRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CartService
{
    private $rs;
    private $repo;
    public $user;

    public function __construct(RequestStack $rs, ProductRepository $repo, Security $user)
    {   
        // hors d'un controller, nous devons faire les injections de dépendances dans un constructeur
        $this->rs = $rs;
        $this->repo = $repo;
        $this->user = $user;
    }

    public function add($id)
    {
        // nous allons récupérer ou créer une session grâce à la classe RequestStack
        $session = $this->rs->getSession();

        // je récupère l'attribut de session 'cart' s'il existe ou un tableau vide
        $cart = $session->get('cart', []);

        // si le produit existe déjà dans mon panier, j'incrémente sa quantité
        if(!empty($cart[$id]))
        {
            $cart[$id]++;
        }
        else
        {
            $cart[$id] = 1;
            // dans mon tableau $cart, à la case $id (qui correspond à l'id d'un produit), je donne la valeur 1
        }

        // je sauvegarde l'état de mon panier en session à l'attribut de session 'cart'
        $session->set('cart', $cart);
    }

    public function remove($id)
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);

        // si le produit existe déjà dans mon panier, je le supprime du tableau $cart via unset()
        if(!empty($cart[$id]))
        {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
    }

    public function decrement($id)
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);
        if(!empty($cart[$id]))
        {
            if($cart[$id] > 1)
            {
                $cart[$id]--;
            }
            else
            {
                unset($cart[$id]);
            }
        }
        $session->set('cart', $cart);
    }

    public function empty()
    {
        $session = $this->rs->getSession();

        // solution 1 : remplacer le panier en session par un tableau vide
        $session->set('cart', []);
        $totalQuantity = 0;
        $session->set('qt', $totalQuantity);
    }

    public function getCartWithData()
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);
        $totalQuantity = 0;
        // $totalQuantity va contenir le nombre total de produits de mon panier

        // je vais crée un nouveau tableau qui contiendra des objets Product et les quantités de chaque objet
        $cartWithData = [];

        // $cartWithData[] est un tableau multidimensionnel : pour chaque id qui se trouve dans le panier, nous allons créer un nouveau tableau dans $cartWithData[] qui contiendra 2 cases : product et quantity

        foreach($cart as $id => $quantity)
        {
            // cette syntaxe signifie : je crée une nouvelle case dans $cartWithData
            $cartWithData[] = [
                'product' => $this->repo->find($id),
                'quantity' => $quantity
            ];
            $totalQuantity += $quantity;
        }

        $session->set('qt', $totalQuantity);
        // je crée l'attribut de session 'totalQuantity' ayant la valeur de $totalQuantity

        return $cartWithData;
    }

    public function getTotal()
    {
        $total = 0; // j'initialise mon total

        // pour chaque produit dans mon paniern je récupère le total par produit puis je l'joute au total final
        $cartWithData = $this->getCartWithData(); // je récupère $cartWithData via la méthode su Service getCartWithData()

        foreach($cartWithData as $item)
        {
            $totalItem = $item['product']->getPrice() * $item['quantity'];
            $total += $totalItem;
            // équivaut à $total = $total + $totalItem
        }

        return $total;
    }

    public function validation($em)
    {
        $cartWithData = $this->getCartWithData();
        $total = $this->getTotal();

        for ($i = 0; $i < count($cartWithData ); $i++) 
        {
            if($cartWithData[$i]['product']->getStock() < $cartWithData[$i]['quantity'])
            {
                if($cartWithData[$i]['product']->getStock() > 0)
                {
                    $cartWithData[$i]['quantity'] = $cartWithData[$i]['product']->getStock();
                }
                else
                {
                    $this->remove($cartWithData[$i]['product']->getId());
                }
            }
            else
            {
                $commande = new Order();
                $commande->addProduct($cartWithData[$i]['product']);
                $commande->setQuantity($cartWithData[$i]['quantity']);
                $commande->setTotal($cartWithData[$i]['product']->getPrice() * $cartWithData[$i]['quantity']);
                $commande->setStatut('en cours de traitement');
                $commande->setUser($this->user->getUser());
                $commande->setCreatedAt(new \DateTime());
                $em->persist($commande);
            }
        }   
        // dd($commande);
        $em->flush();
        $this->empty();



        // dd($commande);

    }

}