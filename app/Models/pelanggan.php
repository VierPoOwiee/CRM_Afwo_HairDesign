<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggans';

    protected $fillable = [
        'nama',
        'jenis_kelamin',
        'no_wa',
        'username_instagram',
        'catatan_khusus',
        'jenis_rambut',
        'alamat',
    ];

    public function transaksiKunjungan(): HasMany
    {
        return $this->hasMany(TransaksiKunjungan::class, 'id_pelanggan');
    }
}
