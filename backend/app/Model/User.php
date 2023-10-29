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
        $user->insert();
        return $user;
    }

    public function consos(): Relationships\HasMany
    {
        return $this->hasMany(Conso::class, 'user_id');
    }

    public function repas(): Relationships\BelongsToMany
    {
        return $this->belongsToMany(Repas::class, 'users_repas', 'user_id', 'repas_id');
    }

    public function parents(): Relationships\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parents', 'infant_id', 'elder_id');
    }

    public function children(): Relationships\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parents', 'elder_id', 'infant_id');
    }

    public function __set(string $name, $value): void
    {
        if($name == "password")
            parent::__set($name, password_hash($value, PASSWORD_DEFAULT));
        else
            parent::__set($name, $value);

    }
}