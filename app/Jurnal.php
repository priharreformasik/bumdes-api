<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $table = 'jurnal';
    protected $fillable = ['id','id)kwitansi','tanggal','id_data_akun','jumlah','posisi_normal','saldo_akhir'];
    public $timestamps = false;

    public function kwitansi()
    {
        return $this->belongsTo('App\Kwitansi', 'id_kwitansi', 'id');
    }

    public function data_akun()
    {
        return $this->belongsTo('App\DataAkun', 'id_data_akun', 'id');
    }
}
