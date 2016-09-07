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
            Flipbox\Fracture\ResponseFactory
        );
    }

    public function testFractureFactoryFacadeInstance()
    {
        $this->assertTrue(
            Flipbox\Fracture\Fracture::getFacadeRoot()
            instanceof
            Flipbox\Fracture\ResponseFactory
        );
    }
}
