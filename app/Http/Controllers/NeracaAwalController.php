<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\NeracaAwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use DB;

class NeracaAwalController extends Controller
{
    public function show(Request $request)
    {
      if($request->has('month') && $request->has('year')){
        $month = $request->input('month');
        $year = $request->input('year');
        $neraca_awal = NeracaAwal::leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                  ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                  ->whereRaw('MONTH(tanggal) = '.$month)
                                  ->whereRaw('YEAR(tanggal) = '.$year)
                                  ->select('klasifikasi_akun.id as kode_klasifikasi','data_akun.id as kode_akun','neraca_awal.tanggal','neraca_awal.jumlah','neraca_awal.id as id_neraca_awal')
                                  ->orderBy('data_akun.id')
                                  ->get();
        $total_kredit = DB::table("neraca_awal")->leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                              ->where('data_akun.posisi_normal','Kredit')
                                              ->whereRaw('MONTH(tanggal) = '.$month)
                                              ->whereRaw('YEAR(tanggal) = '.$year)
                                              ->sum('jumlah');

        $total_debit = DB::table("neraca_awal")->leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                              ->where('data_akun.posisi_normal','Debit')
                                              ->whereRaw('MONTH(tanggal) = '.$month)
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

    public function store(Request $request){

      $validator = Validator::make($request->all(), [
        'tanggal' => 'required',
        'id_data_akun' => 'required',
        'jumlah' => 'required'
        ]);
      if ($validator->fails()) {
        return response()->json(['error'=>$validator->errors()], 401);
      }

      $data = NeracaAwal::create([
              'id_data_akun' => request('id_data_akun'),
              'tanggal' => request('tanggal'),
              'jumlah' => request('jumlah')
              ]);
      $neraca_awal = NeracaAwal::where('neraca_awal.id', $data->id)
                                ->leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
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

    // public function report_all(Request $request){

    //     if($request->from > $request->until){
    //         Alert::error('Oops', 'Input tanggal salah!');
    //         return back()->withInput();
    //     }else{
    //             $list = Jadwal::leftjoin('layanan','jadwal.layanan_id','=','layanan.id')
    //                             ->leftjoin('status','jadwal.status_id','=','status.id')
    //                             ->leftjoin('ruangan','jadwal.ruangan_id','=','ruangan.id')
    //                             ->leftjoin('sesi','jadwal.sesi_id','=','sesi.id')
    //                             ->leftjoin('klien','jadwal.klien_id','=','klien.id')
    //                             ->leftjoin('psikolog','jadwal.psikolog_id','=','psikolog.id')
    //                             ->where('status.nama','=','Selesai')
    //                             ->where('jadwal.tanggal','>=', $request->from = Carbon::parse($request->from))
    //                             ->where('jadwal.tanggal','<=', $request->until = Carbon::parse($request->until))
    //                             ->select('jadwal.*')  
    //                             ->with(['psikolog', 'klien','layanan',  'status', 'ruangan', 'sesi'])
    //                             ->orderBy('layanan.nama')
    //                             ->orderBy('tanggal','asc')
    //                             ->get();
    //                             // dd($list);
    //             if (empty($list[0]->id)) {
    //                 Alert::error('Oops', 'Data tidak tersedia!');
    //                 return back()->withInput();                 
    //             } 

    //             $data = DB::table('jadwal')
    //                 ->leftjoin('layanan','jadwal.layanan_id','=','layanan.id')
    //                 ->leftjoin('status','jadwal.status_id','=','status.id')
    //                 ->where('jadwal.tanggal','>=', $request->from = Carbon::parse($request->from))
    //                 ->where('jadwal.tanggal','<=', $request->until = Carbon::parse($request->until))
    //                 ->select([
    //                     DB::raw('count(layanan_id) as count'),
    //                     DB::raw('layanan.nama as tagh'),
    //                     ])
    //                 ->where('status.nama','=','Selesai')
    //                 ->groupBy('jadwal.layanan_id','layanan.nama')
    //                 ->orderBy('count','desc')
    //                 // ->offset(0)
    //                 ->limit(30)
    //                 ->get();
    //                 // dd($data);

    //             $collection=[];
    //             $collection2 =[];

    //             foreach ($data as $key => $value) {
    //                 $collection[$key] = $value->count;
    //             }
    //             foreach ($data as $key => $value) {
    //                 $collection2[$key] = $value->tagh;
    //             }

    //             $chart = new ECharts;
    //             $chart->labels($collection2);
    //             $chart->dataset('Layanan', 'bar', $collection);
    //             $chart->theme('light');
    //             // $chart->displayAxes(false);

    //             return view('statistik.report_all_layanan',compact('chart','request','layanan','list'));
    //     }

    // }

}
