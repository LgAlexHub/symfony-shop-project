<?php

namespace App\Repository;

use App\Entity\Order;
use App\Repository\Trait\RepositoryToolTrait;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


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

    const alias = 'akaOrder';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Builds a query to paginate orders with the total order amount. 
     * It constructs a query builder object with inner joins to fetch associated items
     * and their prices. Additionally, it selects the sum of the product of quantity and price
     * as totalOrderAmount.
     *
     * @param string $userSearchQuery
     * @param array $orderBy
     * @return QueryBuilder
     */
    private function buildOrderFilterPaginateQueryWithTotalAmount(string $userSearchQuery, array $orderBy) : QueryBuilder {
        $queryBuilder = $this->createQueryBuilder(self::alias)
            ->select(self::alias)
            ->innerJoin(sprintf("%s.items", self::alias), "opr")
            ->innerJoin("opr.item", "pr")
            ->addSelect('SUM(opr.quantity * pr.price) as totalOrderAmount')
            ->groupBy(sprintf("%s.id", self::alias));
            
        if(!empty($userSearchQuery)){
           $this->filterByUserSearch($queryBuilder, $userSearchQuery);
        }

        if(!empty($orderBy)){
           $this->applyOrderBy($queryBuilder, $orderBy);
        }
        return $queryBuilder;
    }

    /**
     * Build a count query for order pagination
     *
     * @param string $userSearchQuery
     * @return QueryBuilder
     */
    private function buildOrderCountQuery(string $userSearchQuery) : QueryBuilder {
        $queryBuilder = $this->createQueryBuilder(self::alias)
            ->select(sprintf('COUNT(DISTINCT %s.id)', self::alias));
        if (!empty($userSearchQuery)){
            $queryBuilder = $this->filterByUserSearch($queryBuilder, $userSearchQuery);   
        }
        return $queryBuilder;
    }

    /**
     * Applies the specified order of sorting to the query builder based on the given array of column names and sorting
     * directions. If the column name is "totalPrice", it adds sorting for "totalOrderAmount", which is an aggregate.
     *
     * @param QueryBuilder $queryBuilder
     * @param array $orderBy
     * @return void
     */
    private function applyOrderBy(QueryBuilder $queryBuilder, array $orderBy) : void {
        foreach ($orderBy as $columnName => $orderValue) {
            if ($columnName === "totalPrice"){
                $queryBuilder->addOrderBy("totalOrderAmount", $orderValue);
                continue;
            }
            $queryBuilder->addOrderBy(sprintf("%s.%s", self::alias, $columnName), $orderValue);
        }
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
        $queryBuilder->where(sprintf("%s.clientLastName LIKE :term_%d", self::alias, 0))
            ->orWhere(sprintf("%s.email LIKE :term_%d", self::alias, 0))
            ->setParameter(sprintf("term_%d", 0), sprintf("%%%s%%", $userSearchQueryTerms[0]));
        unset($userSearchQueryTerms[0]);
        foreach($userSearchQueryTerms as $termKey => $termValue){
            $queryBuilder->orWhere(sprintf("%s.clientLastName LIKE :term_%d", self::alias, $termKey))
                ->orWhere(sprintf("%s.email LIKE :term_%d", self::alias, $termKey))
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

   /**
    * This method return an object that contain paginate orders and data to control pagination
    *
    * @param integer $page
    * @param integer $perPage
    * @param string $userSearchQuery
    * @param array $orderBy
    * @return object {results : mixed, nbResults : int, maxPage: int, page: int}
    */
    public function paginateFilteredOrders(int $page = 1, int $perPage = 12, string $userSearchQuery = '', array $orderBy = []) : object {
        $orderCountQueryBuilder = $this->buildOrderCountQuery($userSearchQuery);
        $orderFilterQueryBuilder = $this->buildOrderFilterPaginateQueryWithTotalAmount($userSearchQuery, $orderBy);
        $orderCountAndMaxPage = $this->calculateOrderMaxPage($orderCountQueryBuilder, $perPage);

        $orderFilterQueryBuilder->setFirstResult($perPage * ($page - 1))
            ->setMaxResults($perPage);

        return (object)[
            'results' => (new Paginator($orderFilterQueryBuilder)),
            'nbResult'  => $orderCountAndMaxPage->count,
            'maxPage'   => $orderCountAndMaxPage->maxPage,
            'page'      => $page,
        ];
    }
}
