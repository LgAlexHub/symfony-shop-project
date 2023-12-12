<?php

namespace App\Entity;

use App\Entity\Trait as HelperTrait;
use App\Repository\ProductRepository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
/**
 * @author Aléki <alexlegras@hotmail.com>
 * @access public
 * @version 1
 * 
 * This class represent a product, this entity is use to show data product to front user.
 * This is class use Lifecycle callback throught traits.
 * 
 */
class Product
{
    use HelperTrait\TimestampableWithIdTrait;
    use HelperTrait\SluggableTrait;

    #[ORM\Column(length: 255, nullable:false, type: Types::STRING)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?ProductCategory $category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductReference::class)]
    private Collection $productReferences;

    public function __construct()
    {
        $this->productReferences = new ArrayCollection();
    }

    /**
     * Product Name getter
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Product Name setter
     *
     * @param string $name
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Product category getter
     *
     * @return ProductCategory|null
     */
    public function getCategory(): ?ProductCategory
    {
        return $this->category;
    }

    /**
     * Product categroy setter
     *
     * @param ProductCategory|null $category
     * @return static-
     */
    public function setCategory(?ProductCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, ProductReferences>
     */
    public function getProductReferences(): Collection
    {
        return $this->productReferences;
    }

    public function addProductReference(ProductReference $productReference): static
    {
        if (!$this->productReferences->contains($productReference)) {
            $this->productReferences->add($productReference);
            $productReference->setProduct($this);
        }

        return $this;
    }

    /**
     * Remove one or many productReferences from a product
     *
     * @param mixed $productReference
     * @return static
     */
    public function removeProductReference(mixed $productReference): static
    {
        if ($this->productReferences->removeElement($productReference)) {
            // set the owning side to null (unless already changed)
            if ($productReference->getProduct() === $this) {
                $productReference->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * Method use by SluggableTrait to get valid source to slug
     *
     * @return string
     */
    public function getValueToSlugify(): string {
        return $this->name;
    }
}
