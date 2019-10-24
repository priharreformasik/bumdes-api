<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\Jurnal;
use App\Kwitansi;
use App\NeracaAwal;
use DB;

class PerubahanEkuitasController extends Controller
{
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
                                ->first()->toArray();

        if ($modal_disetor['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','3100')->first();
            if ($neraca_awal == NULL) {
                $modal_disetor['nilai_akun'] = NULL;
            } else {
                $modal_disetor['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

        $nilai_akun_disetor = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','3100')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 3100, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_disetor->isEmpty()) {
        } else {
            foreach ($nilai_akun_disetor as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_disetor[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_disetor = $nilai_akun_disetor->last();
            $modal_disetor['nilai_akun'] =  $nilai_akun_disetor->Saldo;
        }

        $saldo_ditahan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','3110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        if ($saldo_ditahan ['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','3110')->first();
            if ($neraca_awal == NULL) {
                $saldo_ditahan ['nilai_akun'] = NULL;
            } else {
                $saldo_ditahan ['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

        $nilai_akun_ditahan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','3110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 3110, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_ditahan->isEmpty()) {
        } else {
            foreach ($nilai_akun_ditahan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_ditahan[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_ditahan = $nilai_akun_ditahan->last();
            $saldo_ditahan['nilai_akun'] =  $nilai_akun_ditahan->Saldo;
        }

        $saldo_berjalan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','3120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        if ($saldo_berjalan['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','3120')->first();
            if ($neraca_awal == NULL) {
                $saldo_berjalan['nilai_akun'] = NULL;
            } else {
                $saldo_berjalan['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }


        $nilai_akun_berjalan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','3120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 3120, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_berjalan->isEmpty()) {
        } else {
            foreach ($nilai_akun_berjalan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_berjalan[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_berjalan = $nilai_akun_berjalan->last();
            $saldo_berjalan['nilai_akun'] =  $nilai_akun_berjalan->Saldo;
        }

        return response()->json([
           'status'=>'success',       
           'MODAL AWAL'=> $modal_disetor,
           'SALDO LABA' =>[$saldo_ditahan,$saldo_berjalan],
           'TOTAL EKUITAS AKHIR PERIODE'=>$modal_disetor['nilai_akun']+$saldo_ditahan['nilai_akun']+$saldo_berjalan['nilai_akun']
         ]);
        }
    }

}
