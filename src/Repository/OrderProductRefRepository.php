<?php

namespace App\Repository;

use App\Entity\OrderProductRef;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderProductRef>
 *
 * @method OrderProductRef|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderProductRef|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderProductRef[]    findAll()
 * @method OrderProductRef[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderProductRefRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderProductRef::class);
    }

   /**
    * @return OrderProductRef[] Returns an array of OrderProductRef objects
    */
   public function findByExampleField($value): array
   {
       return $this->createQueryBuilder('o')
           ->andWhere('o.exampleField = :val')
           ->setParameter('val', $value)
           ->orderBy('o.id', 'ASC')
           ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }

   /**
    * This method retreive try to retreive targeted OrderProductRef in an targeted order
    *
    * @param integer $orderId id of targeted order
    * @param integer $productRefId id of product reference
    * @return OrderProductRef|null
    */
   public function findProductInOrder(int $orderId, int $productRefId) : ?OrderProductRef{
        return $this->createQueryBuilder('opr')
            ->innerJoin("opr.order", 'o')
            ->innerJoin("opr.item", "i")
            ->addSelect(["o", "i"])
            ->where('o.id = :orderId')
            ->andWhere('i.id = :productRefId')
            ->setParameter('orderId', $orderId)
            ->setParameter('productRefId', $productRefId)
            ->getQuery()
            ->getOneOrNullResult();
   }

//    public function findOneBySomeField($value): ?OrderProductRef
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
