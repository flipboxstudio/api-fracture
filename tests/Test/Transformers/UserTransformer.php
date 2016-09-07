<?php

namespace Test\Transformers;

use Test\Models\User;
use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
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
