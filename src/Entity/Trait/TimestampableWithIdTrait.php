<?php 

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

use Symfony\Component\Validator\Constraints as Assert;

trait TimestampableWithIdTrait {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\DateTime]
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, updatable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $createdAt;

    #[Assert\DateTime]

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt;

    #[ORM\PrePersist]
    public function prePersist() : void {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate() : void {
        $this->updatedAt = new \DateTimeImmutable();
    }

     /**
     * Get the value of id
     *
     * @return integer|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of createdAt
     */ 
    public function getCreatedAt() : ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */ 
    public function setCreatedAt(?\DateTimeImmutable $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of updatedAt
     */ 
    public function getUpdatedAt() : ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt
     *
     * @return  self
     */ 
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}