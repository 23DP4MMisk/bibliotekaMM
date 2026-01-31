<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gramata extends Model
{
     use HasFactory;

    protected $table = 'Gramata';       // имя таблицы в базе
    protected $primaryKey = 'ISBN';     // первичный ключ
    public $incrementing = false;       // ISBN не автоинкремент
    protected $keyType = 'integer';     // тип ключа

    protected $fillable = [
        'ISBN', 'nosaukums', 'gads', 'apraksts', 'lapu_skaits', 
        'faila_pdf', 'vaku_attels', 'autors', 'Zanra_ID', 'Nodala_ID'
    ];

    public function nodala()
    {
        return $this->belongsTo(Nodala::class, 'Nodala_ID');
    }
}
