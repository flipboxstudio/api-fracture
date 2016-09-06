<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Transformers
    |--------------------------------------------------------------------------
    |
    | Here we define our transformer. Transformer is a class that responsible
    | to transform a resource to a readable format. Fracture automatically
    | will determine what transformer should we use for a resource. For example
    | an App\Models\Book resource / collection that based on App\Models\Book
    | any value from:
    |     config()->get('fracture.transformers.app_models_book.class').
    | If it doesn't exist, Fracture will find the responsible transformer from
    | current route information.
    |
    */

    'transformers' => [

        // 'app_user' => [
        //     'class' => App\Transformers\UserTransformer::class,
        // ],

    ],

    /*
     |--------------------------------------------------------------------------
     | Default Configuration
     |--------------------------------------------------------------------------
     |
     | Here you can define your default configuration for fracture.
     |
     */

    'default' => [

        // Default serializer
        'serializer' => Flipbox\Fracture\Serializers\FractureSerializer::class,

        // Default error serializer
        'error_serializer' => Flipbox\Fracture\Serializers\ErrorSerializer::class,

        // Default transformer
        'transformer' => Flipbox\Fracture\Transformers\FractureTransformer::class,

        // Default error transformer
        'error_transformer' => Flipbox\Fracture\Transformers\ErrorTransformer::class,

    ],

    /*
     |--------------------------------------------------------------------------
     | Routing Configuration
     |--------------------------------------------------------------------------
     |
     | You can define what namespace to be used on each fracture routing.
     | Default is 'App\Http\Controllers\Api'.
     |
     */

    'routes' => [

        // Controller Namespace
        'namespace' => App\Http\Controllers\Api::class,

        // API Subdomain
        'subdomain' => 'api',

    ],

];
