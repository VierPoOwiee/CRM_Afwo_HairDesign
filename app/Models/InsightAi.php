<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsightAi extends Model
{
    protected $table = 'insight_ai';

    protected $fillable = [
        'periode',
        'data_ringkasan',
        'konten_insight',
        'dibuat_pada',
    ];

    protected $casts = [
        'periode' => 'date',
        'data_ringkasan' => 'array',
        'dibuat_pada' => 'datetime',
    ];
}
