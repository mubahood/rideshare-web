<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Encore\Admin\Auth\Database\Administrator;

class AuthController extends BaseAuthController
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view('admin.auth.login');
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $credentials = $request->only(['username', 'password']);
        $remember = $request->get('remember', false);

        $validator = Validator::make($credentials, [
            'username' => 'required|string',
            'password' => 'required|string|min:1',
        ], [
            'username.required' => 'Please enter your email, username, or phone number.',
            'password.required' => 'Please enter your password.',
            'password.min' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors($validator);
        }

        $username = $credentials['username'];
        $password = $credentials['password'];

        // Try to find user by email, username, or phone number
        $user = $this->findUserByUsernameOrEmailOrPhone($username);

        if (!$user) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => 'No account found with this email, username, or phone number.']);
        }

        // Check password
        if (!Hash::check($password, $user->password)) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors(['password' => 'Invalid password.']);
        }

        // Login the user
        $this->guard()->login($user, $remember);

        return $this->sendLoginResponse($request);
    }

    /**
     * Find user by username, email, or phone number
     *
     * @param string $identifier
     * @return Administrator|null
     */
    protected function findUserByUsernameOrEmailOrPhone($identifier)
    {
        // Remove any whitespace
        $identifier = trim($identifier);

        // Try to find by email first
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = Administrator::where('email', $identifier)->first();
            if ($user) {
                return $user;
            }
        }

        // Try to find by phone number (remove any non-numeric characters for comparison)
        if (preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $identifier)) {
            $phoneNumber = preg_replace('/[^0-9]/', '', $identifier);
            
            // Try different phone number fields
            $user = Administrator::where(function($query) use ($identifier, $phoneNumber) {
                $query->where('phone_number', $identifier)
                      ->orWhere('phone_number', $phoneNumber)
                      ->orWhere('phone_number_2', $identifier)
                      ->orWhere('phone_number_2', $phoneNumber);
                
                // Also try with country code variations
                if (strlen($phoneNumber) >= 9) {
                    // Try with +256 prefix
                    $query->orWhere('phone_number', '+256' . substr($phoneNumber, -9))
                          ->orWhere('phone_number_2', '+256' . substr($phoneNumber, -9));
                    
                    // Try with 256 prefix
                    $query->orWhere('phone_number', '256' . substr($phoneNumber, -9))
                          ->orWhere('phone_number_2', '256' . substr($phoneNumber, -9));
                    
                    // Try with 0 prefix
                    $query->orWhere('phone_number', '0' . substr($phoneNumber, -9))
                          ->orWhere('phone_number_2', '0' . substr($phoneNumber, -9));
                }
            })->first();
            
            if ($user) {
                return $user;
            }
        }

        // Finally, try to find by username or name
        return Administrator::where(function($query) use ($identifier) {
            $query->where('username', $identifier)
                  ->orWhere('name', $identifier);
        })->first();
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the post-login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        return admin_url();
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
