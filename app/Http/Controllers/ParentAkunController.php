<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ParentAkun;

class ParentAkunController extends Controller
{
    public function parent(){
       $parent = ParentAkun::all();
       return response()->json([
         'status'=>'success',
         'parent'=> $parent ,
       ]);
     }

    public function store(Request $request)
    {
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
      $data = ParentAkun::find($id)->delete();
      return response()->json([
        'success'=>"Data Deleted successfully."
      ]);
    }
}
