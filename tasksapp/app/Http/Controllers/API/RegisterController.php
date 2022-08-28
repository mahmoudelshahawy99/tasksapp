<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends BaseController
{

    /**

    * function for user register on application
    * accept name, mail, password, confirming password and is_admin boolean value to determine if user is Supervisor or Employee
    * return the token and the name for the user if registeration successed

    */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' =>'required',
            'email' =>'required|email',
            'password' =>'required',
            'c_password' =>'required|same:password',
            'is_admin' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please validate error' ,$validator->errors() );
        }

            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            $user = User::create($input);
            if($input['is_admin']){
                $success['token'] = $user->createToken('Supervisor')->accessToken;
            }else{
                $success['token'] = $user->createToken('Employee')->accessToken;
            }
            $success['name'] = $user->name;

        return $this->sendResponse($success ,'User registered successfully' );
    }

    /**

    * function for user login on application
    * accept mail and  password
    * return the token and the name for the user if user logged in successfully

    */

    public function login(Request $request)
    {

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password]))
        {
            $user = Auth::user();
            if($user['is_admin']){
                $success['token'] = $user->createToken('Supervisor')->accessToken;
            }else{
                $success['token'] = $user->createToken('Employee')->accessToken;
            }
            $success['name'] = $user->name;
            return $this->sendResponse($success ,'User login successfully' );
        }
        else{
            return $this->sendError('Please check your Auth' ,['error'=> 'Unauthorised'] );
        }


    }

    /**

    * function for user logout from application
    * return the message of logging out

    */

    public function logout()
    {

        $user = Auth::user()->token();
        $user->revoke();
        return $this->sendResponse($success=[] ,'User logout successfully' );

    }
}
