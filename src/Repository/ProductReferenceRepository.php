<?php

namespace App\Repository;

use App\Entity\ProductReference;
use App\Repository\Trait\RepositoryToolTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

/**
 * @extends ServiceEntityRepository<ProductReferences>
 *
 * @method ProductReferences|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductReferences|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductReferences[]    findAll()
 * @method ProductReferences[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductReferenceRepository extends ServiceEntityRepository
{
    use RepositoryToolTrait;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductReference::class);
    }

    public function refByQueryWithRelated(string $query){
        $qb = $this->createQueryBuilder('ref');
        $querySegmented = explode(" ", $query);
        if(empty($querySegmented)){
            throw new LogicException($this->getClassName().' => Empty query string');
        }

        foreach($querySegmented as $index => $segment){
            $condition = sprintf("ref.slug LIKE :segment_%d", $index);
            if ($index == 0)
                $qb->where($condition);
            else
                $qb->andWhere($condition);
            $qb->setParameter("segment_$index", "%$segment%");
        }

        $qb->innerJoin('ref.product', 'product')
            ->addSelect("product")
            ->orderBy('ref.slug','ASC');

        return $qb->getQuery()->getResult();
    }

    

    public function productsByQuery(string $query){
        return $this->createQueryBuilder('product')
            ->where('product.name like :query')
            ->orderBy('product.name','ASC')
            ->setParameter('query', "%$query%")
            ->getQuery()
            ->getResult();
    }

    
    public function findBySlug(mixed $value){
        return $this->createQueryBuilder("entity")
            ->where("entity.slug = :slug")
            ->setParameter("slug", $value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return ProductReferences[] Returns an array of ProductReferences objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProductReferences
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
