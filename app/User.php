<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama', 'email', 'password','no_telepon','alamat'
    ];
    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function klasifikasi_akun()
    {
        return $this->hasMany('App\KlasifikasiAkun', 'created_by', 'id');
    }

    public function data_akun()
    {
        return $this->hasMany('App\DataAkun', 'created_by', 'id');
    }

    public function neraca_awal()
    {
        return $this->hasMany('App\NeracaAwal', 'created_by', 'id');
    }

    public function jurnal()
    {
        return $this->hasMany('App\Jurnal', 'created_by', 'id');
    }

    public function kwitansi()
    {
        return $this->hasMany('App\Kwitansi', 'created_by', 'id');
    }


}
