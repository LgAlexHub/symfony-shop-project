<?php 

namespace App\Service;

use Exception;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class EnhancedEntityJsonSerializer {
    private ?Serializer $serializer;
    private mixed $objectToSerialize;
    private ?array $attributes;
    private ?array $options;
    private static $optionKeysToCheck = [
        AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER
    ];

    public function __construct()
    {
        $this->serializer = new Serializer(
            array(new ObjectNormalizer()),
            array('json' => new JsonEncoder())
        );
    }

    /**
     * Get the value of attributes
     */ 
    public function getAttributes() : ?array
    {
        return $this->attributes;
    }

    /**
     * Set the value of attributes
     *
     * @return  self
     */ 
    public function setAttributes(array $attributes) : self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the value of options
     */ 
    public function getOptions() : ?array
    {
        return $this->options;
    }

    /**
     * Set the value of options
     *
     * @return  self
     */ 
    public function setOptions(array $options) : self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the value of objectToSerialize
     */ 
    public function getObjectToSerialize()
    {
        return $this->objectToSerialize;
    }

    /**
     * Set the value of objectToSerialize
     *
     * @return  self
     */ 
    public function setObjectToSerialize($objectToSerialize)
    {
        $this->objectToSerialize = $objectToSerialize;

        return $this;
    }

    /**
     * This method will call Symfony Serializer after settings options if it exist
     * @throws Exception trigger when objectToSerializer is null
     * @return string json string of entity(.ies)
     */
    public function serialize() : string {
        $finalOptions = array(AbstractNormalizer::ATTRIBUTES => $this->getAttributes());
        if (is_null($this->getObjectToSerialize()))
            throw new Exception("objectToSerialize must be not null to start serializing", "-1");
        if (is_array($this->getOptions()) && !empty($this->getOptions())){
            foreach(self::$optionKeysToCheck as $key){
                if(array_key_exists($key, $this->getOptions())){
                    $finalOptions[$key] = $this->getOptions()[$key];
                }
            }
        }

        return $this->serializer->serialize($this->getObjectToSerialize(), 'json', $finalOptions);
    }
}