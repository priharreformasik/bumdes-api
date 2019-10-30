<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KlasifikasiAkun extends Model
{
    protected $table = 'klasifikasi_akun';
    protected $fillable = ['id','nama','id_parent_akun'];
    public $timestamps = true;

    public function parent_akun()
    {
        return $this->belongsTo('App\ParentAkun', 'id_parent_akun', 'id');
    }

    public function data_akun()
    {
        return $this->hasMany('App\DataAkun', 'id_klasifikasi_akun', 'id');
    }
}
