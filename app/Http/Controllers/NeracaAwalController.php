<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\ParentAkun;
use App\KlasifikasiAkun;
use App\NeracaAwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use DB;
use Auth;

class NeracaAwalController extends Controller
{
    public function show(Request $request)
    {
      if($request->has('year')){
        $year = $request->input('year');
        $neraca_awal = NeracaAwal::leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                  ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                  ->whereRaw('YEAR(tanggal) = '.$year)
                                  ->where('neraca_awal.created_by', Auth::user()->id)
                                  ->select('klasifikasi_akun.id as kode_klasifikasi','data_akun.nama as nama_akun','data_akun.id as kode_akun','neraca_awal.tanggal','neraca_awal.jumlah','neraca_awal.id as id_neraca_awal')
                                  ->orderBy('data_akun.id')
                                  ->get();
        $total_kredit = DB::table("neraca_awal")->leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                              ->where('data_akun.posisi_normal','Kredit')
                                              ->where('neraca_awal.created_by', Auth::user()->id)
                                              ->whereRaw('YEAR(tanggal) = '.$year)
                                              ->sum('jumlah');

        $total_debit = DB::table("neraca_awal")->leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                              ->where('data_akun.posisi_normal','Debit')
                                              ->where('neraca_awal.created_by', Auth::user()->id)
                                              ->whereRaw('YEAR(tanggal) = '.$year)
                                              ->sum('jumlah');
        return response()->json([
           'status'=>'success',
           'neraca_awal'=> $neraca_awal,
           'total_kredit'=>$total_kredit,
           'total_debit'=>$total_debit
         ]);

      }elseif (empty($neraca_awal[0]->id)) {
        return response()->json([
        'result'=>'Data tidak tersedia' ,
        ]);
      }
      
    }

    public function data_akun()
    {
      $data = DataAkun::where('created_by', Auth::user()->id)->orWhere('created_by',1)->select('data_akun.id', 'data_akun.nama')->orderBy('data_akun.id')->get();
      return response()->json([
         'status'=>'success',
         'akun'=> $data
       ]);
    }


    public function show_klasifikasi(Request $request)
    {
      if($request->has('year') && $request->has('id_klasifikasi')){
        $year = $request->input('year');
        $id_klasifikasi = $request->input('id_klasifikasi');


        $neraca_awal = NeracaAwal::leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                  ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                  ->where('neraca_awal.created_by', Auth::user()->id)
                                  ->whereRaw('YEAR(tanggal) = '.$year)
                                  ->where('klasifikasi_akun.id', $id_klasifikasi)
                                  ->select('klasifikasi_akun.id as kode_klasifikasi','data_akun.id as kode_akun','data_akun.nama as nama_akun','neraca_awal.tanggal','neraca_awal.jumlah','neraca_awal.id as id_neraca_awal')
                                  ->orderBy('data_akun.id')
                                  ->get();


        return response()->json([
           'status'=>'success',
           'neraca_awal'=>$neraca_awal
         ]);

      }elseif (empty($neraca_awal[0]->id)) {
        return response()->json([
        'result'=>'Data tidak tersedia' ,
        ]);
      }
      
    }

    public function parent(Request $request){

      $parent = ParentAkun::all();

      if($request->has('year')){
      $year = $request->input('year');
      

      $total_kredit = DB::table("neraca_awal")->leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                              ->where('data_akun.posisi_normal','Kredit')
                                              ->where('neraca_awal.created_by', Auth::user()->id)
                                              ->whereRaw('YEAR(tanggal) = '.$year)
                                              ->sum('jumlah');

      $total_debit = DB::table("neraca_awal")->leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                              ->where('data_akun.posisi_normal','Debit')
                                              ->where('neraca_awal.created_by', Auth::user()->id)
                                              ->whereRaw('YEAR(tanggal) = '.$year)
                                              ->sum('jumlah');
      }
      return response()->json([
        'status'=>'success',
        'parent'=> $parent ,
        'total_debit'=>$total_debit,
        'total_kredit'=>$total_kredit
      ]);
     }


     public function klasifikasi(Request $request)
     {
       if ($request->has('id_parent_akun')) {
         
          $id_parent_akun = $request->input('id_parent_akun');

          $klasifikasi = KlasifikasiAkun::where('id_parent_akun', $id_parent_akun)
                              // ->where('created_by', Auth::user()->id)->orWhere('created_by',1)
                              ->select('klasifikasi_akun.id_parent_akun as kode_parent','klasifikasi_akun.id as kode_klasifikasi','klasifikasi_akun.nama as klasifikasi_akun')
                              ->get();
      }
       return response()->json([
          'status'=>'success',
          'klasifikasi_akun'=> $klasifikasi,
        ]);
     }

    public function detail($id)
    {
      $data = NeracaAwal::where('id', $id)->first();
      return response()->json([
         'status'=>'success',
         'parent'=> $data
       ]);
    }

    public function store(Request $request){
      $data = NeracaAwal::where('id_data_akun',$request->id_data_akun)
                          ->where('created_by',Auth::user()->id)->latest()->first();
      if ($data == NULL) {
        $date = '0000-00-00';
      } else {
        $date = date('Y-m-d', strtotime($data->tanggal . " +1 year") );
      }
      $validator = Validator::make($request->all(), [
        'tanggal' => 'required|after_or_equal:'.$date,
        'id_data_akun' => 'required',
        'jumlah' => 'required',
        ]);
      if ($validator->fails()) {
        return response()->json(['error'=>$validator->errors()], 401);
      }

      $data = NeracaAwal::create([
              'id_data_akun' => request('id_data_akun'),
              'tanggal' => request('tanggal'),
              'jumlah' => request('jumlah'),
              'created_by' => Auth::user()->id,
              ]);
      $neraca_awal = NeracaAwal::where('neraca_awal.id', $data->id)
                                ->leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->where('neraca_awal.created_by', Auth::user()->id)
                                ->select('klasifikasi_akun.id as kode_klasifikasi','data_akun.id as kode_akun','neraca_awal.tanggal','neraca_awal.jumlah','neraca_awal.id as id_neraca_awal')
                                ->first();

      return response()->json([
        'status'=>'success',
        'result'=>$neraca_awal
      ]); 
    }

    public function update(Request $request,$id)
    {
      $validator = Validator::make($request->all(), [
        'tanggal' => 'required',
        'id_data_akun' => 'required',
        'jumlah' => 'required'
        ]);
      if ($validator->fails()) {
        return response()->json(['error'=>$validator->errors()], 401);
      }

      $data = NeracaAwal::find($id);
      $data->id_data_akun=$request->get('id_data_akun');
      $data->tanggal=$request->get('tanggal');        
      $data->jumlah=$request->get('jumlah');
      $data->save();

      $neraca_awal = NeracaAwal::where('neraca_awal.id', $data->id)
                                ->leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->select('klasifikasi_akun.id as kode_klasifikasi','data_akun.id as kode_akun','neraca_awal.tanggal','neraca_awal.jumlah','neraca_awal.id as id_neraca_awal')
                                ->first();

      return response()->json([
        'status'=>'successsssss',
        'result'=> $neraca_awal ,
      ]);
    }

    public function destroy($id)
    {
      $data = NeracaAwal::find($id)->delete();
      return response()->json([
        'success'=>"Data Deleted successfully."
      ]);
    }


}
