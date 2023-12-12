<?php 

namespace App\Repository\Trait;


Trait RepositoryToolTrait {
    public function findBySlug(mixed $value){
        return $this->createQueryBuilder("entity")
            ->where("entity.slug LIKE :slug")
            ->setParameter("slug", "%{$value}%")
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}