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
         'neraca_awal'=> $neraca_awal 
       ]);
    }

    public function store(Request $request){

      $data = NeracaAwal::create([
              'id_data_akun' => request('id_data_akun'),
              'tanggal' => request('tanggal'),
              'jumlah' => request('jumlah')
              ]);
      return response()->json([
        'status'=>'success',
        'result'=>$data
      ]); 
    }

    public function update(Request $request,$id)
    {
        $data = NeracaAwal::find($id);
        $data->id_data_akun=$request->get('id_data_akun');
        $data->tanggal=$request->get('tanggal');        
        $data->jumlah=$request->get('jumlah');
        $data->save();
      return response()->json([
        'status'=>'successsssss',
        'result'=> $data ,
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
