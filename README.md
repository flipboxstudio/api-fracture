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
Read this file for more information.

### Optional

Add this line to your facade list:

```
'Fracture' => Flipbox\Fracture\Fracture::class,
```

## Usage

```php
<?php

namespace App\Http\Controllers\Api;

use Fracture;
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

        return User::all();
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

        return User::findOrFail($id);
    }
```

Configure your transformer like so:

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

return Fracture::collection(
    $collection, // Collection
    $message = 'user_list', // API Message
    true, // API success status
    200, // HTTP status code
    ['Custom-Header' => 'Flipbox'] // Your header goes here
);

return Fracture::item(
    $item, // Item
    $message = 'user_info', // API Message
    true, // API success status
    200, // HTTP status code
    ['Custom-Header' => 'Flipbox'] // Your header goes here
);
```

### Generating Error Response

Here's snippet to generate error response:


```php
<?php

return Fracture::error(
    $message = 'awww_snap', // API Message
    new Exception('Something bad happen'), // An exception instance
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

- [ ] Unit testing
- [x] Global error handling
- [x] Configurable serializer
- [x] Configurable error serializer
- [x] Configurable error transformer
- [x] Default transformer for all resource
- [x] Intercept default Laravel response preparation to use Fracture
- [ ] You name it ;)
