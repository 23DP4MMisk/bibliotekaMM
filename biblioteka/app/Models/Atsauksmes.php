<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/** 
 * Savienojumi:
 * - belongsTo Lietotajs - atsauksmes autors
 * - belongsTo Gramata - gramata, kurai atsauksme pieder
 * - belongsTo Atsauksmes - vecākais komentārs
 * - hasMany Atsauksmes - bērnu komentāri
*/


class Atsauksmes extends Model
{
    use HasFactory;

    protected $table = 'Atsauksmes';
    protected $primaryKey = 'Atsauksmes_ID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Lietotaja_ID',
        'Gramatas_ID',
        'vertejums',
        'komentārs',
        'vecakais_komentars'  
    ];

    protected $casts = [
        'vertejums' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Savienojums ar lietotaju (komnts autora)
    public function lietotajs()
    {
        return $this->belongsTo(Lietotajs::class, 'Lietotaja_ID', 'kodsID');
    }

    // Savienojums ar gramatu
    public function gramata()
    {
        return $this->belongsTo(Gramata::class, 'Gramatas_ID', 'ISBN');
    }

    // Vecaku komentars (uz kuru atbild)
    public function vecakais()
    {
        return $this->belongsTo(Atsauksmes::class, 'vecakais_komentars', 'Atsauksmes_ID');
    }

    // Bernu komentars (atbildi uz to )
    public function berni()
    {
        return $this->hasMany(Atsauksmes::class, 'vecakais_komentars', 'Atsauksmes_ID');
    }

}
