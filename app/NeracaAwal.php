<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NeracaAwal extends Model
{
    protected $table = 'neraca_awal';
    protected $fillable = ['id','nama','id_data_akun','tanggal','jumlah'];
    public $timestamps = false;

    public function data_akun()
    {
        return $this->hasMany('App\DataAkun', 'id_neraca_awal', 'id');
    }
}
