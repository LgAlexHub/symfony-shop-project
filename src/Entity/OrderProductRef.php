<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Repository\OrderProductRefRepository;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: OrderProductRefRepository::class)]
/**
 * @author Aléki <alexlegras@hotmail.com>
 * @access public
 * @version 1
 * 
 * This class represent the many to many relationship between an order and productRef table.
 * One instance represent a product link to an order with pivot quantity attribute.
 * This is class use Lifecycle callback throught traits.
 * 
 */
class OrderProductRef 
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER, options: ["default" => 1])]
    private int $quantity;

    #[ORM\JoinColumn(nullable:false)]
    #[ORM\ManyToOne(targetEntity:Order::class, inversedBy: 'items')]
    private Order $order;
    
    #[ORM\JoinColumn(nullable:false)]
    #[ORM\ManyToOne(targetEntity:ProductReference::class)]
    private ProductReference $item;

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
