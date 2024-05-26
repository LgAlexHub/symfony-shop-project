<?php

namespace App\Entity;

use App\Entity\Trait as HelperTrait;
use App\Repository\ProductReferenceRepository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
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
    use HelperTrait\TimestampableWithIdTrait;
    use HelperTrait\SluggableTrait;
    use HelperTrait\DeletableTrait;

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
    
    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'products', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true, type: Types::STRING)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true, type: Types::INTEGER)]
    private ?int $imageSize = null;

    /**
     * Price getter
     *
     * @return integer|null
     */
    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function getFormatedPrice(): ?float {
        return $this->getPrice()/100;
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
    public function getValueToSlugify(): string {
        return "{$this->product->getName()} {$this->weight}{$this->weightType}";
    }


    /**
     * return a string with weight and weight type of a reference 
     *
     * @return string
     */
    public function toString() : string {
        return "{$this->getWeight()} {$this->getWeightType()}";
    }


      /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * Getter for a ImageFile attribute
     *
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * Setter for a imageName attribute
     *
     * @param string|null $imageName
     * @return void
     */
    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    /**
     * Getter for a imageName attribute
     *
     * @return string|null
     */
    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    /**
     * Setter for a imageSize attribute
     *
     * @param integer|null $imageSize
     * @return void
     */
    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    /**
     * Getter for a imageSize attribute
     *
     * @return integer|null
     */
    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    /**
     * Virtual property , retreive image path
     *
     * @return string
     */
    public function getImageUrl() : string {
        return sprintf("/images/products/%s", $this->imageName ?? '');
    }
}
