<?php

namespace App\Model;

class Repas extends Model
{
    protected static string $table = "repas";

    protected static array $publicAttributes = [
        "id",
        "name"
    ];

    public function users(): Relationships\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_repas', 'repas_id', 'user_id');
    }
}