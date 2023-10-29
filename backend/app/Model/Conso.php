<?php

namespace App\Model;

class Conso extends Model
{
    protected static string $table = "consos";

    protected static array $publicAttributes = [
        "id",
        "conso"
    ];

    public function user(): Relationships\BelongsToOne
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}