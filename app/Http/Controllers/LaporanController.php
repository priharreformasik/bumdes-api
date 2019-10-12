<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\Jurnal;
use App\Kwitansi;
use App\NeracaAwal;
use DB;


class LaporanController extends Controller
{
    public function buku_besar(Request $request)
    {
      if($request->has('month') && $request->has('year') && $request->has('id_data_akun')){
        $month = $request->input('month');
        $year = $request->input('year');
        $akun = $request->input('id_data_akun');

        $buku_besar = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
        						->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
        						->whereRaw('jurnal.id_data_akun = '.$akun)
                                ->whereRaw('MONTH(tanggal) = '.$month)
                                ->whereRaw('YEAR(tanggal) = '.$year)
                                // ->select('@s:=0')
                                // ->select('jurnal.tanggal','kwitansi.keterangan','data_akun.id','data_akun.nama','jurnal.posisi_normal','jurnal.jumlah')
                                // ->select('jurnal.tanggal', 'kwitansi.keterangan', '@k:=if(jurnal.posisi_normal="k",jumlah,0) as Kredit', '@d:=if(jurnal.posisi_normal="d",jumlah,0) as Debit','@s:=@s+@d-@k as saldo')
                                ->select(
                                	DB::raw('@k:=if(jurnal.posisi_normal="k",jumlah,0) as Kredit'),
                                	DB::raw('@d:=if(jurnal.posisi_normal="d",jumlah,0) as Debit'),
                                	DB::raw('@s:='.$saldo_awal),
                                	DB::raw('@s:=@s+@d-@k as saldo')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
                                // dd($buku_besar);
        $saldo_awal = NeracaAwal::leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
        						->whereRaw('neraca_awal.id_data_akun = '.$akun)
                                ->whereRaw('YEAR(tanggal) = '.$year)
                                ->select('neraca_awal.jumlah')
                                ->first();
        
        // $total_kredit = DB::table("jurnal")->leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
        // 									->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
        //                                     ->where('jurnal.posisi_normal','k')
        //                                     ->whereRaw('MONTH(tanggal) = '.$month)
        //                                     ->whereRaw('YEAR(tanggal) = '.$year)
        //                                     ->sum('jumlah');

        // $total_debit = DB::table("jurnal")->leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
        // 									->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
        //                                     ->where('jurnal.posisi_normal','d')
        //                                     ->whereRaw('MONTH(tanggal) = '.$month)
        //                                     ->whereRaw('YEAR(tanggal) = '.$year)
        //                                     ->sum('jumlah');
        return response()->json([
           'status'=>'success',
           'saldo_awal'=>$saldo_awal,
           // 'saldo_akhie'=>$total_debit,           
           'buku_besar'=> $buku_besar
         ]);

      }elseif (empty($buku_besar[0]->id)) {
        return response()->json([
        'result'=>'Data tidak tersedia' ,
        ]);
      }
      
    }
}
