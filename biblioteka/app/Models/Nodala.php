<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nodala extends Model
{
   use HasFactory;

    protected $table = 'Nodala';
    protected $primaryKey = 'Nodala_ID';
    public $incrementing = true;

    protected $fillable = ['tips'];

    public function gramatas()
    {
        return $this->hasMany(Gramata::class, 'Nodala_ID');
    }
}
