<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findAllByCategory(?int $categoryId = null)
    {
        if ($categoryId) {
            return $this->findBy(['category' => $categoryId]);
        }

        return $this->findAll();
    }

    public function findProductById(int $id): ?Product
    {
        return $this->find($id);
    }

    public function searchProducts(string $searchTerm = '', ?int $categoryId = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        if ($searchTerm) {
            $qb->andWhere('LOWER(p.name) LIKE LOWER(:searchTerm)')
               ->setParameter('searchTerm', '%'.$searchTerm.'%');
        }

        if ($categoryId) {
            $qb->andWhere('p.category = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        return $qb;
    }
}