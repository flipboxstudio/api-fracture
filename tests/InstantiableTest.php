<?php

class InstantiableTest extends TestCase
{
    public function testProviderClassExists()
    {
        $this->assertTrue(class_exists('Flipbox\Fracture\FractureServiceProvider'));
    }

    public function testFractureClassExists()
    {
        $this->assertTrue(class_exists('Flipbox\Fracture\Fracture'));
    }

    public function testFacadeClassExists()
    {
        $this->assertTrue(class_exists('Flipbox\Fracture\Facades\Fracture'));
    }
}
