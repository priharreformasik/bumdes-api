<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth; 

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
            'password' => request('password'),
        ])){ 
            $user = Auth::user(); 
            $nama = $request->get('nama');
            $no_telepon = $request->get('no_telepon');
            $alamat = $request->get('alamat');
            $email = $request->get('email');

            $success['nama'] = $nama;
            $success['no_telepon'] = $no_telepon;
            $success['alamat'] = $alamat;
            $success['email'] = $email;
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
	/** 
     * details api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function details() 
    { 
        $user = Auth::user(); 
        return response()->json(['success' => $user], $this-> successStatus); 
    } 
}
