<?php

namespace App\Tests;

use App\Entity\Admin;
use PHPUnit\Framework\TestCase;


class AdminTest extends TestCase
{

    private ?Admin $testObject;

    protected function setUp(): void
    {
        $this->testObject = new Admin;
    }

    /**
     * @testdox testSetPseudonyme
     *
     * @return void
     */
    public function testSetPseudonyme(): void
    {
        $this->testObject->setPseudonyme("Jhon");
        $this->assertSame("Jhon", $this->testObject->getPseudonyme());
    }

    /**
     * @testdox testSetEmail
     *
     * @return void
     */
    public function testSetEmail(): void
    {
        $this->testObject->setEmail("test@test.com");
        $this->assertSame("test@test.com", $this->testObject->getEmail());
    }

    /**
     * @testdox testEmailIsValid
     *
     * @return void
     */
    public function testEmailIsValid(): void
    {
        // $this->expectException(\InvalidArgumentException::class);
        $this->testObject->setEmail("InvalidEmail");
        $this->assertFalse((bool)preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $this->testObject->getEmail()));

    }

    /**
     * @testdox testSetPassword
     *
     * @return void
     */
    public function testSetPassword(): void
    {
        $this->testObject->setPassword("MySuperPasswordWhichIsS3cret!");
        $this->assertSame("MySuperPasswordWhichIsS3cret!", $this->testObject->getPassword());
    }

    /**
     * @testdox testSetRoles
     *
     * @return void
     */
    public function testSetRoles(): void
    {
        $this->testObject->setRoles(['ROLE_USER', 'ROLE_BUYER']);
        $this->assertSame(['ROLE_USER', 'ROLE_BUYER'], $this->testObject->getRoles());
    }
}
