<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableWithIdTrait;
use App\Repository\ProductCategoryRepository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
class ProductCategory
{
    use TimestampableWithIdTrait;

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


    public function getLabel(): ?string
    {
        return $this->label;
    }

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

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCategory($this);
        }

        return $this;
    }

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
}
