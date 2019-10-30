<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataAkun;
use App\Jurnal;
use App\Kwitansi;
use App\NeracaAwal;
use DB;

class NeracaController extends Controller
{
    public function neraca(Request $request)
    {
      if($request->has('month') && $request->has('year')){
        $month = $request->input('month');
        $year = $request->input('year');

        $array = [];

        $id = [3,4,5,6,7,11,12];

        $iterasi = DataAkun::whereIn('id_klasifikasi_akun',$id)->get();

        foreach ($iterasi as $i) { 

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
            $neraca_awal = NeracaAwal::where('id_data_akun','1110')->first();
            if ($neraca_awal == NULL) {
                $kas['nilai_akun'] = NULL;
            } else {
                $kas['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
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
            $neraca_awal = NeracaAwal::where('id_data_akun','1111')->first();
            if ($neraca_awal == NULL) {
                $kas_dibank['nilai_akun'] = NULL;
            } else {
                $kas_dibank['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
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
            $neraca_awal = NeracaAwal::where('id_data_akun','1120')->first();
            if ($neraca_awal == NULL) {
                $piutang['nilai_akun'] = NULL;
            } else {
                $piutang['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
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

        if ($sewa_dibayar['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','1130')->first();
            if ($neraca_awal == NULL) {
                $sewa_dibayar['nilai_akun'] = NULL;
            } else {
                $sewa_dibayar['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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
            $neraca_awal = NeracaAwal::where('id_data_akun','1210')->first();
            if ($neraca_awal == NULL) {
                $tanah['nilai_akun'] = NULL;
            } else {
                $tanah['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
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
            $neraca_awal = NeracaAwal::where('id_data_akun','1220')->first();
            if ($neraca_awal == NULL) {
                $gedung['nilai_akun'] = NULL;
            } else {
                $gedung['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
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

        if ($penyusutan_gedung['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','12201')->first();
            if ($neraca_awal == NULL) {
                $penyusutan_gedung['nilai_akun'] = NULL;
            } else {
                $penyusutan_gedung['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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
            $neraca_awal = NeracaAwal::where('id_data_akun','1230')->first();
            if ($neraca_awal == NULL) {
                $kendaraan['nilai_akun'] = NULL;
            } else {
                $kendaraan['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
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

        if ($penyusutan_kendaraan['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','12301')->first();
            if ($neraca_awal == NULL) {
                $penyusutan_kendaraan['nilai_akun'] = NULL;
            } else {
                $penyusutan_kendaraan['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($kantor['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','1240')->first();
            if ($neraca_awal == NULL) {
                $kantor['nilai_akun'] = NULL;
            } else {
                $kantor['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($penyusutan_kantor['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','12401')->first();
            if ($neraca_awal == NULL) {
                $penyusutan_kantor['nilai_akun'] = NULL;
            } else {
                $penyusutan_kantor['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($utang_dagang['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','2110')->first();
            if ($neraca_awal == NULL) {
                $utang_dagang['nilai_akun'] = NULL;
            } else {
                $utang_dagang['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($utang_gaji['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','2120')->first();
            if ($neraca_awal == NULL) {
                $utang_gaji['nilai_akun'] = NULL;
            } else {
                $utang_gaji['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($utang_bank['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','2130')->first();
            if ($neraca_awal == NULL) {
                $utang_bank['nilai_akun'] = NULL;
            } else {
                $utang_bank['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($obligasi['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','2210')->first();
            if ($neraca_awal == NULL) {
                $obligasi['nilai_akun'] = NULL;
            } else {
                $obligasi['nilai_akun'] = (int) $neraca_awal->jumlah;
            }
        }

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

        if ($saldo_ditahan['nilai_akun'] == NULL) {
            $neraca_awal = NeracaAwal::where('id_data_akun','3110')->first();
            if ($neraca_awal == NULL) {
                $saldo_ditahan['nilai_akun'] = NULL;
            } else {
                $saldo_ditahan['nilai_akun'] = (int) $neraca_awal->jumlah;
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
