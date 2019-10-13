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

        $saldo_awal = NeracaAwal::leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                ->whereRaw('neraca_awal.id_data_akun = '.$akun)
                                ->whereRaw('YEAR(tanggal) = '.$year)
                                ->select('neraca_awal.jumlah')
                                ->first();

        $buku_besar = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
        						->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
        						->whereRaw('jurnal.id_data_akun = '.$akun)
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                // ->select('jurnal.tanggal','kwitansi.keterangan','data_akun.id','data_akun.nama','jurnal.posisi_normal','jurnal.jumlah')
                                // ->select('jurnal.tanggal', 'kwitansi.keterangan', '@k:=if(jurnal.posisi_normal="k",jumlah,0) as Kredit', '@d:=if(jurnal.posisi_normal="d",jumlah,0) as Debit','@s:=@s+@d-@k as saldo')
                                ->select('jurnal.tanggal','kwitansi.keterangan',
                                    DB::raw('@s:='.$saldo_awal),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                	DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit'),
                                	// DB::raw('@s:=if(neraca_awal.id_data_akun = '.$akun.', neraca_awal.jumlah,0) as Saldo_Awal'),
                                	DB::raw('@s:=@s+@d-@k as saldo')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
                                // dd($buku_besar);
        // $saldo_awal = NeracaAwal::leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
        // 						->whereRaw('neraca_awal.id_data_akun = '.$akun)
        //                         ->whereRaw('YEAR(tanggal) = '.$year)
        //                         ->select('neraca_awal.jumlah')
        //                         ->first();
        
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

    public function laba_rugi(Request $request)
    {
      if($request->has('month') && $request->has('year')){
        $month = $request->input('month');
        $year = $request->input('year');

        $pendapatan_wisata = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','4110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();


        $pendapatan_homestay = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','4120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();

        $pendapatan_resto = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','4130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();

        $pendapatan_event = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','4140')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();

            return response()->json([
           'status'=>'success',
           // 'saldo_awal'=>$saldo_awal,
           // 'saldo_akhie'=>$total_debit,           
           'Pendapatan'=> $pendapatan_wisata,$pendapatan_homestay,$pendapatan_resto,$pendapatan_event
         ]);
        }
    }

    public function perubahan_modal(Request $request)
    {
      if($request->has('month') && $request->has('year')){
        $month = $request->input('month');
        $year = $request->input('year');

        $modal_disetor = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','3100')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();

        $saldo_ditahan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','3110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();

        $saldo_berjalan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','3120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();

        return response()->json([
           'status'=>'success',       
           'MODAL AWAL'=> $modal_disetor,
           'SALDO LABA' =>$saldo_ditahan,$saldo_berjalan
         ]);
        }
    }

    public function neraca(Request $request)
    {
      if($request->has('month') && $request->has('year')){
        $month = $request->input('month');
        $year = $request->input('year');

        $modal_disetor = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','3100')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();

        $saldo_ditahan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','3110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();

        $saldo_berjalan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','3120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->get();

        return response()->json([
           'status'=>'success',       
           // 'Aset Lancar'=> $kas, $kas_dibank, $piutang_dagang, $sewa_dibayar,
           // 'Aset Tetap' => $tanah, $gedung, $akumulasi_penyusutan_gedung, $kendaraan, $akumulasi_penyususan_kendaraan,$peralatan_kantor, $akumulasi_peralatan_kantor,
           // 'Liabilitas Lancar' => $utang_dagang, $utang_gaji, $utang_bank,
           // 'Liabilitas Jangka Panjang' => $obligasi,
           'Modal' => $modal_disetor,$saldo_ditahan,$saldo_berjalan
         ]);
        }
    }
}
