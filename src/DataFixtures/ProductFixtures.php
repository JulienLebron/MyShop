<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 10; $i++)
        {
            $product = new Product();
            $product->setTitle("Titre du produit n°$i")
                    ->setContent("<p>Contenu du produit n°$i</p>")
                    ->setColor("red")
                    ->setSize("M")
                    ->setCollection("Homme")
                    ->setImage("https://assets.laboutiqueofficielle.com/w_1100,q_auto,f_auto/media/products/2021/01/18/adidas_247739_FZ2246_20221212T083828_06.jpg")
                    ->setPrice(100)
                    ->setStock(20)
                    ->setCreatedAt(new \DateTime());
            $manager->persist($product);
        }
        $manager->flush();

    }
}
