<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\Jurnal;
use App\Kwitansi;
use App\NeracaAwal;
use DB;

class LabaRugiController extends Controller
{
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

        if ($pendapatan_wisata['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','4110')->first();
            if ($neraca_awal == NULL) {
                $pendapatan_wisata['nilai_akun'] = NULL;
            } else {
                $pendapatan_wisata['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($pendapatan_homestay['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','4120')->first();
            if ($neraca_awal == NULL) {
                $pendapatan_homestay['nilai_akun'] = NULL;
            } else {
                $pendapatan_homestay['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($pendapatan_resto['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','4130')->first();
            if ($neraca_awal == NULL) {
                $pendapatan_resto['nilai_akun'] = NULL;
            } else {
                $pendapatan_resto['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($pendapatan_wisata['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','4140')->first();
            if ($neraca_awal == NULL) {
                $pendapatan_wisata['nilai_akun'] = NULL;
            } else {
                $pendapatan_wisata['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_gaji['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5110')->first();
            if ($neraca_awal == NULL) {
                $biaya_gaji['nilai_akun'] = NULL;
            } else {
                $biaya_gaji['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_listrik['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5120')->first();
            if ($neraca_awal == NULL) {
                $biaya_listrik['nilai_akun'] = NULL;
            } else {
                $biaya_listrik['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_administrasi['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5130')->first();
            if ($neraca_awal == NULL) {
                $biaya_administrasi['nilai_akun'] = NULL;
            } else {
                $biaya_administrasi['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_pemasaran['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5140')->first();
            if ($neraca_awal == NULL) {
                $biaya_pemasaran['nilai_akun'] = NULL;
            } else {
                $biaya_pemasaran['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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
        if ($biaya_perlengkapan['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5150')->first();
            if ($neraca_awal == NULL) {
                $biaya_perlengkapan['nilai_akun'] = NULL;
            } else {
                $biaya_perlengkapan['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_sewa['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5160')->first();
            if ($neraca_awal == NULL) {
                $biaya_sewa['nilai_akun'] = NULL;
            } else {
                $biaya_sewa['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_asuransi['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5170')->first();
            if ($neraca_awal == NULL) {
                $biaya_asuransi['nilai_akun'] = NULL;
            } else {
                $biaya_asuransi['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_gedung['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5180')->first();
            if ($neraca_awal == NULL) {
                $biaya_gedung['nilai_akun'] = NULL;
            } else {
                $biaya_gedung['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_kendaraan['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5190')->first();
            if ($neraca_awal == NULL) {
                $biaya_kendaraan['nilai_akun'] = NULL;
            } else {
                $biaya_kendaraan['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_peralatan['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','5200')->first();
            if ($neraca_awal == NULL) {
                $biaya_peralatan['nilai_akun'] = NULL;
            } else {
                $biaya_peralatan['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($pendapatan_lain['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','6110')->first();
            if ($neraca_awal == NULL) {
                $pendapatan_lain['nilai_akun'] = NULL;
            } else {
                $pendapatan_lain['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($biaya_lain['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','7110')->first();
            if ($neraca_awal == NULL) {
                $biaya_lain['nilai_akun'] = NULL;
            } else {
                $biaya_lain['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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
}