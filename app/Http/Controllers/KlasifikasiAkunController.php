<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\KlasifikasiAkun;
use App\ParentAkun;
use Illuminate\Support\Facades\Validator;

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

    public function detail($id)
    {
      $data = KlasifikasiAkun::where('id', $id)->first();
      return response()->json([
         'status'=>'success',
         'parent'=> $data
       ]);
    }

    public function store(Request $request){

      $validator = Validator::make($request->all(), [
        'nama' => 'required',
        'id' => 'required|unique:klasifikasi_akun',
        'id_parent_akun' => 'required'
        ]);

      if ($validator->fails()) {
        return response()->json(['error'=>$validator->errors()], 401);
      }

      $data = KlasifikasiAkun::create([
              'id' => $request->id,
              'nama' => $request->nama,
              'id_parent_akun' => request('id_parent_akun'),
              ]);

      $klasifikasi = KlasifikasiAkun::where('id', $request->id)->get();

      return response()->json([
        'status'=>'success',
        'result'=> $klasifikasi
      ]);
    }

    public function update(Request $request,$id)
    {

      $validator = Validator::make($request->all(), [
        'nama' => 'required',
        'id_parent_akun' => 'required',
        ]);
      if ($validator->fails()) {
        return response()->json(['error'=>$validator->errors()], 401);
      }

      // $this->validate($request, [
      //    'nama' => 'required',
      //    'id' => 'required|unique:klasifikasi_akun,id,'.$request->id,
      //    'id_parent_akun' => 'required',
      //   ]);

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
