<?php

namespace App\Entity;


use App\Repository\OrderProductRefRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderProductRefRepository::class)]
class OrderProductRef
{
    #[ORM\Column(type: Types::INTEGER, options: ["default" => 1])]
    private int $quantity;

    #[ORM\ManyToOne(targetEntity:Order::class)]
    #[ORM\JoinColumn(nullable:false)]
    #[ORM\Id]
    private Order $order;
    
    #[ORM\ManyToOne(targetEntity:ProductReference::class)]
    #[ORM\Id]
    private ProductReference $item;

    /**
     * Get the value of quantity
     */ 
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set the value of quantity
     *
     * @return  self
     */ 
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get the value of order
     */ 
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set the value of order
     *
     * @return  self
     */ 
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get the value of item
     */ 
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set the value of item
     *
     * @return  self
     */ 
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }
}
