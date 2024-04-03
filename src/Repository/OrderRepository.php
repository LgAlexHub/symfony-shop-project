<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderProductRef;
use App\Entity\ProductReference;
use App\Repository\Trait\RepositoryToolTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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

    const orderAlias = 'akaOrder';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Will build 
     *
     * @param string $userSearchQuery
     * @param array $orderBy
     * @return string
     */
    private function buildPaginateFilteredOrdersQuery(string $userSearchQuery, array $orderBy) : string 
    {
        $sqlOrderQueryString = sprintf(
            "SELECT %s.*, SUM(akaOrderProductReference.quantity * akaProductReference.price) AS totalOrderAmount FROM `order` AS %s".
                " INNER JOIN order_product_ref AS akaOrderProductReference ON akaOrderProductReference.order_id = %s.id".
                " JOIN product_reference AS akaProductReference ON akaProductReference.id = akaOrderProductReference.item_id",
            ...array_fill(0, 3, self::orderAlias)
        );

        if(!empty($userSearchQuery)){
            $sqlOrderQueryString.= " WHERE";
            $userSearchQueryTerms = explode(' ', $userSearchQuery);
            foreach ($userSearchQueryTerms as $term) {
                $sqlOrderQueryString.= sprintf(" %s.client_first_name LIKE '%%%s%%' OR %s.client_last_name LIKE '%%%s%%' OR %s.email LIKE '%%%s%%'", self::orderAlias, $term, self::orderAlias, $term, self::orderAlias, $term);
            }
        }

        $sqlOrderQueryString.= sprintf(" GROUP BY %s.id", self::orderAlias);

        if (!empty($orderBy)){
            $sqlOrderQueryString.= " ORDER BY";
            foreach ($orderBy as $key => $value) {
                $sqlOrderQueryString.= sprintf(" %s %s,", $key === "total_price" ? "totalOrderAmount" : self::orderAlias.".".$key, $value);
            }
            $sqlOrderQueryString = substr($sqlOrderQueryString, 0, -1);
        }
        return $sqlOrderQueryString;
    }

    /**
     * Undocumented function
     *
     * @param null|string $userSearchQuery
     * @return string
     */
    private function buildPaginateFilteredCountOrdersQuery(null|string $userSearchQuery) : string
    {
        $sqlOrderCountQueryString = sprintf(
            "SELECT COUNT(DISTINCT %s.id) as countOrder FROM `order` as %s".
                " INNER JOIN order_product_ref AS opr ON opr.order_id = %s.id",
            ...array_fill(0, 3, self::orderAlias)

        );
        if(!is_null($userSearchQuery)){
            $sqlOrderCountQueryString.= " WHERE";
            $userSearchQueryTerms = explode(' ', $userSearchQuery);
            foreach ($userSearchQueryTerms as $term) {
                $sqlOrderCountQueryString.= sprintf(" %s.client_first_name LIKE '%%%s%%' OR %s.client_last_name LIKE '%%%s%%' OR %s.email LIKE '%%%s%%'", self::orderAlias, $term, self::orderAlias, $term, self::orderAlias, $term);
            }
        }
        return $sqlOrderCountQueryString;
    }

    /**
     * Undocumented function
     *
     * @param string $query
     * @param integer $perPage
     * @return object
     */
    private function calculateOrderMaxPage(string $query, int $perPage) : object {
        $countResultSetMapper = new ResultSetMapping();
        $countResultSetMapper->addScalarResult("countOrder", "countOrder");
        $orderCount = $this->getEntityManager()
            ->createNativeQuery($query, $countResultSetMapper)
            ->getSingleScalarResult();
        return (object)['count' => $orderCount, 'maxPage' => (int) ceil($orderCount / $perPage)];
    }

    /**
     * Undocumented function
     *
     * @param string $sqlQuery
     * @param integer $perPage
     * @param integer $page
     * @return void
     */
    private function executePaginateOrderQuery(string $sqlQuery, int $perPage, int $page){
        $paginateSqlQuery = $sqlQuery.sprintf(" LIMIT %d OFFSET %d", $perPage, ($page - 1) * $perPage);
        // dd($paginateSqlQuery);
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Order::class, 'o');
        $rsm->addScalarResult("totalOrderAmount", "totalOrderAmount");
        $rsm->addJoinedEntityFromClassMetadata(OrderProductRef::class, 'opr', 'o', 'items', [
            'id' => 'order_product_ref_id'
        ]);
        $rsm->addJoinedEntityFromClassMetadata(ProductReference::class, 'pr', 'opr', 'item', [
            'id' => 'product_ref_id',
            'created_at' => 'product_ref_created_at',
            'updated_at' => 'product_ref_updated_at'
        ]);
        return $this->getEntityManager()->createNativeQuery($paginateSqlQuery, $rsm)->getResult();
    }

    /**
     * Undocumented function
     *
     * @param integer $page
     * @param integer $perPage
     * @param string|null $queryString
     * @param array $orderBy
     * @return object
     */
    public function paginateFilteredOrders(int $page = 1, int $perPage = 12, string $userSearchQuery = '', array $orderBy = []) : object {
        $orderCountSqlQuery = $this->buildPaginateFilteredCountOrdersQuery($userSearchQuery);
        $orderSqlQuery = $this->buildPaginateFilteredOrdersQuery($userSearchQuery, $orderBy);
        $orderCountAndMaxPage = $this->calculateOrderMaxPage($orderCountSqlQuery, $perPage);
        return (object)[
            'results' => $this->executePaginateOrderQuery($orderSqlQuery, $perPage, $page),
            'nbResult'  => $orderCountAndMaxPage->count,
            'maxPage'   => $orderCountAndMaxPage->maxPage,
            'page'      => $page,
        ];
    }
}
