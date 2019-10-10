<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\KlasifikasiAkun;
use App\ParentAkun;

class KlasifikasiAkunController extends Controller
{
    public function show(Request $request)
    {
      // $klasifikasi = ParentAkun::all();
      // $klasifikasi = ParentAkun::with('klasifikasi_akun')->get();
      if ($request->id) {
          $klasifikasi = KlasifikasiAkun::where('id', $request->id)->get();
      }elseif ($request->id_parent_akun) {
          $klasifikasi = KlasifikasiAkun::where('id_parent_akun', $request->id_parent_akun)->get();
      }else {
          $klasifikasi = KlasifikasiAkun::all();
      }

                    // dd($user);
      return response()->json([
         'status'=>'success',
         'klasifikasi_akun'=> $klasifikasi ,
       ]);
    }

    public function store(Request $request){

      $data = KlasifikasiAkun::create([
              'id' => $request->id,
              'nama' =>$request->nama,
              'id_parent_akun' => request('id_parent_akun'),
              ]);
      return response()->json([
        'status'=>'success',
        'result'=>$data
      ]);
    }

    public function update(Request $request,$id)
    {
        $data = KlasifikasiAkun::find($id);
        $data->nama=$request->get('nama');
        $data->id_parent_akun=$request->get('id_parent_akun');
        $data->save();
      return response()->json([
        'status'=>'successsssss',
        'result'=> $data ,
      ]);
    }

    public function destroy($id)
    {
      $data = KlasifikasiAkun::find($id)->delete();
      return response()->json([
        'success'=>"Data Deleted successfully."
      ]);
    }

}
