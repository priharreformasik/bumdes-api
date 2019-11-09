<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\KlasifikasiAkun;
use App\ParentAkun;
use App\DataAkun;
use Illuminate\Support\Facades\Validator;
use Auth;

class KlasifikasiAkunController extends Controller
{
    public function show(Request $request)
    {
      if ($request->id) {
          $klasifikasi = KlasifikasiAkun::where('id', $request->id)->get();
      }elseif ($request->id_parent_akun) {
          $klasifikasi = KlasifikasiAkun::where('id_parent_akun', $request->id_parent_akun)->get();
      }else {
          $klasifikasi = KlasifikasiAkun::where('created_by', Auth::user()->id)->orWhere('created_by',1)->get();
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
              'created_by' => Auth::user()->id,
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
      $data = KlasifikasiAkun::where('id',$id)->first();
        $dataAkun = DataAkun::where('id_klasifikasi_akun', $data->id)->get()->count();
         if ($dataAkun > 0) {
           return response()->json([
             'status'=>'failed',
             'message'=>'Data is being used by another table!'
           ]);
         }else {
           $data->delete();
           return response()->json([
             'status'=>'success',
             'message'=>'Data Deleted successfully.'
           ]);         
         }
    }

}
