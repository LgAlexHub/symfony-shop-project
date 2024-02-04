<?php

namespace App\Entity;

use App\Entity\Trait as HelperTrait;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
/**
 * @author Aléki <alexlegras@hotmail.com>
 * @access public
 * @version 1
 * 
 * This class represent an order from a client, this entity is use to show data order to administrator.
 * This is class use Lifecycle callback throught traits.
 * 
 */
class Order
{

    use HelperTrait\TimestampableWithIdTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $clientFirstName = null;

    #[ORM\Column(length: 255)]
    private ?string $clientLastName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\OneToMany(targetEntity:OrderProductRef::class, mappedBy: "order")]
    private Collection $orderProducts;

    public function __construct()
    {
        $this->orderProducts = new ArrayCollection();
    }

     /**
     * Order ID getter
     *
     * @return string|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Order client first name getter
     *
     * @return string|null
     */
    public function getClientFirstName(): ?string
    {
        return $this->clientFirstName;
    }

    /**
     * Order client first name setter
     *
     * @return string|null
     */
    public function setClientFirstName(string $clientFirstName): static
    {
        $this->clientFirstName = $clientFirstName;

        return $this;
    }

     /**
     * Order client last name getter
     *
     * @return string|null
     */
    public function getClientLastName(): ?string
    {
        return $this->clientLastName;
    }

     /**
     * Order client last name setter
     *
     * @return string|null
     */
    public function setClientLastName(string $clientLastName): static
    {
        $this->clientLastName = $clientLastName;

        return $this;
    }

     /**
     * Order client email getter
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

     /**
     * Order client email setter
     *
     * @return string|null
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Order client comment getter
     *
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Order client comment setter
     *
     * @return string|null
     */
    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

   
}
