<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Lietotajs extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'lietotajs';
    protected $primaryKey = 'kodsID';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'lietotaja_vards',
        'epasts',
        'parole',
        'loma',
        'registresanas_datums',
        'status'
    ];

    protected $hidden = [
        'parole'
    ];

    protected $casts = [
        'registresanas_datums' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public function getAuthPassword()
    {
        return $this->parole;
    }
}