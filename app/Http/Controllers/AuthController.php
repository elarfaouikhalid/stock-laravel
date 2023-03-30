<?php

namespace App\Http\Controllers;

use App\Events\UserLoggedIn;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['login']);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        // dd($validator);
        $user = User::where('email', $request->email)->first();
     
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // throw ValidationException::withMessages([
            //     'email' => ['The provided credentials are incorrect.'],
            // ]);
            return response()->json(["errors" => "The provided credentials are incorrect."], 422);
        }

        $user->tokens()->delete();
        event(new UserLoggedIn($user, now()));
     
        return response()->json([
            "token" => $user->createToken("api_token")->plainTextToken,
            // "user" => $user
        ], 200);
    }

    public function userProfile() {
        return response()->json(auth()->user());
    }

    public function getprofileinfo($id) {
        
    }

    public function getLogs()
    {
        $logs = [];
        $logFile = storage_path('logs/activity.log');
        
        if (file_exists($logFile)) {
            $logContents = file_get_contents($logFile);
            preg_match_all('/(?<=local\.INFO: ).*/', $logContents, $matches);
            $logs = $matches[0];
        }
        
        return response()->json(['logs' => $logs]);
        
            
    }
    
    public function logout() {
        Auth::user()->tokens()->delete();
        Auth::guard('web')->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    // public function register(Request $request) {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|between:2,100',
    //         'email' => 'required|string|email|max:100|unique:users',
    //         'password' => 'required|string|confirmed|min:8',
    //     ]);

    //     if($validator->fails()){
    //         return response()->json($validator->errors(), 400);
    //     }

    //     $user = User::create(array_merge(
    //                 $validator->validated(),
    //                 ['password' => Hash::make($request->password)]
    //             ));

    //     return response()->json([
    //         'message' => 'User successfully registered',
    //         'user' => $user
    //     ], 201);
    // }
    public function changeProfile(Request $request) {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    
        $validatedData = $validator->validated();
    
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 422);
        }
    
        DB::beginTransaction();
    
        try {
            if ($request->hasFile("profile_image")) {
                $file = $request->file("profile_image");
                $path = $file->hashName();
                $file->store('public/users/profile');
            }
    
            Profile::updateOrCreate(
                ['user_id' => Auth()->user()->id],
                ["profile_image" => 'http://127.0.0.1:8000/storage/users/profile/'.$path]
            );
    
            DB::commit();
    
            return response()->json([
                "success" => "Image updated successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
    
            return response()->json([
                "error" => "Failed to update image"
            ], 422);
        }
    }
    public function getProfile(Request $request) {
        $profiledata = Profile::where('user_id', $request->user_id)
        ->get();

        return response()->json(['profiledata' => $profiledata]);
    }
}
