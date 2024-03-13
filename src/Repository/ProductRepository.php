<?php

namespace App\Repository;

use App\Entity\Product;
use App\Repository\Trait\RepositoryToolTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    use RepositoryToolTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function productsByQuery(string $query){
        return $this->createQueryBuilder('product')
            ->where('product.name like :query')
            ->orderBy('product.name','ASC')
            ->setParameter('query', "%$query%")
            ->getQuery()
            ->getResult();
    }

    public function productOrderByNamePaginate(int $page = 1, int $perPage = 12, $category = null, $query = null){
        $productCountQuery = $this->createQueryBuilder('product')
            ->innerJoin('product.productReferences', 'prodRef');

        if (!is_null($category)){
            $productCountQuery->innerJoin('product.category', 'prodCat')
                ->addSelect('prodCat')
                ->where('prodCat.label = :cat')
                ->setParameter("cat", $category);
        }

        // WARNING : Peut-être un overextend de regarder les slugs des productsRefs quand on peut direct checker le product slug
        if (!is_null($query)){
            $productCountQuery
                ->where('LOWER(prodRef.slug) LIKE :query')
                ->setParameter('query', '%'.$query.'%');
        }

        $productCountQuery = $productCountQuery
            ->select('count(DISTINCT product.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $maxPage = ceil($productCountQuery/$perPage);
        // if ($page > $maxPage){
        //     $page = $maxPage;
        // }
        $productQuery = $this->createQueryBuilder('product')
            ->innerJoin('product.productReferences', 'prodRef');
           
        if (!is_null($category)){
            $productQuery->innerJoin('product.category', 'prodCat')
                ->addSelect('prodCat')
                ->where('prodCat.label = :cat')
                ->setParameter("cat", $category);
        }

        if (!is_null($query)){
            $productQuery
                ->where('LOWER(prodRef.slug) LIKE :query')
                ->setParameter('query', '%'.$query.'%');
        }

        $productQuery =  $productQuery->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->orderBy('product.name', 'ASC')
            ->getQuery();


        return (object)[
            'productPaginator' => new Paginator($productQuery),
            'maxPage' => (int)$maxPage,
            'page' => $page, 
            'nbResult' => $productCountQuery
        ];
    }


//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
