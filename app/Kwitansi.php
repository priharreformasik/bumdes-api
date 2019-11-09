<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kwitansi extends Model
{
    protected $table = 'kwitansi';
    protected $fillable = ['id','no_kwitansi','keterangan','created_by'];
    public $timestamps = true;

    public function jurnal()
    {
        return $this->hasMany('App\Jurnal', 'id_kwitansi', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
}
