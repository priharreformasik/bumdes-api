<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\NeracaAwal;

class NeracaAwalController extends Controller
{
    public function show()
    {
      $neraca_awal = NeracaAwal::all();
      $neraca_awal = NeracaAwal::with('data_akun')->get();
                    // dd($user);
      return response()->json([
         'status'=>'success',
         'neraca_awal'=> $neraca_awal ,
       ]);
    }
}
