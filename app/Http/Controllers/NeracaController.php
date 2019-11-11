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

            $id = [3,4,5,6,7,11,12,21,22];

            $iterasi = DataAkun::whereIn('id_klasifikasi_akun',$id)->get();

            foreach ($iterasi as $i) { 

            $data = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                    ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                    ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                    ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                    ->where('data_akun.id','=',$i->id)
                                    ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                    ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                    ->select('data_akun.id_klasifikasi_akun','data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
                                    ->orderBy('jurnal.tanggal')
                                    ->first()->toArray();

            if ($data['nilai_akun'] == NULL) {
                $neraca_awal = NeracaAwal::where('id_data_akun',$i->id)->first();
                if ($neraca_awal == NULL) {
                    $data['nilai_akun'] = NULL;
                } else {
                    $data['nilai_akun'] = (int) $neraca_awal->jumlah;
                }
            }

            $nilai_akun_data = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                    ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                    ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
                                    ->where('data_akun.id','=',$i->id)
                                    ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                    ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                    ->select(DB::raw('@s:=if(neraca_awal.id_data_akun ='.$i->id.', neraca_awal.jumlah,0) as Saldo_Awal'),
                                        DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
                                        DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
                                    )
                                    ->orderBy('jurnal.tanggal')
                                    ->get();
           
            if ($nilai_akun_data->isEmpty()) {
            } else {
                foreach ($nilai_akun_data as $key => $value) {
                    if ($key == 0) {
                        $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
                    } else {
                        $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
                    }
                    $nilai_akun_data[$key]['Saldo'] = $saldo_akhir;
                }
                $nilai_akun_data = $nilai_akun_data->last();
                $data['nilai_akun'] =  $nilai_akun_data->Saldo;
            }

            $array[] = $data;

        }

        $id_total_pendapatan = array_keys(array_column($array, 'id_klasifikasi_akun'), 4);
        $total_pendapatan = 0;
        foreach ($id_total_pendapatan as $key => $value) {
            $total_pendapatan = $total_pendapatan + $array[$value]['nilai_akun'];
        }

        $id_total_pendapatan = array_keys(array_column($array, 'id_klasifikasi_akun'), 4);
        $list_pendapatan = [];
        foreach ($id_total_pendapatan as $key => $value) {
            $list_pendapatan[] = $array[$value];
        }

        $id_total_biaya = array_keys(array_column($array, 'id_klasifikasi_akun'), 5);
        $total_biaya = 0;
        foreach ($id_total_biaya as $key => $value) {
            $total_biaya = $total_biaya + $array[$value]['nilai_akun'];
        }

        $id_total_biaya = array_keys(array_column($array, 'id_klasifikasi_akun'), 5);
        $list_biaya = [];
        foreach ($id_total_biaya as $key => $value) {
            $list_biaya[] = $array[$value];
        }

        $id_total_pendapatan_lain = array_keys(array_column($array, 'id_klasifikasi_akun'), 6);
        $total_pendapatan_lain = 0;
        foreach ($id_total_pendapatan_lain as $key => $value) {
            $total_pendapatan_lain = $total_pendapatan_lain + $array[$value]['nilai_akun'];
        }

        $id_total_pendapatan_lain = array_keys(array_column($array, 'id_klasifikasi_akun'), 6);
        $list_pendapatan_lain = [];
        foreach ($id_total_pendapatan_lain as $key => $value) {
            $list_pendapatan_lain[] = $array[$value];
        }

        $id_total_biaya_lain = array_keys(array_column($array, 'id_klasifikasi_akun'), 7);
        $total_biaya_lain = 0;
        foreach ($id_total_biaya_lain as $key => $value) {
            $total_biaya_lain = $total_biaya_lain + $array[$value]['nilai_akun'];
        }

        $id_total_biaya_lain = array_keys(array_column($array, 'id_klasifikasi_akun'), 7);
        $list_biaya_lain = [];
        foreach ($id_total_biaya_lain as $key => $value) {
            $list_biaya_lain[] = $array[$value];
        }

        $total_lain = $total_pendapatan_lain - $total_biaya_lain;
        $laba_usaha = $total_pendapatan - $total_biaya;
        $saldo_laba_rugi = $laba_usaha + $total_lain;

        $id_aset_lancar = array_keys(array_column($array, 'id_klasifikasi_akun'), 11);
        $total_aset_lancar = 0;
        foreach ($id_aset_lancar as $key => $value) {
            $total_aset_lancar = $total_aset_lancar + $array[$value]['nilai_akun'];
        }

        $id_total_aset_lancar = array_keys(array_column($array, 'id_klasifikasi_akun'), 11);
        $list_aset_lancar = [];
        foreach ($id_total_aset_lancar as $key => $value) {
            $list_aset_lancar[] = $array[$value];
        }

        $id_aset_tetap = array_keys(array_column($array, 'id_klasifikasi_akun'), 12);
        $total_aset_tetap = 0;
        foreach ($id_aset_tetap as $key => $value) {
            $total_aset_tetap = $total_aset_tetap + $array[$value]['nilai_akun'];
        }

        $id_total_aset_tetap = array_keys(array_column($array, 'id_klasifikasi_akun'), 12);
        $list_aset_tetap = [];
        foreach ($id_total_aset_tetap as $key => $value) {
            $list_aset_tetap[] = $array[$value];
        }

        $id_liabilitas_lancar = array_keys(array_column($array, 'id_klasifikasi_akun'), 21);
        $total_liabilitas_lancar = 0;
        foreach ($id_liabilitas_lancar as $key => $value) {
            $total_liabilitas_lancar = $total_liabilitas_lancar + $array[$value]['nilai_akun'];
        }

        $id_total_liabilitas_lancar = array_keys(array_column($array, 'id_klasifikasi_akun'), 21);
        $list_liabilitas_lancar = [];
        foreach ($id_total_liabilitas_lancar as $key => $value) {
            $list_liabilitas_lancar[] = $array[$value];
        }

        $id_liabilitas_jangka_panjang = array_keys(array_column($array, 'id_klasifikasi_akun'), 22);
        $total_liabilitas_jangka_panjang = 0;
        foreach ($id_liabilitas_jangka_panjang as $key => $value) {
            $total_liabilitas_jangka_panjang = $total_liabilitas_jangka_panjang + $array[$value]['nilai_akun'];
        }

        $id_total_liabilitas_jangka_panjang = array_keys(array_column($array, 'id_klasifikasi_akun'), 22);
        $list_liabilitas_jangka_panjang = [];
        foreach ($id_total_liabilitas_jangka_panjang as $key => $value) {
            $list_liabilitas_jangka_panjang[] = $array[$value];
        }

        $total_ekuitas=$array[12]['nilai_akun']+$array[13]['nilai_akun']+$saldo_laba_rugi;

        $total_aset = $total_aset_tetap + $total_aset_lancar;
        $total_liabilitas = $total_liabilitas_lancar +  $total_liabilitas_jangka_panjang ;


        return response()->json([
            'status'=>'success',       
            'Aset Lancar'=> $list_aset_lancar,
            'Total Aset Lancar' =>$total_aset_lancar,
            'Aset Tetap' => $list_aset_tetap,
            'Total Aset Tetap' =>$total_aset_tetap,
            'Liabilitas Lancar' => $list_liabilitas_lancar,
            'Total Liabilitas Lancar' =>$total_liabilitas_lancar,
            'Liabilitas Jangka Panjang' => $list_liabilitas_jangka_panjang,
            'Total Liabilitas Jangka Panjang' => $total_liabilitas_jangka_panjang,
            'EKUITAS' =>['Modal disetor'=>$array[12],'Saldo laba ditahan'=>$array[13], 'Saldo laba tahun berjalan'=> $saldo_laba_rugi],
            'Total Aset'=>$total_aset,
            'Total Liabilitas'=>$total_liabilitas,
            'Total Ekuitas'=>$total_ekuitas,
            'Total Liabilitas dan Ekuitas'=>$total_liabilitas+$total_ekuitas
         ]);
        }

    }
}
