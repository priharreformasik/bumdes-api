<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataAkun extends Model
{
    protected $table = 'data_akun';
    protected $fillable = ['id','id_klasifikasi_akun','nama','posisi_normal'];
    public $timestamps = true;

    public function klasifikasi_akun()
    {
        return $this->belongsTo('App\KlasifikasiAkun', 'id_klasifikasi_akun', 'id');
    }

    public function neraca_awal()
    {
        return $this->belongsTo('App\NeracaAwal', 'id_neraca_awal', 'id');
    }

    public function jurnal()
    {
        return $this->hasMany('App\Jurnal', 'id_data_akun', 'id');
    }
}
