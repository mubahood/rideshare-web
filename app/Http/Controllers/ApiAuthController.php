<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiAuthController extends Controller
{

    use ApiResponser;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {

        /* $token = auth('api')->attempt([
            'username' => 'admin',
            'password' => 'admin',
        ]);
        die($token); */
        $this->middleware('auth:api', ['except' => ['login', 'register','otp-verify']]);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $query = auth('api')->user();
        return $this->success($query, $message = "Profile details", 200);
    }





    public function login(Request $r)
    {
        if ($r->username == null) {
            return $this->error('Username is required.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $r->username = trim($r->username);

        $u = User::where('phone_number_1', $r->username)
            ->orWhere('phone_number_2', $r->username)
            ->orWhere('username', $r->username)
            ->orWhere('id', $r->username)
            ->orWhere('email', $r->username)
            ->first();



        if ($u == null) {

            $phone_number = Utils::prepare_phone_number($r->username);

            if (Utils::phone_number_is_valid($phone_number)) {
                $phone_number = $r->phone_number;

                $u = User::where('phone_number', $phone_number)
                    ->orWhere('username', $phone_number)
                    ->orWhere('email', $phone_number)
                    ->first();
            }
        }

        if ($u == null) {
            return $this->error('User account not found.');
        }


        JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => trim($r->password),
        ]);


        if ($token == null) {
            return $this->error('Wrong credentials.');
        }



        $u->token = $token;
        $u->remember_token = $token;

        return $this->success($u, 'Logged in successfully.');
    }


    public function register(Request $r)
    {
        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }
        $phone_number = Utils::prepare_phone_number(trim($r->phone_number));
        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        if ($r->first_name == null || $r->last_name == null) {
            return $this->error('Name is required.');
        }
        if ($r->gender == null) {
            return $this->error('Gender is required.');
        }

        $u = Administrator::where('phone_number', $phone_number)
            ->orWhere('username', $phone_number)->first();
        if ($u != null) {
            $u->phone_number = $phone_number;
            $u->save();
            $resp = Utils::send_otp($u);
            if (strlen($resp) > 0) {
                return $this->error($resp);
            }
            return $this->success($u, 'Verification code sent to your phone number.');
        }

        $user = new Administrator();
        $user->first_name = trim($r->first_name);
        $user->last_name = trim($r->last_name);
        $user->sex = trim($r->gender);
        $user->username = $phone_number;
        $user->name = trim($r->first_name) . " " . trim($r->last_name);
        $user->password = '4321';

        $user->status = 1;
        if (!$user->save()) {
            return $this->error('Failed to create account. Please try again.');
        }

        $new_user = Administrator::find($user->id);
        if ($new_user == null) {
            return $this->error('Account created successfully but failed to log you in.');
        }
        /* Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'username' => $phone_number,
            'password' => trim($r->password),
        ]); */
        if ($new_user != null) {
            $resp = Utils::send_otp($new_user);
            if (strlen($resp) > 0) {
                return $this->error($resp);
            }
            return $this->success($new_user, 'Account created successfully. Verification code sent to your phone number.');
        } else {
            return $this->error('Account created successfully but failed to log you in.');
        }
    }


}
