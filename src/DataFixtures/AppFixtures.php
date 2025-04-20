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

        $categories = [];

        // 5 test categories
        for ($i = 0; $i < 5; $i++) {
            $category = new Category();
            $category->setName($faker->word());
            $manager->persist($category);
            $categories[] = $category;
        }


        // 50 test products
        for ($i = 0; $i < 50; $i++) {
            $product = new Product();
            $product->setName($faker->word())
                    ->setDescription($faker->sentence(10))
                    ->setPrice($faker->randomFloat(2, 5, 100))
                    ->setStock($faker->numberBetween(1, 100))
                    ->setImage('https://via.placeholder.com/300x200')
                    ->setCategory($faker->randomElement($categories));
            $manager->persist($product);
        }
    
        $manager->flush();
    }
    
}
