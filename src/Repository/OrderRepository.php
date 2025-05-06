<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
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

    /**
     * Find pending order by user and product
     */
    public function findPendingByUserAndProduct(User $user, Product $product): ?Order
    {
        return $this->createQueryBuilder('o')
            ->join('o.orderItems', 'oi')
            ->where('o.user = :user')
            ->andWhere('oi.product = :product')
            ->andWhere('o.status = :status')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * Find or create pending order for user
     */
    public function findOrCreatePendingOrder(User $user): Order
    {
        $pendingOrder = $this->createQueryBuilder('o')
            ->where('o.user = :user')
            ->andWhere('o.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getOneOrNullResult();
            
        if (!$pendingOrder) {
            $pendingOrder = new Order();
            $pendingOrder->setUser($user);
            $pendingOrder->setOrderDate(new \DateTime());
            $pendingOrder->setStatus('pending');
        }
        
        return $pendingOrder;
    }
}