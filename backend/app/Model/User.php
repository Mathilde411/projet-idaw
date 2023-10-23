<?php

namespace App\Model;

class User extends Model
{
    protected static string $table = "users";

    protected static array $publicAttributes = [
        "id",
        "name",
        "email"
    ];

    public static function createUser(string $name, string $email, ?string $password): ?User
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        if(isset($password))
            $user->password = $password;
        if($user->insert())
            return $user;
        return null;
    }

    public function __set(string $name, $value): void
    {
        if($name == "password")
            parent::__set($name, password_hash($value, PASSWORD_DEFAULT));
        else
            parent::__set($name, $value);

    }
}