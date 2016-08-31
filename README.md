# Fracture

Fracture is a library to make your Laravel-based API response more consistent.

## Installation

```
composer require flipboxstudio/api-fracture
```

## Configuration

Add this line to your service provider list:

```
Flipbox\Fracture\FractureServiceProvider::class,
```

After that, run:

```
php artisan vendor:publish --tag="config"
```

This command will copy an empty `fracture.php` configuration into your `config` directory.
You may need to read this file for more custom configuration.

### Optional

Add this line to your facade list:

```
'Fracture' => Flipbox\Fracture\Fracture::class,
```

## Usage

To register your route, use `Flipbox\Fracture\Api` facade instead of laravel `Route` facade.
You need to change your `App\Providers\RouteServiceProvider` class. See the `mapApiRoutes` method.
On default Laravel installation, it's still use `Route` facade, there you need to change to `Flipbox\Fracture\Api`.

```php
<?php

namespace App\Providers;

use Flipbox\Fracture\Api;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your api controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $apiNamespace = 'App\Http\Controllers\Api';

    // [... OMITTED ...]

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Api::group([
            'middleware' => ['api'],
            'namespace' => $this->apiNamespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }
}
```

Then, change your routes file:

```
Api::group(['middleware' => ['auth:api']], function ($route) {
    $route->get('/user', 'AuthController@user');

    $route->resource('/resource/user', 'UserController');
});
```

Below is an example controller using Fracture:

```php
<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Fracture::setMessage('user_list_fetched');

        return User::all(); // identical with Fracture::responseCollection(User::all(), 'user_list_fetched')
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        Fracture::setMessage('user_fetched');

        return User::findOrFail($id); // identical with Fracture::responseItem(User::findOrFail($id), 'user_fetched')
    }
```

Fracture has it's own default transformer which you can configure it on `fracture.php` config file.
From above controller (with fracture default transformer), here's your response transformed into:

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Krisan Alfa Timur",
        "email": "alfa@flipbox.co.id",
        "created_at": "2016-08-29 07:01:08",
        "updated_at": "2016-08-29 11:37:10",
        "type": "user"
      }
    ]
  },
  "message": "user_list_fetched"
}
```

To configure your transformer, Fracture will determine the object returned from controller.
So, to transform your `App\User` object, you need to configure the transformer in `fracture.php`, file inside `transformers`, add `app_user` information there:

```php
<?php

return [

    'transformers' => [

        'app_user' => [
            'class' => App\Transformers\UserTransformer::class,
        ],

    ],


    // [... OMITTED ...]

];
```

Fracture will automatically `camel_case` your current transformer to determine which config should it use.
So for `App\User` resource type, it will parsed to `app_user`.
Inside that key, give it an array key-value based with `class` value as it's transformer.
Here's a basic transformer:

```php
<?php

namespace App\Transformers;

use URL;
use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'type' => 'user',
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'links' => [[
                'rel' => 'self',
                'type' => 'api',
                'uri' => URL::to('/api/resource/user/'.$user->id),
            ]],
        ];
    }
}
```

From the above transformer, the response will be transformed into:

```json
{
  "success": true,
  "data": [
    {
      "type": "user",
      "id": "1",
      "attributes": {
        "name": "Krisan Alfa Timur",
        "links": [
          {
            "rel": "self",
            "type": "api",
            "uri": "http://laravel.ivory.dev/api/resource/user/1"
          }
        ]
      }
    }
  ],
  "message": "user_list_fetched"
}
```

### Set Transformer on The Fly

You can set the transformer just before the response being sent to client.
To do so, before return any response in controller method, you may call `Fracture::setTransformer` method:

```php
<?php

use App\Transformers\UserTransformer;

Fracture::setTransformer(UserTransformer::class);
// Or
Fracture::setTransformer(new UserTransformer());
```

### Route Transformer

You also can tell Fracture to use certain transformer via route actions:

```php
<?php

Route::get('/user', [
    'uses' => 'UserController@index',
    'transformer' => App\Transformers\UserTransformer::class,
    'middleware' => ['auth:api'],
    'as' => 'user.list'
]);
```

### Custom HTTP Status Code

```php
<?php

return Fracture::responseCollection(
    $collection, // Collection
    'user_list', // API Message
    true, // API success status
    200, // HTTP status code
    ['Custom-Header' => 'Flipbox'] // Your header goes here
);

return Fracture::responseItem(
    $item, // Item
    'user_info', // API Message
    true, // API success status
    200, // HTTP status code
    ['Custom-Header' => 'Flipbox'] // Your header goes here
);
```

### Generating Error Response

Here's a snippet to generate error response:

```php
<?php

return Fracture::responseError(
    'awww_snap', // API Message
    new \Exception('Something bad happen'), // An exception instance
    500, // HTTP status code
    ['Custom-Header' => 'Flipbox'] // Your header goes here
);
```

From that code, the response is:

```json
{
  "success": false,
  "data": {
    "type": "error",
    "code": 0
  },
  "message": "awww_snap"
}
```

## ToDo

- [ ] Refactor (need voulenteer)
- [x] Unit testing
- [x] Global error handling
- [x] Configurable serializer
- [x] Configurable error serializer
- [x] Configurable error transformer
- [x] Default transformer for all resource
- [x] Intercept default Laravel response preparation to use Fracture
- [ ] You name it ;)
