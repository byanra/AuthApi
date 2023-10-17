<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

use function PHPUnit\Framework\isEmpty;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json($validate->errors());
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'msg' => 'Anda Berhasil Registrasi',
                'data' => $user,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'msg' => $th->getMessage()
            ]);
        }
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'msg' => 'Unauthorized'
            ], 401);
        }
        $user = User::where('email', $request->email)->firstOrFail();


        $token = $user->createToken('apiToken')->plainTextToken;

        return response()->json([
            'msg' => 'Login Success',
            'data' => $user,
            'akses token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->tokens->each(function ($token, $key) {
                $token->delete();
            });

            return response()->json([
                'message' => 'Logout berhasil'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan internal saat logout'
            ], 500);
        }
    }
}
