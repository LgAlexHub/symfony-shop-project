<?php

namespace App\Repository;

use App\Entity\Order;
use App\Repository\Trait\RepositoryToolTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    use RepositoryToolTrait;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByUuid(mixed $value){
        return $this->findOneBy(['uuid' => $value]);
    }


    public function findByUuidWithRelated(string $uuid){
        return $this->createQueryBuilder("o")
            ->leftJoin('o.items', 'items')
            ->addSelect("items")
            ->where("o.uuid = :uuid")
            ->setParameter("uuid", $uuid, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllRelated(){
        return $this->createQueryBuilder("o")
            ->innerJoin('o.items', 'items')
            ->addSelect("items")
            ->getQuery()
            ->getResult();
    }

    public function orderPagination(int $page = 1, int $perPage = 12, string $queryString = null, string $orderBy = null, bool $asc = true) {
        $orderQuery = $this->createQueryBuilder('o')
            ->innerJoin('o.items', 'orderItems')
            ->addSelect('orderItems');
        
        $orderCountQuery = $this->createQueryBuilder('orders')
            ->innerJoin('orders.items', 'items')
            ->addSelect('items')
            ->select('COUNT(DISTINCT orders.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        $maxPage = ceil($orderCountQuery/$perPage);

        if (!is_null($queryString)){
            $terms = explode(" ", $queryString);
            // First iteration outside loop to set one Where clause , then unset variable to use the foreach loop as usual
            $orderQuery
                ->where("o.clientFirstName LIKE :term_0")
                ->orWhere("o.clientLastName LIKE :term_0")
                ->orWhere("o.email LIKE :term_0")
                ->setParameter("term_0", $terms[0]);
            unset($terms[0]);

            foreach ($terms as $index => $term) {
                $orderQuery
                    ->orWhere("o.clientFirstName LIKE :term_".$index)
                    ->orWhere("o.clientLastName LIKE :term_".$index)
                    ->orWhere("o.email LIKE :term_".$index)
                    ->setParameter("term_".$index, $term);
            }
        }

        if (!is_null($orderBy)){
            $orderQuery->orderBy("o.".$orderBy, $asc ? 'ASC' : 'DESC');
        }else{
            $orderQuery->orderBy("o.createdAt", $asc ? 'ASC' : 'DESC');
        }

        $orderQuery->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery();

        return (object)[
            'paginator' => new Paginator($orderQuery),
            'maxPage'   => (int) $maxPage,
            'page'      => $page,
            'nbResult'  => $orderCountQuery
        ];
    }

//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
