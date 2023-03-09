<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    // Administration des produits

    #[Route('/admin/produits', name: 'admin_produits')]
    public function adminVehicules(ProductRepository $repo, EntityManagerInterface $em)
    {
        // récupération des noms des colonnes SQL
        $colonnes = $em->getClassMetadata(Product::class)->getFieldNames();
        // récupérations de tout les articles
        $produits = $repo->findAll();

        return $this->render('admin/admin_produits.html.twig', [
            'produits' => $produits,
            'colonnes' => $colonnes
        ]);
    }

    #[Route('/admin/produit/new', name: 'admin_new_produit')]
    #[Route('/admin/{id}/edit-produit', name: 'admin_edit_produit')]
    public function editProduit(Request $request, EntityManagerInterface $manager, Product $produit = null)
    {
        // la classe Request contient les données véhiculées par les superglobales ($_POST, $_GET ...)
        // $produit = new Product; // je crée un objet Vehicule vide prêt à être rempli
        if($produit == null)
        {
            $produit = new Product;
            $produit->setCreatedAt(new \DateTime()); // ajout de la date à l'insertion de l'produit
        }
        $form = $this->createForm(ProductType::class, $produit);
        // createForm() permet de récupérer un formulaire
        dump($request); // permet d'afficher les données de l'objet $request
        $form->handleRequest($request);
        // handleRequest() permet d'insérer les données du formulaire dans l'objet $produit
        // Pour pouvoir insérer les données en BDD, on récupère le manager et on ajoute le code d'insertion
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($produit); // prépare l'insertion de l'produit
            $manager->flush(); // on exécute la requête d'insertion 
            // cette méthode permet de nous rediriger vers la page de notre produit nouvellement crée
            $this->addFlash('success', "✅ L'action sur le produit à été réalisé avec succès !");
            return $this->redirectToRoute('admin_produits');
        }
        return $this->render('admin/form_produit.html.twig', [
            'formEdit' => $form->createView(),
            // createView() renvoie un objet représentant l'affichage du formulaire
            'editMode' => $produit->getId() !== NULL 
            // si nous sommes sur la route /new : editMode = 0
            // sinon, editMode = 1
        ]);
    }

    #[Route('/admin/{id}/delete-produit', name: 'admin_delete_produit')]
    public function deleteVehicule(Product $produit, EntityManagerInterface $manager)
    {
        $manager->remove($produit);
        $manager->flush();

        // on envoi un message d'alerte vers la vue
        $this->addFlash('success', "✅ Le produit à bien été supprimé !");

        return $this->redirectToRoute('admin_produits');
    }

     // Administration des membres

    #[Route('/admin/users', name: 'admin_users')]
    public function adminUsers(UserRepository $repo, EntityManagerInterface $em)
    {
        // récupération des noms des colonnes SQL
        $colonnes = $em->getClassMetadata(User::class)->getFieldNames();
        // récupérations de tout les articles
        $users = $repo->findAll();
 
     return $this->render('admin/admin_users.html.twig', [
            'users' => $users,
            'colonnes' => $colonnes
        ]);
    }

    #[Route('/admin/{id}/delete-user', name: 'admin_delete_user')]
    public function deleteUser(User $user, EntityManagerInterface $manager)
    {
        $manager->remove($user);
        $manager->flush();
 
        // on envoi un message d'alerte vers la vue
        $this->addFlash('success', "✅ L'utilisateur à bien été supprimé !");
 
        return $this->redirectToRoute('admin_users');
    }

         // Administration des commandes

         #[Route('/admin/commandes', name: 'admin_commandes')]
         public function adminCommandes(OrderRepository $repo, EntityManagerInterface $em)
         {
             // récupération des noms des colonnes SQL
             $colonnes = $em->getClassMetadata(Order::class)->getFieldNames();
             // récupérations de tout les articles
             $orders = $repo->findAll();
     
             return $this->render('admin/admin_commandes.html.twig', [
                 'orders' => $orders,
                 'colonnes' => $colonnes
             ]);
         }
    
    
         #[Route('/admin/{id}/delete-order', name: 'admin_delete_order')]
         public function deleteCommande(Order $order, EntityManagerInterface $manager)
         {
             $manager->remove($order);
             $manager->flush();
     
             // on envoi un message d'alerte vers la vue
             $this->addFlash('success', "✅ La commande à bien été annulé !");
     
             return $this->redirectToRoute('admin_commandes');
         }
}
