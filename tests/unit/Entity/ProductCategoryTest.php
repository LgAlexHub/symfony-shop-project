<?php

namespace App\Tests;

use App\Entity\ProductCategory;
use PHPUnit\Framework\TestCase;

class ProductCategoryTest extends TestCase
{
    private ?ProductCategory $testObject;
    /**
     * @override
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->testObject = new ProductCategory();
    }

    /**
     * @testdox testSetLabel
     *
     * @return void
     */
    public function testSetLabel() : void {
        $this->testObject->setLabel("jelly");
        $this->assertSame("jelly", $this->testObject->getLabel());
    }

    /**
     * @testdox testSluggify
     *
     * @return void
     */
    public function testSluggify(){
        $this->testObject->setLabel("Vegetables Jelly");
        $this->assertSame("vegetables-jelly", $this->testObject->generateSlug($this->testObject->getLabel()));
    }
}
