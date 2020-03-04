<?php

namespace App\Common;

use App\Models\User;
use App\Helper\HashHelper;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class ApiUserProvider implements UserProvider
{
    private $userId, $timestamp;


    /**
     * Create a new database user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  int  $userId
     * @param  int  $timestamp
     * @param  string $token
     * @return void
     */
    public function __construct($userId, $timestamp)
    {
        $this->userId = $userId;

        $this->timestamp = $timestamp;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        //--
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
       //--
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Model  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        //--
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
           (count($credentials) === 1 &&
            Str::contains($this->firstCredentialKey($credentials), 'password'))) {
            return;
        }

        $query = User::query();

        $user = $query->where('id', $this->getUserId())->first();

        if (!$user) {

            return;

        }

        //確認token
        if (HashHelper::checkApiToken($credentials['api_token'], $user->id, $this->getTimestamp())) {

            return;

        }

        return $user;
    }

    /**
     * Get the first key from the credential array.
     *
     * @param  array  $credentials
     * @return string|null
     */
    protected function firstCredentialKey(array $credentials)
    {
        foreach ($credentials as $key => $value) {
            return $key;
        }
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        //--
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

}
