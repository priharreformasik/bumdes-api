<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Hash;

class UserController extends Controller
{
    public $successStatus = 200;
/**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request){
        if(Auth::attempt([

            'email' => request('email'),
            'password' => request('password')
        ])){
            $user = Auth::user();
            $success['id'] = $user->id;
            $success['nama'] = $user->nama;
            $success['no_telepon'] = $user->no_telepon;
            $success['alamat'] = $user->alamat;
            $success['email'] = $user->email;
            $success['token'] =  $user->createToken('MyApp')-> accessToken;
            return response()->json([
                'status' => 'sukses',
                'user' => $success
            ]);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }
	/**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'alamat' => 'required',
            'no_telepon' => 'required|unique:users',
            // 'c_password' => 'required|same:password',
        ]);
		if ($validator->fails()) {
		            return response()->json(['error'=>$validator->errors()], 401);
		        }
		$input = $request->all();
		        $input['password'] = bcrypt($input['password']);
		        $user = User::create([
                    'nama'=>$request->nama,
                    'no_telepon'=>$request->no_telepon,
                    'alamat'=>$request->alamat,
                    'email'=>$request->email,
                    'password'=>bcrypt($request->password),
                ]);
		        // $success['token'] =  $user->createToken('MyApp')-> accessToken;
		return response()->json([
            'status'=>'success',
            'result'=>$user
            ]);
    }

    public function update(Request $request,$id)
    {
        $this->validate($request, [            
         'nama' => 'required',
         'email' => 'required|email|max:255|unique:users,email,'.$request->id,
         'alamat' => 'required',
         'no_telepon' => 'required|regex:/^[0-9]+$/|max:25',
        ]);

        $data = User::find($id);
        $data->nama=$request->get('nama',$data->nama);
        $data->email=$request->get('email',$data->email);
        $data->alamat=$request->get('alamat');
        $data->no_telepon=$request->get('no_telepon');
        $data->save();


      return response()->json([
        'status'=>'successsssss',
        'result'=> $data ,
      ]);
    }
	
    public function show($id)
    {
      $user = User::find($id);
      return response()->json(['success' => $user]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function update_password_api(Request $request){

     $ubahPassword = User::find(Auth::user()->id);
     if(Hash::check($request->password_lama,$ubahPassword->password)){
       if($request->password_baru == $request->konfirmasi){
         $ubahPassword->password = bcrypt($request->konfirmasi);
         $ubahPassword->save();
         //$status=='success';
         $status['status'] = 'sukses';
         return response()->json(['password'=>$status]);
       }
     }else{
        $status['status'] = 'Password Lama Yang Anda Masukkan Tidak Benar!';
         return response()->json(['password'=>$status]);
     }
   }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this-> successStatus);
    }
}
