<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function zanrs()
    {
        return $this->belongsTo(Zanrs::class, 'Zanra_ID', 'Zanra_ID');
    }

    protected static function booted()
    {
        
        static::created(function ($gramata) {
            if ($gramata->Zanra_ID) {
                self::updateGenreBookCount($gramata->Zanra_ID);
            }
        });
        
        
        static::deleted(function ($gramata) {
            if ($gramata->Zanra_ID) {
                self::updateGenreBookCount($gramata->Zanra_ID);
            }
        });

         
        static::updated(function ($gramata) {
            $originalZanraId = $gramata->getOriginal('Zanra_ID');
            $newZanraId = $gramata->Zanra_ID;
            
            if ($originalZanraId != $newZanraId) {
                if ($originalZanraId) {
                    self::updateGenreBookCount($originalZanraId);
                }
                if ($newZanraId) {
                    self::updateGenreBookCount($newZanraId);
                }
            }
        });
    }

    
    private static function updateGenreBookCount($zanraId)
    {
        $count = self::where('Zanra_ID', $zanraId)->count();
        
        DB::table('zanrs')
            ->where('Zanra_ID', $zanraId)
            ->update(['gramatu_skaits' => $count]);
    }
}
