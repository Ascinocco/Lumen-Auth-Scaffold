<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Token;

class UserController extends Controller
{

    public function read()
    {
        return response()->json([
            'success' => true,
            'user' => Auth::user()->toCleanJson()
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'email' => 'required|email|max:255',
            'firstName' => 'required|alpha|max:80',
            'lastName' => 'required|alpha|max:125',
        ];

        $this->validate($request, $rules);
        $fields = $request->all();

        $user = Auth::user();

        $user->firstName = $fields['firstName'];
        $user->lastName = $fields['lastName'];

        // needed custom email validation because
        // we save all fields each time for ease but because of that
        // an email that belongs to the user can be marked as not unique
        // by laravels built in validation
        if($user->email !== $fields['email'])
        {
            $userWithEmail = User::where('email', $fields['email'])->get();

            if (count($userWithEmail) > 0)
            {
                return response()->json([
                    'success' => false,
                    'msg' => 'Email already in use'
                ]);
            }

            $user->email = $fields['email'];
        }

        $user->save();

        return response()->json([
            'success' => true,
            'msg' => 'Account Updated!',
            'user' => $user->toCleanJson()
        ]);
    }

    public function delete()
    {
        $user = Auth::user();
        
        $tokens = Token::where('userId', $user->id)->get();

        foreach($tokens as $token)
        {
            $token->delete();
        }
        
        $user->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Sad to see you go :('
        ]);
    }
}