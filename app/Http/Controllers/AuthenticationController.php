<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;
use App\Token;
use App\User;

class AuthenticationController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function register()
    {

    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {
            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {

            // remove expired token from db
            $expiredToken = Token::where('value', $token)->first();
            $expiredToken->delete();

            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], $e->getStatusCode());
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if ($user->comparePassword($password)) 
        {
            $newToken = new Token;
            $newToken->value = $token;
            $newToken->userId = $user->id;
            $newToken->save();
            return response()->json(compact('token'));
        }

        return response()->json([
            'success' => false,
            'msg' => 'Invalid email or password'
        ]);
    }

    public function logut()
    {

    }

    public function secureRoute()
    {
        return "Worked";
    }
}