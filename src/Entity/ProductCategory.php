<?php

namespace App\Entity;

use App\Entity\Trait\SluggableTrait;
use App\Repository\ProductCategoryRepository;
use App\Entity\Trait\TimestampableWithIdTrait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * This class represent a category of product, this class can be bind in Product Class to describe it's category.
 * This is class use Lifecycle callback throught traits.
 */
class ProductCategory
{
    use TimestampableWithIdTrait;
    use SluggableTrait;

    #[ORM\Column(length: 255, nullable:false, type: Types::STRING)]
    private ?string $label = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class)]
    private Collection $products;
    
    /**
     * TODO : Check le lazy loading, j'ai pas envie de charger les produits à chaque fois
     * que je veux une catégorie
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * Category label getter
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Category label setter
     *
     * @param string $label
     * @return static
     */
    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * This method allow to set bind product to category
     *
     * @param Product $product
     * @return static
     */
    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCategory($this);
        }

        return $this;
    }

    /**
     * This method allow a category to unbind a product
     *
     * @param Product $product
     * @return static
     */
    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    
    /**
     * Method use by SluggableTrait to get valid source to slug
     *
     * @return string
     */
    protected function getValueToSlugify(): string {
        return $this->label;
    }
}
