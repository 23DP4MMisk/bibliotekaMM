<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zanrs extends Model
{
    use HasFactory;

    protected $table = 'zanrs';
    protected $primaryKey = 'Zanra_ID';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $fillable = [
        'nosaukums', 'gramatu_skaits', 'Nodala'
    ];

    public function gramatas()
    {
        return $this->hasMany(Gramata::class, 'Zanra_ID', 'Zanra_ID');
    }
}