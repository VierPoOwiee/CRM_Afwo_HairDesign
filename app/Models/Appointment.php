<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    protected $fillable = [
        'tanggal',
        'waktu',
        'nama',
        'service',
        'no_wa',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Nama hari dalam Bahasa Indonesia (Minggu..Sabtu).
     */
    public function hari(): string
    {
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $hari[$this->tanggal->dayOfWeek];
    }
}