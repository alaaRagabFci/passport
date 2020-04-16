<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;

class PassportController extends Controller
{
  /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
 
        $token = $user->createToken('TutsForWeb')->accessToken;
 
        return response()->json(['token' => $token], 200);
    }

        /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
 
        if (auth()->attempt($credentials)) {
        	$http = new \GuzzleHttp\Client;
			$response = $http->post('http://localhost/passport/oauth/token', [
			    'form_params' => [
			        'grant_type' => 'password',
			        'client_id' => 2,
			        'client_secret' => 'hWBcw5YU05Rv4sqRAKpPXxc50NS2P3zhj6hJ1lHW',
			        'username' => $request->email,
			        'password' => $request->password,
			    ],
			]);
			$authTokens = json_decode((string) $response->getBody(), true);
            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
            $refresh_token = $authTokens['refresh_token'];
            return response()->json(['token' => $token, 'refresh_token' => $refresh_token], 200);
        } else {
            return response()->json(['error' => 'UnAuthorised'], 401);
        }
    }

        /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoggedUser()
    {
        return response()->json(['user' => auth()->user()], 200);
    }
}
