<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\KlasifikasiAkun;
use App\NeracaAwal;
use App\Jurnal;
use Illuminate\Support\Facades\Validator;
use Auth;

class DataAkunController extends Controller
{
    public function show(Request $request)
    {
      if ($request->id) {
          $data = DataAkun::where('id', $request->id)->get();
      }elseif ($request->id_klasifikasi_akun) {
          $data = DataAkun::where('data_akun.id_klasifikasi_akun', $request->id_klasifikasi_akun)->get();
      }else {
          $data = DataAkun::where('created_by', Auth::user()->id)->orWhere('created_by',1)->get();
      }

      return response()->json([
         'status'=>'success',
         'data_akun'=> $data ,
       ]);
    }

    public function detail($id)
    {
      $data = DataAkun::where('id', $id)->first();
      return response()->json([
         'status'=>'success',
         'parent'=> $data
       ]);
    }

    public function store(Request $request){

      $validator = Validator::make($request->all(), [
        'nama' => 'required',
        'id' => 'required|unique:data_akun',
        'id_klasifikasi_akun' => 'required',
        'posisi_normal' => 'required'
        ]);
      if ($validator->fails()) {
        return response()->json(['error'=>$validator->errors()], 401);
      }

      $data = DataAkun::create([
              'id' => $request->id,
              'id_klasifikasi_akun' => request('id_klasifikasi_akun'),
              'nama' =>$request->nama,
              'posisi_normal' => request('posisi_normal'),              
              'created_by' => Auth::user()->id,
              ]);
      $akun = DataAkun::where('id', $request->id)->get();
      return response()->json([
        'status'=>'success',
        'result'=>$akun
      ]);
    }

    public function update(Request $request,$id)
    {
      $validator = Validator::make($request->all(), [
        'nama' => 'required',
        'id' => 'required|unique:data_akun,id,'.$request->id,
        'id_klasifikasi_akun' => 'required',
        'posisi_normal' => 'required'
        ]);
      if ($validator->fails()) {
        return response()->json(['error'=>$validator->errors()], 401);
      }

      $data = DataAkun::find($id);
      $data->id_klasifikasi_akun=$request->get('id_klasifikasi_akun');
      $data->posisi_normal=$request->get('posisi_normal');
      $data->nama=$request->get('nama');
      $data->save();

      return response()->json([
        'status'=>'successsssss',
        'result'=> $data ,
      ]);
    }

    public function destroy($id)
    {
      $data = DataAkun::where('id',$id)->first();
        $neracaAwal = NeracaAwal::where('id_data_akun', $data->id)->get()->count();        
        $jurnal = Jurnal::where('id_data_akun', $data->id)->get()->count();
        if ($data->created_by == 9) {
          return response()->json([
             'status'=>'failed',
             'message'=>'Data cannot deleted cause created by admin!'
           ]);
        } else  {
          if ($neracaAwal > 0) {
           return response()->json([
             'status'=>'failed',
             'message'=>'Data is being used by another table!'
           ]);
         }else if ($jurnal > 0) {
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


}
