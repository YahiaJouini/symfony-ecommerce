<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findOrdersByUser($user)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.orderDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndProduct($user, $product)
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.products', 'p')
            ->where('o.user = :user')
            ->andWhere('p.id = :productId')
            ->setParameter('user', $user)
            ->setParameter('productId', $product->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
