<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Trait as HelperTrait;
use App\Repository\ProductCategoryRepository;

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
    use HelperTrait\TimestampableWithIdTrait;
    use HelperTrait\SluggableTrait;

    #[ORM\Column(length: 255, nullable:false, type: Types::STRING)]
    private ?string $label = null;

    /**
     * TODO : Check le lazy loading, j'ai pas envie de charger les produits à chaque fois
     * que je veux une catégorie
     */
    public function __construct()
    {
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
     * Method use by SluggableTrait to get valid source to slug
     *
     * @return string
     */
    public function getValueToSlugify(): string {
        return $this->label;
    }
}
