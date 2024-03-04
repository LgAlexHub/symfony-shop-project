<?php

namespace App\Entity;

use App\Entity\Trait as HelperTrait;
use App\Repository\OrderRepository;

use Symfony\Component\Uid\Uuid;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`order`')]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
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

    #[ORM\Column(length: 255)]
    private ?string $clientFirstName = null;

    #[ORM\Column(length: 255)]
    private ?string $clientLastName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\OneToMany(targetEntity:OrderProductRef::class, mappedBy: "order")]
    private Collection $items;

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue]
    private Uuid $uuid;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    private bool $isValid;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * Method use to normalize uuid object into string
     *
     * @return string
     */
    public function getSerializeUuid(): string
    {
        return $this->uuid->toRfc4122();
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

    /**
     * Get the value of uuid
     */ 
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set the value of uuid
     *
     * @return  self
     */ 
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get the value of isValid
     */ 
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * Set the value of isValid
     *
     * @return  self
     */ 
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Return a collection of OrderProductRef
     *
     * @return Collection
     */
    public function getItems() : Collection
    {
        return $this->items;
    }

    public function setItems (Collection $collection){
        $this->items = $collection;
    }

    /**
     * Add an instance of OrderProductRef to Order
     *
     * @param OrderProductRef $item
     * @return static
     */
    public function addItem(OrderProductRef $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }
        return $this;
    }

    /**
     * Remove an item from Order basket
     *
     * @param OrderProductRef $item
     * @return static
     */
    public function removeItem(OrderProductRef $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }
}
