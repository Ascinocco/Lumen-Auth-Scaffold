<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Token;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

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

    public function register(Request $request)
    {
        $rules = [
            'email' => 'required|email|unique:users,email|max:255',
            'firstName' => 'required|alpha|max:80',
            'lastName' => 'required|alpha|max:125',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required|min:8'
        ];

        $this->validate($request, $rules);

        $fields = $request->all();

        $user = new User;
        
        $user->firstName = $fields['firstName'];
        $user->lastName = $fields['lastName'];
        $user->email = $fields['email'];
        $user->password = User::hashPassword($fields['password']);

        $user->save();

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

        $newToken = new Token;
        $newToken->value = $token;
        $newToken->userId = $user->id;
        $newToken->save();

        return response()->json([
            'success' => true,
            'msg' => 'Account Created!',
            'token' => $newToken->value
        ]);
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
            return response()->json([
                'success' => true,
                'msg' => 'Logged In!',
                'token' => $token
            ]);
        }

        return response()->json([
            'success' => false,
            'msg' => 'Invalid email or password'
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $token = $request->input('token');

        $clauses = [
            'value' => $token,
            'userId' => $user->id
        ];

        $assocTokenRecord = Token::where($clauses)->first();
        $assocTokenRecord->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Goodbye! :)'
        ]);
    }

    public function resetPassword(Request $request, $resetToken)
    {
        $token = DB::table('password_reset_tokens')
            ->where('value', $resetToken)
            ->first();

        $exireyTime = Carbon::parse($token->expiresAt);
        $currentTime = Carbon::now();

        $currentTime = $currentTime->addMinutes(10);
        // $expireyTime = $expireyTime->addMinutes(10);

        if ($currentTime->gt($expireyTime))
        {
            return response()->json([
                'success' => false,
                'msg' => 'Reset token has expired'
            ]);
        }

        $rules = [
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required|min:8'
        ];

        $this->validate($request, $rules);
        $password = $request->input('password');

        $user = User::where('email', $token->email)->first();
        $user->password = User::hashPassword($password);
        $user->save();

        return response()->json([
            'success' => true,
            'msg' => 'Password has been reset. Log with your new password'
        ]);
    }

    public function requestPasswordReset(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        Mail::send('emails.resetPassword', ['user' => $user],
            function ($message) use ($user) {
                $message->from('postmaster@sandboxb6dd6ed7a4b24c0186210ea1722d240a.mailgun.org', 'My Boilerplate');
                $message->to('anthonyscinocco@gmail.com', $user->firstName)->subject('Request for Password Reset');
        });

        return response()->json([
            'success' => 'unknown',
            'msg' => 'No errors but no email either'
        ]);

        // $email = $request->input('email');
        // $user = User::where('email', $email)->first();

        // if (!$user)
        // {
        //     return response()->json([
        //         'success' => false,
        //         'msg' => 'Could not find your account'
        //     ]);
        // }
        
        // // send email with unqiue token
        // $token = uniqid('pw_reset_', true);

        // // prep email fields
        // $link = url('/resetPassword');
        // $link = $link . "/" . $token;
        // $to = $email;
        // $subject = "Request for Password Reset";
        // $message = "We have recieved your request to reset your password. Click the link" .
        // " provided to reset it. The link will expire in 10 minutes. " . 
        // "Link: " . $link;

        // $headers = "From: support@example.com";

        // $expiresAt = Carbon::now();
        // $expiresAt = $expiresAt->addMinutes(10);

        // $tokenFields = [
        //         'value' => $token,
        //         'email' => $email,
        //         'expiresAt' => $expiresAt
        //     ];

        // DB::table('password_reset_tokens')->insert($tokenFields);

        // $returnVal = mail($to, $subject, $message, $headers);

        // dd($returnVal);

        // return response()->json([
        //     'success' => true,
        //     'msg' => 'Check your email'
        // ]);
    }
}