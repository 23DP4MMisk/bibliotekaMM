<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Lietotajs extends Authenticatable implements JWTSubject
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}