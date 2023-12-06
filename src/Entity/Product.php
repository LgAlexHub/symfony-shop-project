<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use App\Entity\Trait\TimestampableWithIdTrait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    use TimestampableWithIdTrait;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?ProductCategory
    {
        return $this->category;
    }

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
}
