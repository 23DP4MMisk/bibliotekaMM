<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lejupielade extends Model
{
    protected $table = 'Lejupielade';
    protected $fillable = ['Datums', 'Gramatas_ID', 'Lietotajs_ID'];
}