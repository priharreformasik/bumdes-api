<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\Jurnal;
use App\Kwitansi;
use DB;

class JurnalController extends Controller
{
    public function show(Request $request)
    {
      if($request->has('month') && $request->has('year')){
        $month = $request->input('month');
        $year = $request->input('year');
        $jurnal = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
        						    ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                        ->whereRaw('MONTH(tanggal) = '.$month)
                        ->whereRaw('YEAR(tanggal) = '.$year)
                        ->select('jurnal.tanggal','kwitansi.no_kwitansi','kwitansi.keterangan','data_akun.id','data_akun.nama','jurnal.posisi_normal','jurnal.jumlah')
                        ->orderBy('jurnal.tanggal')
                        ->orderBy('data_akun.id')
                        ->get();
        $total_kredit = DB::table("jurnal")->leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
        									->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                          ->where('jurnal.posisi_normal','k')
                          ->whereRaw('MONTH(tanggal) = '.$month)
                          ->whereRaw('YEAR(tanggal) = '.$year)
                          ->sum('jumlah');

        $total_debit = DB::table("jurnal")->leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
        									->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                          ->where('jurnal.posisi_normal','d')
                          ->whereRaw('MONTH(tanggal) = '.$month)
                          ->whereRaw('YEAR(tanggal) = '.$year)
                          ->sum('jumlah');
        return response()->json([
           'status'=>'success',
           'jurnal'=> $jurnal,
           'total_kredit'=>$total_kredit,
           'total_debit'=>$total_debit
         ]);

      }elseif (empty($jurnal[0]->id)) {
        return response()->json([
        'result'=>'Data tidak tersedia' ,
        ]);
      }
      
    }

    public function store(Request $request){

	     $this->validate($request, [
	      'no_kwitansi' => 'required',
	      'keterangan' => 'required',
	      'id_data_akun' => 'required',
	      'tanggal' => 'required',
	      'jumlah' => 'required',
	      'posisi_normal' => 'required',
	    ],
	    [
	      'no_kwitansi.required' => 'No kwitansi tidak boleh kosong!',
	      'keterangan.required' => 'Keterangan tidak boleh kosong!',
	      'id_data_akun.required' => 'Data akun sudah digunakan!',
	      'tanggal.required' => 'Tanggal tidak boleh kosong!',
	      'jumlah.required' => 'Jumlah tidak boleh kosong!',
	      'posisi_normal.required' => 'Posisi normal tidak boleh kosong!',
	    ]);
		$data = Kwitansi::create([			
		          'no_kwitansi'=>$request->no_kwitansi,
		          'keterangan'=>$request->keterangan,
              ])->jurnal()->create([
		          'id_data_akun' => request('id_data_akun'),
	              'tanggal' => request('tanggal'),
	              'jumlah' => request('jumlah'),
	              'posisi_normal' => request('posisi_normal')
		      ]);
		$jurnal = Kwitansi::where('id',$data->id_kwitansi)->with('jurnal.data_akun')->first();
		// dd($jurnal);

      return response()->json([
        'status'=>'success',
        'result'=>$jurnal
      ]); 
    }

    public function destroy($id)
    {
      $data = Jurnal::find($id)->delete();
      return response()->json([
        'success'=>"Data Deleted successfully."
      ]);
    }
}
