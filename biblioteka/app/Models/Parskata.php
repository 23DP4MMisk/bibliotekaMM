<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parskata extends Model
{
    protected $table = 'Parskata';

    protected $primaryKey = 'Parskata_ID';
    public $incrementing = true;          
    protected $keyType = 'int';           
    public $timestamps = true; 
    protected $fillable = ['parskatas_skaits', 'Gramatas', 'Lietotajs'];
}