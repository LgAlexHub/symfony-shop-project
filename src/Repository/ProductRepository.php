<?php

namespace App\Repository;

use App\Entity\Product;
use App\Repository\Trait\RepositoryToolTrait;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

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

    const alias = 'akaProduct';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function productsByQuery(string $query) : mixed {
        return $this->createQueryBuilder('product')
            ->where('product.name like :query')
            ->orderBy('product.name','ASC')
            ->setParameter('query', "%$query%")
            ->getQuery()
            ->getResult();
    }

    private function buildProductFilterPaginateQuery(string $userSearchQuery) : QueryBuilder {
        $queryBuilder = $this->createQueryBuilder(self::alias)
            ->innerJoin(sprintf("%s.productReferences", self::alias), "productReference")
            ->innerJoin(sprintf("%s.category", self::alias), "productCategory")
            ->addSelect(['productReference', 'productCategory']);
        
        if(!empty($userSearchQuery)){
            $this->filterByUserSearch($queryBuilder, $userSearchQuery);
        }

        return $queryBuilder;
    }

    private function buildProductCountQuery(string $userSearchQuery) : QueryBuilder {
        $queryBuilder = $this->createQueryBuilder(self::alias)
            ->innerJoin(sprintf("%s.productReferences", self::alias), "productReference")
            ->select(sprintf("COUNT(DISTINCT %s.id)", self::alias));
        
        if(!empty($userSearchQuery)){
            $this->filterByUserSearch($queryBuilder, $userSearchQuery);
        }

        return $queryBuilder;
    }

    /**
     * Note : Pas besoin de return le query builder car en php les objets sont passé en réf
     * Filters the query based on the user's search terms. It searches for each term in the client's last name 
     * and email columns.
     * @param QueryBuilder $queryBuilder
     * @param string $userSearchQuery
     * @return void
     */
    private function filterByUserSearch(QueryBuilder $queryBuilder, string $userSearchQuery) : void {
        $userSearchQueryTerms = explode(" ", $userSearchQuery);
        $queryBuilder->where(sprintf("%s.name LIKE :term_%d", self::alias, 0))
            ->setParameter(sprintf("term_%d", 0), sprintf("%%%s%%", $userSearchQueryTerms[0]));
        unset($userSearchQueryTerms[0]);
        foreach($userSearchQueryTerms as $termKey => $termValue){
            $queryBuilder->orWhere(sprintf("%s.name LIKE :term_%d", self::alias, $termKey))
                ->setParameter(sprintf("term_%d", 0), sprintf("%%%s%%", $termValue));
        }
    }

    private function calculateOrderMaxPage(QueryBuilder $queryBuilder, int $perPage) : object {
        $orderCount = $queryBuilder->getQuery()
            ->getSingleScalarResult();
        return (object)[
            'count'   => $orderCount,
            'maxPage' => (int) ceil($orderCount / $perPage)
        ];
    }

    public function paginateFilterProducts(int $page = 1, int $perPage = 12, $category = null, $userSearchQuery = null) : mixed {
        $productCountQueryBuilder = $this->buildProductCountQuery($userSearchQuery);
        $productFilterQueryBuilder = $this->buildProductFilterPaginateQuery($userSearchQuery);
        $productCountAndMaxPage = $this->calculateOrderMaxPage($productCountQueryBuilder, $perPage);

        $productFilterQueryBuilder->setFirstResult($perPage * ($page - 1))
            ->setMaxResults($perPage);

        return (object)[
            'results' => (new Paginator($productFilterQueryBuilder)),
            'nbResult'  => $productCountAndMaxPage->count,
            'maxPage'   => $productCountAndMaxPage->maxPage,
            'page'      => $page,
        ];
    }
}
