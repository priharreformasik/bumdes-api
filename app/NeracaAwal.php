<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NeracaAwal extends Model
{
    protected $table = 'neraca_awal';
    protected $fillable = ['id','id_data_akun','tanggal','jumlah','tahun','created_by'];
    public $timestamps = true;

    public function data_akun()
    {
        return $this->hasMany('App\DataAkun', 'id_neraca_awal', 'id');
    }

    public function jurnal()
    {
        return $this->hasOne('App\Jurnal', 'id_neraca_awal', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
}
