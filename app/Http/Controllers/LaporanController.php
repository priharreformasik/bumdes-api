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
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
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

        foreach ($buku_besar as $key => $value) {
            if ($key == 0) {
                $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
            } else {
                $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
            }
            $buku_besar[$key]['Saldo'] = $saldo_akhir;
        }


        $total_kredit = DB::table("jurnal")->leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                         ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                            ->where('jurnal.posisi_normal','k')
                                            ->whereRaw('jurnal.id_data_akun = '.$akun)
                                            ->whereRaw('MONTH(tanggal) = '.$month)
                                            ->whereRaw('YEAR(tanggal) = '.$year)
                                            ->sum('jumlah');

        $total_debit = DB::table("jurnal")->leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                         ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                            ->where('jurnal.posisi_normal','d')
                                            ->whereRaw('jurnal.id_data_akun = '.$akun)
                                            ->whereRaw('MONTH(tanggal) = '.$month)
                                            ->whereRaw('YEAR(tanggal) = '.$year)
                                            ->sum('jumlah');

        return response()->json([
           'status'=>'success',
           'saldo_awal'=>$saldo_awal,         
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
                                ->first()->toArray();

        $nilai_akun_wisata = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','4110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 4110, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_wisata->isEmpty()) {
        } else {
            foreach ($nilai_akun_wisata as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_wisata[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_wisata = $nilai_akun_wisata->last();
            $pendapatan_wisata['nilai_akun'] =  $nilai_akun_wisata->Saldo;
        }

        $pendapatan_homestay = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','4120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_homestay = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','4120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 4120, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_homestay->isEmpty()) {
        } else {
            foreach ($nilai_akun_homestay as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_homestay[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_homestay = $nilai_akun_homestay->last();
            $pendapatan_homestay['nilai_akun'] =  $nilai_akun_homestay->Saldo;
        }

        $pendapatan_resto = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','4130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_resto = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','4130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 4130, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_resto->isEmpty()) {
        } else {
            foreach ($nilai_akun_resto as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_resto[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_resto = $nilai_akun_resto->last();
            $pendapatan_resto['nilai_akun'] =  $nilai_akun_resto->Saldo;
        }

        $pendapatan_event = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','4140')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_event = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','4140')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 4140, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')

                                ->get();
       
        if ($nilai_akun_event->isEmpty()) {
        } else {
            foreach ($nilai_akun_event as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_event[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_event = $nilai_akun_event->last();
            $pendapatan_event['nilai_akun'] =  $nilai_akun_event->Saldo;
        }


        $biaya_gaji = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_gaji = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5110, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_gaji->isEmpty()) {
        } else {
            foreach ($nilai_akun_gaji as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_gaji[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_gaji = $nilai_akun_gaji->last();
            $biaya_gaji['nilai_akun'] =  $nilai_akun_gaji->Saldo;
        }

        $biaya_listrik = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_listrik = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5120, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_listrik->isEmpty()) {
        } else {
            foreach ($nilai_akun_listrik as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_listrik[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_listrik = $nilai_akun_listrik->last();
            $biaya_listrik['nilai_akun'] =  $nilai_akun_listrik->Saldo;
        }

        $biaya_administrasi = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_administrasi = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5130, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_administrasi->isEmpty()) {
        } else {
            foreach ($nilai_akun_administrasi as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_administrasi[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_administrasi = $nilai_akun_administrasi->last();
            $biaya_administrasi['nilai_akun'] =  $nilai_akun_administrasi->Saldo;
        }

        $biaya_pemasaran = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5140')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_pemasaran = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5140')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5140, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_pemasaran->isEmpty()) {
        } else {
            foreach ($nilai_akun_pemasaran as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_pemasaran[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_pemasaran = $nilai_akun_pemasaran->last();
            $biaya_pemasaran['nilai_akun'] =  $nilai_akun_pemasaran->Saldo;
        }

        $biaya_perlengkapan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5150')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_perlengkapan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5150')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5150, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_perlengkapan->isEmpty()) {
        } else {
            foreach ($nilai_akun_perlengkapan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_perlengkapan[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_perlengkapan = $nilai_akun_perlengkapan->last();
            $biaya_perlengkapan['nilai_akun'] =  $nilai_akun_perlengkapan->Saldo;
        }

        $biaya_sewa = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5160')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_sewa = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5160')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5160, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_sewa->isEmpty()) {
        } else {
            foreach ($nilai_akun_sewa as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_sewa[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_sewa = $nilai_akun_sewa->last();
            $biaya_sewa['nilai_akun'] =  $nilai_akun_sewa->Saldo;
        }

        $biaya_asuransi = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5170')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_asuransi = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5170')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5170, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_asuransi->isEmpty()) {
        } else {
            foreach ($nilai_akun_asuransi as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_asuransi[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_asuransi = $nilai_akun_asuransi->last();
            $biaya_asuransi['nilai_akun'] =  $nilai_akun_asuransi->Saldo;
        }

        $biaya_gedung = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5180')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_gedung = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5180')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5180, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_gedung->isEmpty()) {
        } else {
            foreach ($nilai_akun_gedung as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_gedung[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_gedung = $nilai_akun_gedung->last();
            $biaya_gedung['nilai_akun'] =  $nilai_akun_gedung->Saldo;
        }

        $biaya_kendaraan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5190')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_kendaraan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5190')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5190, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_kendaraan->isEmpty()) {
        } else {
            foreach ($nilai_akun_kendaraan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_kendaraan[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_kendaraan = $nilai_akun_kendaraan->last();
            $biaya_kendaraan['nilai_akun'] =  $nilai_akun_kendaraan->Saldo;
        }

        $biaya_peralatan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5200')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_peralatan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5200')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5200, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_peralatan->isEmpty()) {
        } else {
            foreach ($nilai_akun_peralatan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_peralatan[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_peralatan = $nilai_akun_peralatan->last();
            $biaya_peralatan['nilai_akun'] =  $nilai_akun_peralatan->Saldo;
        }

        $pendapatan_lain = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','6110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_pendapatan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','6110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 6110, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_pendapatan->isEmpty()) {
        } else {
            foreach ($nilai_akun_pendapatan as $key => $value) {

                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }

                $nilai_akun_pendapatan[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_pendapatan = $nilai_akun_pendapatan->last();
            $pendapatan_lain['nilai_akun'] =  $nilai_akun_pendapatan->Saldo;
        }

        $biaya_lain = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','7110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_biaya = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','7110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 7110, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_biaya->isEmpty()) {
        } else {
            foreach ($nilai_akun_biaya as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_biaya[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_biaya = $nilai_akun_biaya->last();
            $biaya_lain['nilai_akun'] =  $nilai_akun_biaya->Saldo;
        }

        $total_biaya = $biaya_gaji['nilai_akun']+$biaya_listrik['nilai_akun']+$biaya_administrasi['nilai_akun']+$biaya_pemasaran['nilai_akun']+$biaya_perlengkapan['nilai_akun']+$biaya_sewa['nilai_akun']+$biaya_asuransi['nilai_akun']+$biaya_gedung['nilai_akun']+$biaya_kendaraan['nilai_akun']+$biaya_peralatan['nilai_akun'];

        $total_pendapatan = $pendapatan_wisata['nilai_akun']+$pendapatan_homestay['nilai_akun']+$pendapatan_resto['nilai_akun']+$pendapatan_event['nilai_akun'];

        $total_lain = $pendapatan_lain['nilai_akun']+$biaya_lain['nilai_akun'];
        $laba_usaha =  $total_pendapatan - $total_biaya;

        return response()->json([
           'status'=>'success',
           'Pendapatan'=> [$pendapatan_wisata,$pendapatan_homestay,$pendapatan_resto,$pendapatan_event],
           'TOTAL PENDAPATAN'=>$total_pendapatan,
           'Biaya' => [$biaya_gaji, $biaya_listrik, $biaya_administrasi,$biaya_pemasaran,$biaya_perlengkapan,$biaya_sewa,$biaya_asuransi,$biaya_gedung,$biaya_kendaraan,$biaya_peralatan],
           'TOTAL BIAYA'=>$total_biaya,
           'LABA USAHA'=>$laba_usaha,
           'Lain-lain' => [$pendapatan_lain,$biaya_lain],
           'TOTAL LAIN-LAIN'=> $total_lain,
           'SALDO LABA/RUGI BERJALAN'=>$laba_usaha + $total_lain
         ]);

        }


    // public function total_biaya()
    // {
    //     for ($i=5110; $i = 5200 ; $i+10) { 
            
    //     }
    // }

    public function perubahan_modal(Request $request)
    {
      if($request->has('month') && $request->has('year')){
        $month = $request->input('month');
        $year = $request->input('year');

        // $modal = [];

        // for ($i=3100; $i = 3120 ; $i+10) { 
        //     $init = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
        //                         ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
        //                         ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
        //                         ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
        //                         ->where('data_akun.id','=',$i)
        //                         ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
        //                         ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
        //                         ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
        //                         ->orderBy('jurnal.tanggal')
        //                         ->first()->toArray();
        //     $init['nilai_akun'] = NeracaAwal::where('id_data_akun',$i)->first()->jumlah;

        //     $nilai_akun = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
        //                         ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
        //                         ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
        //                         ->where('data_akun.id','=',$i)
        //                         ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
        //                         ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
        //                         ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = '.$i.', neraca_awal.jumlah,0) as Saldo_Awal'),
        //                             DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
        //                             DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
        //                         )
        //                         ->orderBy('jurnal.tanggal')
        //                         ->get();

        //     if ($nilai_akun->isEmpty()) {
        //     } else {
        //     foreach ($nilai_akun as $key => $value) {
        //         if ($key == 0) {
        //             $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
        //         } else {
        //             $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
        //         }
        //         $nilai_akun[$key]['Saldo'] = $saldo_akhir;
        //     }
        //     $nilai_akun = $nilai_akun->last();
        //     $init['nilai_akun'] =  $nilai_akun->Saldo;
        //     }
        //     $modal[] = $init;

        // }

        $modal_disetor = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')

                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();


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

        $biaya_listrik = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();


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

        $biaya_administrasi = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();


        $saldo_berjalan['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','3120')->first()->jumlah;

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

        $biaya_perlengkapan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5150')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_perlengkapan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5150')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5150, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_perlengkapan->isEmpty()) {
        } else {
            foreach ($nilai_akun_perlengkapan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_perlengkapan[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_perlengkapan = $nilai_akun_perlengkapan->last();
            $biaya_perlengkapan['nilai_akun'] =  $nilai_akun_perlengkapan->Saldo;
        }

        $biaya_sewa = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5160')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_sewa = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5160')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5160, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_sewa->isEmpty()) {
        } else {
            foreach ($nilai_akun_sewa as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_sewa[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_sewa = $nilai_akun_sewa->last();
            $biaya_sewa['nilai_akun'] =  $nilai_akun_sewa->Saldo;
        }

        $biaya_asuransi = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5170')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_asuransi = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5170')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5170, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_asuransi->isEmpty()) {
        } else {
            foreach ($nilai_akun_asuransi as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_asuransi[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_asuransi = $nilai_akun_asuransi->last();
            $biaya_asuransi['nilai_akun'] =  $nilai_akun_asuransi->Saldo;
        }

        $biaya_gedung = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5180')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_gedung = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5180')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5180, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_gedung->isEmpty()) {
        } else {
            foreach ($nilai_akun_gedung as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_gedung[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_gedung = $nilai_akun_gedung->last();
            $biaya_gedung['nilai_akun'] =  $nilai_akun_gedung->Saldo;
        }

        $biaya_kendaraan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5190')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_kendaraan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5190')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5190, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_kendaraan->isEmpty()) {
        } else {
            foreach ($nilai_akun_kendaraan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_kendaraan[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_kendaraan = $nilai_akun_kendaraan->last();
            $biaya_kendaraan['nilai_akun'] =  $nilai_akun_kendaraan->Saldo;
        }

        $biaya_peralatan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','5200')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_peralatan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','5200')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 5200, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_peralatan->isEmpty()) {
        } else {
            foreach ($nilai_akun_peralatan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_peralatan[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_peralatan = $nilai_akun_peralatan->last();
            $biaya_peralatan['nilai_akun'] =  $nilai_akun_peralatan->Saldo;
        }

        $pendapatan_lain = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','6110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_pendapatan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','6110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 6110, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_pendapatan->isEmpty()) {
        } else {
            foreach ($nilai_akun_pendapatan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_pendapatan[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_pendapatan = $nilai_akun_pendapatan->last();
            $pendapatan_lain['nilai_akun'] =  $nilai_akun_pendapatan->Saldo;
        }

        $biaya_lain = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','7110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        $nilai_akun_biaya = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','7110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 7110, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_biaya->isEmpty()) {
        } else {
            foreach ($nilai_akun_biaya as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_biaya[$key]['Saldo'] = $saldo_akhir;
            }
            $nilai_akun_biaya = $nilai_akun_biaya->last();
            $biaya_lain['nilai_akun'] =  $nilai_akun_biaya->Saldo;
        }

        $total_biaya = $biaya_gaji['nilai_akun']+$biaya_listrik['nilai_akun']+$biaya_administrasi['nilai_akun']+$biaya_pemasaran['nilai_akun']+$biaya_perlengkapan['nilai_akun']+$biaya_sewa['nilai_akun']+$biaya_asuransi['nilai_akun']+$biaya_gedung['nilai_akun']+$biaya_kendaraan['nilai_akun']+$biaya_peralatan['nilai_akun'];

        $total_pendapatan = $pendapatan_wisata['nilai_akun']+$pendapatan_homestay['nilai_akun']+$pendapatan_resto['nilai_akun']+$pendapatan_event['nilai_akun'];

        $total_lain = $pendapatan_lain['nilai_akun']+$biaya_lain['nilai_akun'];
        $laba_usaha =  $total_pendapatan - $total_biaya;

        return response()->json([
           'status'=>'success',
           'Pendapatan'=> [$pendapatan_wisata,$pendapatan_homestay,$pendapatan_resto,$pendapatan_event],
           'TOTAL PENDAPATAN'=>$total_pendapatan,
           'Biaya' => [$biaya_gaji, $biaya_listrik, $biaya_administrasi,$biaya_pemasaran,$biaya_perlengkapan,$biaya_sewa,$biaya_asuransi,$biaya_gedung,$biaya_kendaraan,$biaya_peralatan],
           'TOTAL BIAYA'=>$total_biaya,
           'LABA USAHA'=>$laba_usaha,
           'Lain-lain' => [$pendapatan_lain,$biaya_lain],
           'TOTAL LAIN-LAIN'=> $total_lain,
           'SALDO LABA/RUGI BERJALAN'=>$laba_usaha + $total_lain
         ]);
        }
    }

    // public function total_biaya()
    // {
    //     for ($i=5110; $i = 5200 ; $i+10) { 
            
    //     }
    // }

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

        if ($saldo_ditahan['nilai_akun'] == NULL) {
            $saldo_ditahan['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','3110')->first()->jumlah;
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

    public function neraca(Request $request)
    {
      if($request->has('month') && $request->has('year')){
        $month = $request->input('month');
        $year = $request->input('year');

        $kas = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','1110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        if ($kas['nilai_akun'] == NULL) {
            $kas['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','1110')->first()->jumlah;
        }

        $nilai_akun_kas = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','1110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 1110, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_kas->isEmpty()) {
        } else {
            foreach ($nilai_akun_kas as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_kas[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_kas = $nilai_akun_kas->last();
            $kas['nilai_akun'] =  $nilai_akun_kas->Saldo;
        }

        $kas_dibank = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','1111')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        if ($kas_dibank['nilai_akun'] == NULL) {
            $kas_dibank['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','1111')->first()->jumlah;
        }

        $nilai_akun_kas_dibank = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','1111')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 1111, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_kas_dibank->isEmpty()) {
        } else {
            foreach ($nilai_akun_kas_dibank as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_kas_dibank[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_kas_dibank = $nilai_akun_kas_dibank->last();
            $kas_dibank['nilai_akun'] =  $nilai_akun_kas_dibank->Saldo;
        }

        $piutang = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','1120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        if ($piutang['nilai_akun'] == NULL) {
            $piutang['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','1120')->first()->jumlah;
        }

        $nilai_akun_piutang = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','1120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 1120, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_piutang->isEmpty()) {
        } else {
            foreach ($nilai_akun_piutang as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_piutang[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_piutang = $nilai_akun_piutang->last();
            $piutang['nilai_akun'] =  $nilai_akun_piutang->Saldo;
        }

        $sewa_dibayar = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','1130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        // if ($sewa_dibayar['nilai_akun'] == NULL) {
        //     $sewa_dibayar['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','1130')->first()->jumlah;
        // }

        $nilai_akun_sewa = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','1130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 1130, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_sewa->isEmpty()) {
        } else {
            foreach ($nilai_akun_sewa as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_sewa[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_sewa = $nilai_akun_sewa->last();
            $sewa_dibayar['nilai_akun'] =  $nilai_akun_sewa->Saldo;
        }

        $total_aset_lancar = $kas['nilai_akun']+$kas_dibank['nilai_akun']+$piutang['nilai_akun']+$sewa_dibayar['nilai_akun'];

        $tanah = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','1210')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        if ($tanah['nilai_akun'] == NULL) {
            $tanah['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','1210')->first()->jumlah;
        }

        $nilai_akun_tanah = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','1210')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 1210, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_tanah->isEmpty()) {
        } else {
            foreach ($nilai_akun_tanah as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_tanah[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_tanah = $nilai_akun_tanah->last();
            $tanah['nilai_akun'] =  $nilai_akun_tanah->Saldo;
        }

        $gedung = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','1220')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        if ($gedung['nilai_akun'] == NULL) {
            $gedung['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','1220')->first()->jumlah;
        }

        $nilai_akun_gedung = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','1220')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 1220, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_gedung->isEmpty()) {
        } else {
            foreach ($nilai_akun_gedung as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_gedung[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_gedung = $nilai_akun_gedung->last();
            $gedung['nilai_akun'] =  $nilai_akun_gedung->Saldo;
        }


        $penyusutan_gedung = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','12201')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        // if ($penyusutan_gedung['nilai_akun'] == NULL) {
        //     $penyusutan_gedung['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','12201')->first()->jumlah;
        // }

        $nilai_akun_penyusutan_gedung = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','12201')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 12201, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_penyusutan_gedung->isEmpty()) {
        } else {
            foreach ($nilai_akun_penyusutan_gedung as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_penyusutan_gedung[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_penyusutan_gedung = $nilai_akun_penyusutan_gedung->last();
            $penyusutan_gedung['nilai_akun'] =  $nilai_akun_penyusutan_gedung->Saldo;
        }

        $kendaraan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','1230')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        if ($kendaraan['nilai_akun'] == NULL) {
            $kendaraan['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','1230')->first()->jumlah;
        }

        $nilai_akun_kendaraan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','1230')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 1230, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_kendaraan->isEmpty()) {
        } else {
            foreach ($nilai_akun_kendaraan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_kendaraan[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_kendaraan = $nilai_akun_kendaraan->last();
            $kendaraan['nilai_akun'] =  $nilai_akun_kendaraan->Saldo;
        }


        $penyusutan_kendaraan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','12301')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        // if ($penyusutan_kendaraan['nilai_akun'] == NULL) {
        //     $penyusutan_kendaraan['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','12301')->first()->jumlah;
        // }

        $nilai_akun_penyusutan_kendaraan = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','12301')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 12301, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_penyusutan_kendaraan->isEmpty()) {
        } else {
            foreach ($nilai_akun_penyusutan_kendaraan as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_penyusutan_kendaraan[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_penyusutan_kendaraan = $nilai_akun_penyusutan_kendaraan->last();
            $penyusutan_kendaraan['nilai_akun'] =  $nilai_akun_penyusutan_kendaraan->Saldo;
        }

        $kantor = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','1240')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        // if ($kantor['nilai_akun'] == NULL) {
        //     $kantor['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','1240')->first()->jumlah;
        // }

        $nilai_akun_kantor = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','1240')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 1240, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_kantor->isEmpty()) {
        } else {
            foreach ($nilai_akun_kantor as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_kantor[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_kantor = $nilai_akun_kantor->last();
            $kantor['nilai_akun'] =  $nilai_akun_kantor->Saldo;
        }


        $penyusutan_kantor = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','12401')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        // if ($penyusutan_kantor['nilai_akun'] == NULL) {
        //     $penyusutan_kantor['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','12401')->first()->jumlah;
        // }

        $nilai_akun_penyusutan_kantor = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','12401')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 12401, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_penyusutan_kantor->isEmpty()) {
        } else {
            foreach ($nilai_akun_penyusutan_kantor as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_penyusutan_kantor[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_penyusutan_kantor = $nilai_akun_penyusutan_kantor->last();
            $penyusutan_kantor['nilai_akun'] =  $nilai_akun_penyusutan_kantor->Saldo;
        }

        $utang_dagang = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','2110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        // if ($utang_dagang['nilai_akun'] == NULL) {
        //     $utang_dagang['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','2110')->first()->jumlah;
        // }

        $nilai_akun_utang_dagang = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','2110')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 2110, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_utang_dagang->isEmpty()) {
        } else {
            foreach ($nilai_akun_utang_dagang as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_utang_dagang[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_utang_dagang = $nilai_akun_utang_dagang->last();
            $utang_dagang['nilai_akun'] =  $nilai_akun_utang_dagang->Saldo;
        }



        $utang_gaji = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','2120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        // if ($utang_gaji['nilai_akun'] == NULL) {
        //     $utang_gaji['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','2120')->first()->jumlah;
        // }

        $nilai_akun_utang_gaji = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','2120')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 2120, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_utang_gaji->isEmpty()) {
        } else {
            foreach ($nilai_akun_utang_gaji as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_utang_gaji[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_utang_gaji = $nilai_akun_utang_gaji->last();
            $utang_gaji['nilai_akun'] =  $nilai_akun_utang_gaji->Saldo;
        }

        $utang_bank = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','2130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        // if ($utang_bank['nilai_akun'] == NULL) {
        //     $utang_bank['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','2130')->first()->jumlah;
        // }

        $nilai_akun_utang_bank = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','2130')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 2130, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_utang_bank->isEmpty()) {
        } else {
            foreach ($nilai_akun_utang_bank as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_utang_bank[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_utang_bank = $nilai_akun_utang_bank->last();
            $utang_bank['nilai_akun'] =  $nilai_akun_utang_bank->Saldo;
        }

        $obligasi = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id','=','2210')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                ->orderBy('jurnal.tanggal')
                                ->first()->toArray();

        // if ($obligasi['nilai_akun'] == NULL) {
        //     $obligasi['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','2210')->first()->jumlah;
        // }

        $nilai_akun_obligasi = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                ->where('data_akun.id','=','2210')
                                ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = 2210, neraca_awal.jumlah,0) as Saldo_Awal'),
                                    DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                    DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                )
                                ->orderBy('jurnal.tanggal')
                                ->get();
       
        if ($nilai_akun_obligasi->isEmpty()) {
        } else {
            foreach ($nilai_akun_obligasi as $key => $value) {
                if ($key == 0) {
                    $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                } else {
                    $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                }
                $nilai_akun_obligasi[$key]['Saldo'] = $saldo_akhir;
            }

            $nilai_akun_obligasi = $nilai_akun_obligasi->last();
            $obligasi['nilai_akun'] =  $nilai_akun_obligasi->Saldo;
        }

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

        if ($saldo_ditahan['nilai_akun'] == NULL) {
            $saldo_ditahan['nilai_akun'] = (int) NeracaAwal::where('id_data_akun','3110')->first()->jumlah;
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


        $total_aset_tetap = $tanah['nilai_akun']+$gedung['nilai_akun']+$penyusutan_gedung['nilai_akun']+$kendaraan['nilai_akun']+$penyusutan_kendaraan['nilai_akun']+$kantor['nilai_akun']+$penyusutan_kantor['nilai_akun'];

        $total_liabilitas_lancar = $utang_dagang['nilai_akun']+$utang_gaji['nilai_akun']+$utang_bank['nilai_akun'];

        $total_aset = $total_aset_tetap + $total_aset_lancar;
        $total_liabilitas = $total_liabilitas_lancar + $obligasi['nilai_akun'];
        $total_ekuitas=$modal_disetor['nilai_akun']+$saldo_ditahan['nilai_akun']+$saldo_berjalan['nilai_akun'];
        return response()->json([
           'status'=>'success',       
           'Aset Lancar'=> [$kas, $kas_dibank,$piutang, $sewa_dibayar],
           'Total Aset Lancar' =>$total_aset_lancar,
           'Aset Tetap' => [$tanah, $gedung, $penyusutan_gedung, $kendaraan, $penyusutan_kendaraan,$kantor, $penyusutan_kantor],
           'Total Aset Tetap' =>$total_aset_tetap,
           'Liabilitas Lancar' => [$utang_dagang, $utang_gaji, $utang_bank],
            'Total Liabilitas Lancar' =>$total_liabilitas_lancar,
           'Liabilitas Jangka Panjang' => $obligasi,
           'Total Aset'=>$total_aset,
           'Total Liabilitas'=>$total_liabilitas,
           'Total Ekuitas'=>$total_ekuitas,
           'Total Liabilitas dan Ekuitas'=>$total_liabilitas+$total_ekuitas
         ]);
        }

    }
}
