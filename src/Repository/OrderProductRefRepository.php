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

   /**
    * Undocumented function
    *
    * @param integer $orderId
    * @return mixed
    */
    public function findProductWithRelatedInOrder(int $orderId) : mixed {
        return $this->createQueryBuilder("akaOrderProductRef")
            ->innerJoin("akaOrderProductRef.item", "akaProductRef")
            ->innerJoin("akaOrderProductRef.order", "akaOrder")
            ->innerJoin("akaProductRef.product", "akaProduct")
            ->addSelect(["akaProductRef", "akaProduct"])
            ->where("akaOrder.id = :orderId")
            ->setParameter("orderId", $orderId)
            ->getQuery()
            ->getResult();
    }
}
