<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ParentAkun;
use App\KlasifikasiAkun;
use Illuminate\Support\Facades\Validator;

class ParentAkunController extends Controller
{
    public function parent(){
       $parent = ParentAkun::all();
       return response()->json([
         'status'=>'success',
         'parent'=> $parent ,
       ]);
     }

    public function detail($id)
    {
      $parent = ParentAkun::where('id', $id)->first();
      return response()->json([
         'status'=>'success',
         'parent'=> $parent
       ]);
    }

    public function store(Request $request)
    {

      $validator = Validator::make($request->all(), [
        'nama' => 'required',
        'id' => 'required|unique:parent_akun',
        ]);
      if ($validator->fails()) {
        return response()->json(['error'=>$validator->errors()], 401);
      }

      $parent = ParentAkun::create([
        'id' => request('id'),
        'nama' => request('nama')
      ]);

      $akun = ParentAkun::where('id', $request->id)->get();
    
      return response()->json([
        'status'=>'successsssss',
        'result'=> $akun
      ]);
    }

    public function update(Request $request,$id)
    {
      $validator = Validator::make($request->all(), [
        'nama' => 'required',
        'id' => 'required|unique:parent_akun,id,'.$request->id,
        ]);
      if ($validator->fails()) {
        return response()->json(['error'=>$validator->errors()], 401);
      }

      $data = ParentAkun::find($id);
      $data->nama=$request->get('nama');
      $data->save();

      return response()->json([
        'status'=>'successsssss',
        'result'=> $data ,
      ]);
    }

    public function destroy($id)
    {
      $data = ParentAkun::where('id',$id)->first();
        $klasifikasiAkun = KlasifikasiAkun::where('id_parent_akun', $data->id)->get()->count();
         if ($klasifikasiAkun > 0) {
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
