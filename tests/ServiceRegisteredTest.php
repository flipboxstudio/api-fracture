<?php

class ServiceRegisteredTest extends TestCase
{
    public function testFractureFactoryIsBoundToContainer()
    {
        $this->assertTrue($this->app->bound('fracture.factory'));
    }

    public function testFractureFactoryIsBoundCorrectly()
    {
        $this->assertTrue(
            $this->app->make('fracture.factory')
            instanceof
            Flipbox\Fracture\Fracture
        );
    }

    public function testFractureFactoryFacadeInstance()
    {
        $this->assertTrue(
            Flipbox\Fracture\Facades\Fracture::getFacadeRoot()
            instanceof
            Flipbox\Fracture\Fracture
        );
    }
}
