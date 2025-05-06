<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        $categories = [
            ['name' => 'Accessoires de mode', 'image' => 'https://img.freepik.com/photos-gratuite/kit-carriere-modele-nature-morte_23-2150229753.jpg?t=st=1745395999~exp=1745399599~hmac=74895904ddf2cd8e40b0f36d1807389b8c3df49b3aa9ee586b8309595d552af8&w=1380'],
            ['name' => 'Objets de décoration', 'image' => 'https://images.pexels.com/photos/667838/pexels-photo-667838.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'],
            ['name' => 'Textile & Tissus', 'image' => 'https://www.institutderelooking.com/images/hector-j-rivas-Nh6NsnqYVsI-unsplash.jpg'],
            ['name' => 'Cosmétiques naturels', 'image' => 'https://media.istockphoto.com/id/1320934166/fr/photo/produits-cosm%C3%A9tiques-de-soins-de-la-peau-sur-feuilles-vertes.jpg?s=612x612&w=0&k=20&c=I151m3xHJOJwznLDf4B157c0cpaqZwXQm9QMeRGyJEE='],
            ['name' => 'Papeterie artisanale', 'image' => 'https://www.shutterstock.com/image-photo/arts-craft-supplies-corrugated-color-600nw-351226619.jpg'],
            ['name' => 'Cuisine & Art de la table', 'image' => 'https://media.istockphoto.com/id/1187666943/fr/photo/en-attente-dinvit%C3%A9-ensemble-d%C3%A9l%C3%A9gance-de-table.jpg?s=612x612&w=0&k=20&c=Iaa2zptlrTWeOL-_cPnRH0_fKjUaLKWJTKyhQgnoNSk='],
        ];
        
        
        $categoryEntities = [];
        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['name'])
                     ->setImage($categoryData['image']);
            $manager->persist($category);
            $categoryEntities[] = $category;
        }        

        // 50 test products
        $showHomeCount = 0; 
        for ($i = 0; $i < 50; $i++) {
            $product = new Product();
            
            if ($showHomeCount < 6 && $faker->boolean(50)) {
                $product->setShowHome(true);
                $showHomeCount++;
            } else {
                $product->setShowHome(false);
            }
        
            $product->setName(ucfirst($faker->words(3, true)))
                    ->setDescription($faker->paragraph(3))
                    ->setPrice($faker->randomFloat(2, 5, 100))
                    ->setStock($faker->numberBetween(10, 100))
                    ->setImage("https://picsum.photos/700/800?random=" . random_int(1, 10000))
                    ->setCategory($faker->randomElement($categoryEntities));
        
            $manager->persist($product);
        }
        
        $manager->flush();
    }        
}