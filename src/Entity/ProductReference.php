<?php

namespace App\Entity;

use App\Entity\Trait\SluggableTrait;
use App\Entity\Trait\TimestampableWithIdTrait;
use App\Repository\ProductReferenceRepository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductReferenceRepository::class)]
/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * This class represent a reference of product, this class can be bind in Product Class to describe it's price and weight.
 * This is class use Lifecycle callback throught traits.
 */
class ProductReference
{
    use TimestampableWithIdTrait;
    use SluggableTrait;

    #[Assert\NotNull]
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $price = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $weight = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $weightType = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'productReferences')]
    private ?Product $product = null;

    /**
     * Price getter
     *
     * @return integer|null
     */
    public function getPrice(): ?int
    {
        return $this->price;
    }

    /**
     * Price setter
     *
     * @param integer $price
     * @return static
     */
    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Weight getter
     *
     * @return integer|null
     */
    public function getWeight(): ?int
    {
        return $this->weight;
    }

    /**
     * Weight setter
     *
     * @param integer|null $weight
     * @return static
     */
    public function setWeight(?int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Weight type getter , represent the unit of product reference weight 
     * example : g, mg , L, mL
     *
     * @return string|null
     */
    public function getWeightType(): ?string
    {
        return $this->weightType;
    }

    /**
     * Weight type setter
     *
     * @param string|null $weightType
     * @return static
     */
    public function setWeightType(?string $weightType): static
    {
        $this->weightType = $weightType;

        return $this;
    }

    /**
     * Retrieve the parent product associated with this reference.
     *
     * This method is useful when you need to access the parent product of a reference.
     *
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * Bind product
     *
     * @param Product|null $product
     * @return static
     */
    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }


    /**
     * Method use by SluggableTrait to get valid source to slug
     *
     * @return string
     */
    protected function getValueToSlugify(): string {
        return "{$this->product->getName()} {$this->weight}{$this->weightType}";
    }
}
