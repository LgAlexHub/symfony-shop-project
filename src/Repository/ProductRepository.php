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

    /**
     * Finds products by a search query. It constructs a query to retrieve 
     * products whose names match the specified search query, ordered alphabetically.
     *
     * @param string $query
     * @return mixed
     */
    public function findProductsByQuery(string $query) : mixed {
        return $this->createQueryBuilder(self::alias)
            ->where(sprintf("%s.name LIKE :query"))
            ->orderBy(sprintf("%s.name", self::alias), "ASC")
            ->setParameter("query", "%$query%")
            ->getQuery()
            ->getResult();
    }

    /**
     * Builds a query to paginate products with filtering capabilities based on the user's search query. 
     * It constructs a query builder object with inner joins to fetch associated product
     * references and categories. Additionally, it adds selections for productReference and productCategory.
     *
     * @param string $userSearchQuery
     * @return QueryBuilder
     */
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

    /**
     * Builds a query to count products based on the user's search query. 
     * It constructs a query builder object to count the number of products that match
     * the given search criteria. The query includes inner joins to fetch associated product references.
     *
     * @param string $userSearchQuery
     * @return QueryBuilder
     */
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

    /**
     * Calculates the maximum number of pages for paginating products based on the total count of products and
     * the specified number of items per page. It executes a query to retrieve the total count of products and
     * then computes the maximum page count.
     *
     * @param QueryBuilder $queryBuilder
     * @param integer $perPage
     * @return object
     */
    private function calculateProductMaxPage(QueryBuilder $queryBuilder, int $perPage) : object {
        $orderCount = $queryBuilder->getQuery()
            ->getSingleScalarResult();
        return (object)[
            'count'   => $orderCount,
            'maxPage' => (int) ceil($orderCount / $perPage)
        ];
    }

    /**
     * Paginates and filters products based on the specified parameters.
     * It constructs a query to count products and another query to retrieve
     * paginated products with applied filters. Additionally, 
     * it calculates the maximum number of pages for pagination.
     *
     * @param integer $page
     * @param integer $perPage
     * @param [type] $category
     * @param [type] $userSearchQuery
     * @return mixed
     */
    public function paginateFilterProducts(int $page = 1, int $perPage = 12, $category = null, $userSearchQuery = null) : mixed {
        $productCountQueryBuilder = $this->buildProductCountQuery($userSearchQuery);
        $productFilterQueryBuilder = $this->buildProductFilterPaginateQuery($userSearchQuery);
        $productCountAndMaxPage = $this->calculateProductMaxPage($productCountQueryBuilder, $perPage);

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
