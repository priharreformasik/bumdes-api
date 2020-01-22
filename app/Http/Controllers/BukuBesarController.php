<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\Jurnal;
use App\Kwitansi;
use App\NeracaAwal;
use DB;
use Auth;


class BukuBesarController extends Controller
{
    public function buku_besar(Request $request)
    {
      if($request->has('month') && $request->has('year') && $request->has('id_data_akun')){
        $month = $request->input('month');
        $year = $request->input('year');
        $akun = $request->input('id_data_akun');

        $saldo_awal = NeracaAwal::leftjoin('data_akun','data_akun.id','=','neraca_awal.id_data_akun')
                                ->where('neraca_awal.created_by', Auth::user()->id)
                                ->whereRaw('neraca_awal.id_data_akun = '.$akun)
                                ->whereRaw('YEAR(tanggal) = '.$year)
                                ->select('neraca_awal.jumlah')
                                ->first();

        $buku_besar = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')                 
                                ->where('jurnal.created_by', Auth::user()->id)
                                ->whereRaw('jurnal.id_data_akun = '.$akun)
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('jurnal.id','jurnal.tanggal','kwitansi.keterangan',
                                    DB::raw('@s:=if(neraca_awal.id_data_akun ='.$akun.', neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->groupBy('id')
                                ->get();
        
        $saldo_akhir = 0;
        if ($buku_besar->isEmpty()) {
            $buku_besar[] = ['Saldo Awal' => (int) NeracaAwal::where('id_data_akun',$akun)->where('created_by', Auth::id())
                                                            ->first()->jumlah];
        } else {
            foreach ($buku_besar as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $buku_besar[$key]['Saldo'] = $saldo_akhir;
            }
        }
        $total_kredit = (int) Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                            ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                            ->where('jurnal.posisi_normal','k')
                                            ->whereRaw('jurnal.id_data_akun = '.$akun)
                                            ->whereRaw('MONTH(tanggal) = '.$month)
                                            ->whereRaw('YEAR(tanggal) = '.$year)
                                            ->where('jurnal.created_by', Auth::id())
                                            ->sum('jumlah');

        $total_debit = (int) Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                         ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                            ->where('jurnal.posisi_normal','d')
                                            ->whereRaw('jurnal.id_data_akun = '.$akun)
                                            ->whereRaw('MONTH(tanggal) = '.$month)
                                            ->whereRaw('YEAR(tanggal) = '.$year)
                                            ->where('jurnal.created_by', Auth::id())
                                            ->sum('jumlah');

        return response()->json([
           'status'=>'success',
           'saldo_awal'=> $saldo_awal,         
           'buku_besar'=> $buku_besar,
           'total_kredit' => $total_kredit,
           'total_debit' =>$total_debit
         ]);

      }elseif (empty($buku_besar[0]->id)) {
        return response()->json([
        'result'=>'Data tidak tersedia' ,
        ]);
      }
      
    }
}