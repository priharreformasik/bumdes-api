<?php

namespace App\Http\Controllers;
use App\Http\Controllers\LabaRugiController;
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
        $array = [];

        $id = [3,4,5,6,7];

        $iterasi = DataAkun::whereIn('id_klasifikasi_akun',$id)->get();

        foreach ($iterasi as $i) { 
            $data = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
                                ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
                                ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
                                ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
                                ->where('data_akun.id',$i->id)
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
                                    ->where('data_akun.id',$i->id)
                                    ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
                                    ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
                                    ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = '.$i->id.', neraca_awal.jumlah,0) as Saldo_Awal'),
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


        $total_pendapatan = $array[3]['nilai_akun']+$array[4]['nilai_akun']+$array[5]['nilai_akun']+$array[6]['nilai_akun'];
        $total_biaya = $array[7]['nilai_akun']+$array[8]['nilai_akun']+$array[9]['nilai_akun']+$array[10]['nilai_akun']+$array[8]['nilai_akun']+$array[9]['nilai_akun']+$array[10]['nilai_akun']+$array[11]['nilai_akun']+$array[12]['nilai_akun']+$array[11]['nilai_akun'];
        $total_lain = $array[12]['nilai_akun']+$array[13]['nilai_akun'];
        $laba_usaha = $total_pendapatan - $total_biaya;
        $saldo_laba_rugi = $laba_usaha + $total_lain;
        
        return response()->json([
           'status'=>'success',       
           'MODAL AWAL'=> $array[0],
           'SALDO LABA' =>[$array[1], 'Saldo laba tahun berjalan'=> $saldo_laba_rugi],
           'TOTAL EKUITAS AKHIR PERIODE'=>$array[0]['nilai_akun']+$array[1]['nilai_akun']+$saldo_laba_rugi
         ]);
        }
    }

}



// <?php

// namespace App\Http\Controllers;
// use App\Http\Controllers\LabaRugiController;
// use Illuminate\Http\Request;
// use App\DataAkun;
// use App\Jurnal;
// use App\Kwitansi;
// use App\NeracaAwal;
// use DB;

// class PerubahanEkuitasController extends Controller
// {
//     public function perubahan_modal(Request $request)
//     {
//       if($request->has('month') && $request->has('year')){
//         $month = $request->input('month');
//         $year = $request->input('year');
//         $array = [];

//         $id = [3,4,5,6,7];

//         $iterasi = DataAkun::whereIn('id_klasifikasi_akun',$id)->get();

//         foreach ($iterasi as $i) { 
//             $data = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
//                                 ->leftjoin('klasifikasi_akun','klasifikasi_akun.id','=','data_akun.id_klasifikasi_akun')
//                                 ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
//                                 ->leftjoin('neraca_awal','neraca_awal.id','=','jurnal.id_neraca_awal')
//                                 ->where('data_akun.id',$i->id)
//                                 ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
//                                 ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
//                                 ->select('data_akun.id_klasifikasi_akun','data_akun.nama','data_akun.id as no_akun',DB::raw('sum(jurnal.jumlah) AS nilai_akun'))
//                                 ->orderBy('jurnal.tanggal')
//                                 ->first()->toArray();

//             if ($data['nilai_akun'] == NULL) {
//                 $neraca_awal = NeracaAwal::where('id_data_akun',$i->id)->first();
//                 if ($neraca_awal == NULL) {
//                     $data['nilai_akun'] = NULL;
//                 } else {
//                     $data['nilai_akun'] = (int) $neraca_awal->jumlah;
//                 }
//             }

//             $nilai_akun_data = Jurnal::leftjoin('data_akun','data_akun.id','=','jurnal.id_data_akun')
//                                     ->leftjoin('kwitansi','kwitansi.id','=','jurnal.id_kwitansi')
//                                     ->leftjoin('neraca_awal','neraca_awal.id_data_akun','=','jurnal.id_data_akun')
//                                     ->where('data_akun.id',$i->id)
//                                     ->whereRaw('MONTH(jurnal.tanggal) = '.$month)
//                                     ->whereRaw('YEAR(jurnal.tanggal) = '.$year)
//                                     ->select(DB::raw('@s:=if(neraca_awal.id_data_akun = '.$i->id.', neraca_awal.jumlah,0) as Saldo_Awal'),
//                                         DB::raw('@d:=if(jurnal.posisi_normal="d",jurnal.jumlah,0) as Debit'),
//                                         DB::raw('@k:=if(jurnal.posisi_normal="k",jurnal.jumlah,0) as Kredit')
//                                     )
//                                     ->orderBy('jurnal.tanggal')
//                                     ->get();
        
//             if ($nilai_akun_data->isEmpty()) {
//             } else {
//                 foreach ($nilai_akun_data as $key => $value) {
//                     if ($key == 0) {
//                         $saldo_akhir = $value->Saldo_Awal+$value->Debit-$value->Kredit;
//                     } else {
//                         $saldo_akhir = $saldo_akhir+$value->Debit-$value->Kredit;
//                     }
//                     $nilai_akun_data[$key]['Saldo'] = $saldo_akhir;
//                 }

//                 $nilai_akun_data = $nilai_akun_data->last();
//                 $data['nilai_akun'] =  $nilai_akun_data->Saldo;
//             }

//             $array[] = $data;
//         }

//         $id_total_pendapatan = array_keys(array_column($array, 'id_klasifikasi_akun'), 4);
//         $total_pendapatan = 0;
//         foreach ($id_total_pendapatan as $key => $value) {
//             $total_pendapatan = $total_pendapatan + $array[$value]['nilai_akun'];
//         }

//         $id_total_pendapatan = array_keys(array_column($array, 'id_klasifikasi_akun'), 4);
//         $list_pendapatan = [];
//         foreach ($id_total_pendapatan as $key => $value) {
//             $list_pendapatan[] = $array[$value];
//         }

//         $total_biaya = $array[7]['nilai_akun']+$array[8]['nilai_akun']+$array[9]['nilai_akun']+$array[10]['nilai_akun']+$array[8]['nilai_akun']+$array[9]['nilai_akun']+$array[10]['nilai_akun']+$array[11]['nilai_akun']+$array[12]['nilai_akun']+$array[11]['nilai_akun'];
//         $total_lain = $array[12]['nilai_akun']+$array[13]['nilai_akun'];
//         $laba_usaha = $total_pendapatan - $total_biaya;
//         $saldo_laba_rugi = $laba_usaha + $total_lain;
        
//         return response()->json($list_pendapatan);
//         }
//     }

// }
