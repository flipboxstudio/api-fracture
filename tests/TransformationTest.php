<?php

class TransformationTest extends TestCase
{
    public function testTransformerClassExists()
    {
        $this->assertTrue(class_exists(Test\Transformers\UserTransformer::class));
    }

    public function testArrayTransformation()
    {
        $resource = Flipbox\Fracture\Fracture::item([
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz',
            'quux' => 'Quux',
        ]);

        $this->assertTrue($resource instanceof League\Fractal\Scope);
        $this->assertTrue(method_exists($resource, 'toArray'));
        $this->assertTrue(is_array($resource->toArray()));
    }

    public function testEloquentTransformation()
    {
        $user = Test\Models\User::first();
        $resource = Flipbox\Fracture\Fracture::item($user);

        $this->assertTrue($resource instanceof League\Fractal\Scope);
        $this->assertTrue(method_exists($resource, 'toArray'));
        $this->assertTrue(is_array($resource->toArray()));
    }

    public function testEloquentCustomTransformer()
    {
        Flipbox\Fracture\Fracture::setTransformer(Test\Transformers\UserTransformer::class);

        $user = Test\Models\User::first();
        $resource = Flipbox\Fracture\Fracture::item($user);

        $this->assertTrue(Fracture::getTransformer(true) instanceof Test\Transformers\UserTransformer);
        $this->assertTrue($resource instanceof League\Fractal\Scope);
        $this->assertTrue(method_exists($resource, 'toArray'));
        $this->assertTrue(is_array($resource->toArray()));
    }

    public function testEloquentCustomTransformerViaConfiguration()
    {
        Config::set('fracture.transformers.test_models_user.class', Test\Transformers\UserTransformer::class);

        $user = Test\Models\User::first();
        $resource = Flipbox\Fracture\Fracture::item($user);

        $this->assertTrue(Fracture::getTransformer(true) instanceof Test\Transformers\UserTransformer);
        $this->assertTrue($resource instanceof League\Fractal\Scope);
        $this->assertTrue(method_exists($resource, 'toArray'));
        $this->assertTrue(is_array($resource->toArray()));
    }

    public function testCollectionTransformation()
    {
        $users = Test\Models\User::all();
        $resource = Flipbox\Fracture\Fracture::collection($users);

        $this->assertTrue($resource instanceof League\Fractal\Scope);
        $this->assertTrue(method_exists($resource, 'toArray'));
        $this->assertTrue(is_array($resource->toArray()));
    }

    public function testErrorTransformation()
    {
        $error = new Exception();
        $resource = Flipbox\Fracture\Fracture::error($error);

        $this->assertTrue($resource instanceof League\Fractal\Scope);
        $this->assertTrue(method_exists($resource, 'toArray'));
        $this->assertTrue(is_array($resource->toArray()));
    }
}
