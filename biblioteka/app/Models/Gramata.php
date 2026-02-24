<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gramata extends Model
{
     use HasFactory;

    protected $table = 'Gramata';       // tabula vards
    protected $primaryKey = 'ISBN';     // primara atslega
    public $incrementing = false;       // ISBN nav autoinkrements
    protected $keyType = 'integer';     // atslegas tips

    protected $fillable = [
        'ISBN', 'nosaukums', 'gads', 'apraksts', 'lapu_skaits', 
        'faila_pdf', 'vaku_attels', 'autors', 'Zanra_ID', 'Nodala_ID'
    ];

    public function nodala()
    {
        return $this->belongsTo(Nodala::class, 'Nodala_ID');
    }
}
