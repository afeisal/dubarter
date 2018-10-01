<?php

namespace App\Http\Controllers\api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user=User::all();
        return ['status'=>'success','content'=>$user];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:10|confirmed',
            'last_name' => 'required|string',
            "photo" =>'nullable|image',
        ]);
        if ($validator->fails()) {
            return ['status' => 'failed', 'content' => $validator->errors()];
        }
        $requests = $request->all();
        unset($requests['password_confirmation']);


//        $requests['image'] = 'https://api.adorable.io/avatars/285/' . $request->name . '.png';
        $requests['api_token'] = Str::random(50);
        $requests['password'] = Hash::make($request->password);
        $img=$request->photo;
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $file =  uniqid() . '.png';
        \File::put(public_path(). '/' . $file,$data);
        $requests['photo']=public_path(). '/' . $file;

//        $success = file_put_contents($file, $data);
//dd($success);
        DB::table('users')->insert($requests);

        $user=User::where('email',$requests['email'])->first();

        return ['status' => 'success', 'content' => $user,'api_token'=>$user->api_token];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user=User::find($id);
        if (is_null($user)){
            return ['status'=>'failed','content'=>'Not Found such user'];
        }

        return ['status'=>'success','content'=>$user];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $child=User::find($id);

        if(is_null($child)){
            return ['status'=>'failed','not found such this user'];
        }
        $validator = Validator::make($request->all(), [

            'name' => 'string|max:255',
            'email' => 'email|unique:users',
            'password' => 'string|min:10|confirmed',
            'last_name' => 'string',
            "photo" =>'nullable|image',
        ]);

        if ($validator->fails()) {
            return ['status' => 'failed', 'content' => $validator->errors()];
        }


        $requests = $request->all();
       if($request->has('password')){
           $requests['password']=Hash::make($request->password);
       }
        unset($requests['api_token']);
        unset($requests['password_confirmation']);
        if ($request->hasFile('photo')){
            $img=$request->photo;
            $img = str_replace('data:image/png;base64,', '', $img);
            $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            $file =  uniqid() . '.png';
            \File::put(public_path(). '/' . $file,$data);
            $requests['photo']=public_path(). '/' . $file;
        }
        DB::beginTransaction();
        DB::table('users')->where('id',$id)->update($requests);
        DB::commit();

        $child=User::find($id);

        return ['status'=>'success','content'=>$child];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        $user=User::find($id);
        if (is_null($user)){
            return ['status'=>'failed','content'=>'Not Found such user'];
        }
        $user->delete();
        DB::commit();
        return ['status'=>'success','content'=>$user];
    }

    //========== function for login =================
  /*  public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|min:14|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'last_name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return ['status' => 'failed', 'content' => $validator->errors()];
        }
        $requests = $request->all();
        unset($requests['password_confirmation']);


//        $requests['image'] = 'https://api.adorable.io/avatars/285/' . $request->name . '.png';
        $requests['api_token'] = Str::random(50);
        $requests['password'] = Hash::make($request->password);
        DB::table('users')->insert($requests);

        $user=User::where('email',$requests['email'])->first();

        return ['status' => 'success', 'content' => $user,'api_token'=>$user->api_token];
    }*/

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:10',
        ]);
        if ($validator->fails()) {
            return ['status' => 'failed', 'content' => $validator->errors()];
        }
        $user = User::where('email',$request->email)->first();
        $pw=$request->password;
        $hashed =$user->password;


        if (!Hash::check($request->password,$user->password)) {
            return ['status' => 'failed', 'content' => 'كلمة المرور غير صحيحة'];
        }
        if($user->api_token==null){
            $user->api_token=Str::random(50);
            $user->save();
        }


        $user = User::where('email',$request->email)->first();
        return ['status'=>'success','content'=>$user,'api_token'=>$user->api_token];

    }
}
