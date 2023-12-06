<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableWithIdTrait;
use App\Repository\ProductReferenceRepository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductReferenceRepository::class)]
class ProductReference
{

    use TimestampableWithIdTrait;

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

    #[ORM\Column(length: 1024, nullable: true, type: Types::STRING)]
    private ?string $picPath = null;

    public function __construct(){
    }


    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWeightType(): ?string
    {
        return $this->weightType;
    }

    public function setWeightType(?string $weightType): static
    {
        $this->weightType = $weightType;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getPicPath(): ?string
    {
        return $this->picPath;
    }

    public function setPicPath(?string $picPath): static
    {
        $this->picPath = $picPath;

        return $this;
    }
}
