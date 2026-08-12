<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pelanggan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nomor_whatsapp',
        'username_ig',
        'jenis_kelamin',
        'catatan_khusus',
        'alamat',
    ];
}