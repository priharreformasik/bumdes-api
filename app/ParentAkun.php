<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParentAkun extends Model
{
    protected $table = 'parent_akun';
    protected $fillable = ['id','nama'];
    public $timestamps = true;

    public function klasifikasi_akun()
    {
        return $this->hasMany('App\KlasifikasiAkun', 'id_parent_akun', 'id');
    }

}