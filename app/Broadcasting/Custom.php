<?php

namespace App\Broadcasting;

use App\User;

class Custom
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\User  $user
     * @return array|bool
     */
    public function join(User $user, $id)
    {
        if ($id === 'Guest') {
            return true;
        }

        return auth('sanctum')->guest() ? false : $user->_id === $id;
    }
}
