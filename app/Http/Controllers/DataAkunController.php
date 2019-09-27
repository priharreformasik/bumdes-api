<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ParentAkun;
use App\KlasifikasiAkun;

class DataAkunController extends Controller
{
    public function show()
    {
      $data = ParentAkun::all();
      $data = ParentAkun::with('klasifikasi_akun.data_akun')->get();
                    // dd($user);
      return response()->json([
         'status'=>'success',
         'data_akun'=> $data ,
       ]);
    }


}
