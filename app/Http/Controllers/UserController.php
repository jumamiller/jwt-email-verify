<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * register user
     */
    public function register(Request $request){
        //dd($request);
        $validated=Validator::make($request->all(),[
            'first_name'    =>'required|string|max:255',
            'last_name'     =>'required|string|max:255',
            'username'      =>'required|string|max:255',
            'email_address' =>'required|string|email|unique:users',
            'phone_number'  =>'required|string|max:15',
            'password'      =>'required|string|confirmed',        
        ]);

        /**
         * if validation fails
         */
        if($validated->fails()){
            return response()->json($validated->errors()->toJson(),400);
        }

        /**
         * if no errors
         * send to db
         * @var $user
         */
         $verification_code=Str::random(30);
         $user=User::create([
             'first_name'       =>$request->get('first_name'),
             'last_name'        =>$request->get('last_name'),
             'username'         =>$request->get('username'),
             'email_address'    =>$request->get('email_address'),
             'phone_number'     =>$request->get('phone_number'),
             'password'         =>Hash::make($request->get('password')),
             'token'            =>$verification_code,
         ]);

         /**
          * email verification
          */
          try{
           
            $name=$user->first_name." ".$user->last_name;
            $email=$user->email_address;
            
            $subject=$user->first_name." ".$user->last_name." Please verify your email address";
            Mail::send('email.verify',['verification_code'=>$verification_code],
            function($mail) use ($name,$email,$subject){
                $mail->from('officialjuma3538@gmail.com');
                $mail->to($email,$name);
                $mail->subject($subject);
            });
           /**
           * create token
           */
            $token=JWTAuth::fromUser($user);

            return response()->json(compact('user','token'),201);
           
          }catch(\Exception $e){
              return response()->json($e,500);
          }
    }

    public function verifyUser($verification_code){
        $check=DB::table('users')->where('token',$verification_code)->first();

        if(!is_null($check)){
            $user=User::find($check->id);

            if($user->is_verified==true){
                return response()->json([
                    'success'=>true,
                    'message'=>'Account already verified'
                ]);
            }
            $user->update(['is_verified'=>1]);
            //DB::table('users')->where('token',$verification_code)->delete();

            return response()->json([
                'success'=>true,
                'message'=>'You have successfully verified your email address.'
                ]);
        }
        return response()->json(['success'=> false, 'error'=> "Verification code is invalid."]);
    }

    public function login(Request $request){
        $credentials=$request->only('username','password');

        $credentials['is_verified']=1;
        try{
            if(!$token=JWTAuth::attempt($credentials)){
                return response()->json([
                    'success'=>false,
                    'error'=>'invalid username or password'
                ],404);
            }
        }catch(JWTException $e){
            return response()->json([
                'success'=>false,
                'error'=>'failed to login,try again'
            ],500);
        }

        return response()->json([
            'success'=>true,
            'data'=>['token'=>$token]
        ],200);
    }

    public function logout(Request $request){
        $this->validate($request,['token'=>'required']);
        try{
            JWTAuth::invalidate($request->input('token'));
            return response()->json(['success' => true, 'message'=> "You have successfully logged out."]);
        }catch(JWTException $e){
            return response()->json(['success' => false, 'error' => 'Failed to logout, please try again.'], 500);
        }
    }
}
