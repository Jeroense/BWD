<?php

namespace App\Observers;

use App\User;

class UserObserver
{
    /**
     * Handle to the User "created" event.
     * This will happen after the record is created
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(User $user)
    {
        //
    }

        /**
     * Handle to the User "creating" event.
     * This will happen before the record is created
     *
     * @param  \App\User  $user
     * @return void
     */
    public function creating(User $user)
    {
        $user->api_token = bin2hex(openssl_random_pseudo_bytes(30));
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }
}
