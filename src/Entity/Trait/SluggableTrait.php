<?php 

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * This trait add one attribute to bind class, the slug, and come with few methods 
 * that handle our new property slug
 * Follow method must be implemented : getValueToSlugify
 */
trait SluggableTrait {
    
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, unique: true)]
    private ?string $slug;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateSlug(){
        $this->slug = $this->generateSlug($this->getValueToSlugify());
    }

    /**
     * This method call Symfony String slugger to generate a slug with string source
     *
     * @param string $value
     * @return string
     */
    private function generateSlug(string $value) : string {
        $slugger = new AsciiSlugger();
        return $slugger->slug($value)->lower();
    }

    /**
     * This method has to be implement to make trait work, 
     * this method should retreive the source string to make a slug 
     * @return string
     */
    abstract protected function getValueToSlugify() : string;

    /**
     * Get the value of slug
     */ 
    public function getSlug() : ?string
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     *
     * @return  self
     */ 
    public function setSlug(?string $slug)
    {
        $this->slug = $slug;

        return $this;
    }
}