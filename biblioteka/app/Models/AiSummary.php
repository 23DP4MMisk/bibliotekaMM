<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSummary extends Model
{
    protected $table = 'ai_summaries';
    
    protected $fillable = ['gramatas_id', 'summary_text', 'review_count'];

    public function gramata()
    {
        return $this->belongsTo(Gramata::class, 'gramatas_id', 'ISBN');
    }
}
