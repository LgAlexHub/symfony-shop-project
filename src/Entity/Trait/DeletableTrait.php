<?php 

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;


/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * This trait add to new property to binding class
 * DateTimeImmutable createdAt
 * DateTimeImmutable updetadAt
 * Add methods to handle 2 news properties
 */
trait DeletableTrait {

    #[ORM\Column(name: 'deleted_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt;

    /**
     * Restore entity visibility by setting it's deletedAt attribute to 
     */ 
    public function restore() : void 
    {
         $this->deletedAt = null;
    }

    /**
     * Soft delete entity by setting it's deletedAt attribute to now timestamps
     *
     */ 
    public function delete() : void
    {
        $this->deletedAt = new \DateTimeImmutable();

    }

    /**
     * Get deletedAt attribute
     *
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt() : ?\DateTimeImmutable{
        return $this->deletedAt;
    }

    /**
     * 
     *
     * @return void
     */
    public function toggleDelete() : void {
        is_null($this->deletedAt) ? $this->delete() : $this->restore();
    }

}