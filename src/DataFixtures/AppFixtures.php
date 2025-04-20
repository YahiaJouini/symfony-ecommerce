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
            'Accessoires',
            'Objets déco',
            'Textiles',
            'Bijoux artisanaux',
            'Mobilier fait main',
            'Artisanat de cuisine'
        ];
        
        $categoryEntities = [];
        foreach ($categories as $name) {
            $category = new Category();
            $category->setName($name)
                     ->setImage("https://picsum.photos/700/800");
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

            $product->setName($faker->word())
                    ->setDescription($faker->sentence(10))
                    ->setPrice($faker->randomFloat(2, 5, 100))
                    ->setStock($faker->numberBetween(1, 100))
                    ->setImage("https://picsum.photos/700/800")
                    ->setCategory($faker->randomElement($categoryEntities));

            $manager->persist($product);
        }

        $manager->flush();
    }
}