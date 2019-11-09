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
                                    ->select('data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
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

        $total_pendapatan = $array[15]['nilai_akun']+$array[16]['nilai_akun']+$array[17]['nilai_akun']+$array[18]['nilai_akun'];
        $total_biaya = $array[19]['nilai_akun']+$array[20]['nilai_akun']+$array[21]['nilai_akun']+$array[22]['nilai_akun']+$array[23]['nilai_akun']+$array[24]['nilai_akun']+$array[25]['nilai_akun']+$array[26]['nilai_akun']+$array[27]['nilai_akun']+$array[28]['nilai_akun'];
        $total_lain = $array[29]['nilai_akun']+$array[30]['nilai_akun'];
        $laba_usaha = $total_pendapatan - $total_biaya;
        $saldo_laba_rugi = $laba_usaha + $total_lain;   

        $total_aset_lancar = $array[0]['nilai_akun']+$array[1]['nilai_akun']+$array[2]['nilai_akun']+$array[3]['nilai_akun'];

        $total_aset_tetap = $array[4]['nilai_akun']+$array[5]['nilai_akun']+$array[31]['nilai_akun']+$array[6]['nilai_akun']+$array[32]['nilai_akun']+$array[7]['nilai_akun']+$array[33]['nilai_akun'];

        $total_liabilitas_lancar = $array[8]['nilai_akun']+$array[9]['nilai_akun']+$array[10]['nilai_akun'];
        $total_liabilitas_jangka_panjang  = $array[11]['nilai_akun'];

        $total_ekuitas=$array[12]['nilai_akun']+$array[13]['nilai_akun']+$saldo_laba_rugi;

        $total_aset = $total_aset_tetap + $total_aset_lancar;
        $total_liabilitas = $total_liabilitas_lancar +  $total_liabilitas_jangka_panjang ;


        return response()->json([
            'status'=>'success',       
            'Aset Lancar'=> [$array[0], $array[1],$array[2], $array[3]],
            'Total Aset Lancar' =>$total_aset_lancar,
            'Aset Tetap' => [$array[4], $array[5],$array[31], $array[6],$array[32], $array[7],$array[33]],
            'Total Aset Tetap' =>$total_aset_tetap,
            'Liabilitas Lancar' => [$array[8], $array[9], $array[10]],
            'Total Liabilitas Lancar' =>$total_liabilitas_lancar,
            'Liabilitas Jangka Panjang' => $array[11],
            'Total Liabilitas Jangka Panjang' => $array[11],
            'EKUITAS' =>['Modal disetor'=>$array[12],'Saldo laba ditahan'=>$array[13], 'Saldo laba tahun berjalan'=> $saldo_laba_rugi],
            'Total Aset'=>$total_aset,
            'Total Liabilitas'=>$total_liabilitas,
            'Total Ekuitas'=>$total_ekuitas,
            'Total Liabilitas dan Ekuitas'=>$total_liabilitas+$total_ekuitas
         ]);
        }

    }
}
