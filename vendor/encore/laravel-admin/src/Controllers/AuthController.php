<?php

namespace Encore\Admin\Controllers;

use App\Models\Campus;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @var string
     */
    protected $loginView = 'admin::login';

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        //return redirect('register');
        return view($this->loginView);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {

        $phone_number = Utils::prepare_phone_number($request->username);
        if ($this->guard()->attempt([
            'phone_number' => $phone_number,
            'password' => $request->password,
        ], true)) {
            if ($this->guard()->attempt([
                'email' => $phone_number,
                'password' => $request->password,
            ], true)) {
                if ($this->guard()->attempt([
                    'username' => $phone_number,
                    'password' => $request->password,
                ], true)) {
                    return $this->sendLoginResponse($request);
                }
            }
        }

        return back()
            ->withErrors(['password' => 'Wrong credentials.'])
            ->withInput();


        $r = $request;


        if (isset($_POST['password_1'])) {

            if (Validator::make($_POST, [
                'name' => 'required|string|min:4'
            ])->fails()) {
                return back()
                    ->withErrors(['name' => 'Enter your valid name.'])
                    ->withInput();
            }

            if (Validator::make($_POST, [
                'email' => 'required|email',
            ])->fails()) {
                return back()
                    ->withErrors(['email' => 'Enter a valid email address.'])
                    ->withInput();
            }

            if (Validator::make($_POST, [
                'password' => 'required|min:2'
            ])->fails()) {
                return back()
                    ->withErrors(['password' => 'Enter password with more than 3 chracters.'])
                    ->withInput();
            }

            if (Validator::make($_POST, [
                'password_1' => 'required|min:2'
            ])->fails()) {
                return back()
                    ->withErrors(['password_1' => 'Enter password with more than 3 chracters.'])
                    ->withInput();
            }

            if ($r->password != $r->password_1) {
                return back()
                    ->withErrors(['password_1' => 'Confirmation password did not match.'])
                    ->withInput();
            }

            $u = Administrator::where([
                'email' => $_POST['email']
            ])->orwhere([
                'username' => $_POST['email']
            ])->first();


            if ($u != null) {
                $u->username = $r->email;
                $u->email = $r->email;
                $u->password = password_hash($r->password, PASSWORD_DEFAULT);
                $u->save();
            } else {
                $admin = new Administrator();
                $admin->username = $r->email;
                $admin->name = $r->name;
                //$admin->avatar = 'user.png';
                $admin->password = password_hash($r->password, PASSWORD_DEFAULT);

                if (!$admin->save()) {
                    return back()
                        ->withErrors(['email' => 'Failed to create account. Try again.'])
                        ->withInput();
                }
            }
        }


        $u = Administrator::where([
            'email' => $_POST['email']
        ])->orwhere([
            'username' => $_POST['email']
        ])->first();


        if ($u == null) {
            return back()
                ->withErrors(['email' => 'Account with provided email address was not found.'])
                ->withInput();
        }


        $u->username = $r->email;
        $u->email = $r->email;
        $u->password = password_hash($r->password, PASSWORD_DEFAULT);
        $u->save();


        if (Auth::attempt([
            'email' => $r->email,
            'password' => $r->password,
        ], true)) {
        }


        $credentials = $request->only(['email', 'password']);
        $remember = true;

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        $credentials['username'] = $request->email;
        $credentials['password'] = $request->password;

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }


        return back()
            ->withErrors(['email' => 'Failed to log you in. Try again.'])
            ->withInput();
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->disableView();
            }
        );

        return $content
            ->title('My profile')
            ->body($form->edit(Admin::user()->id));
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->divider('Bio information');

        $form->radio('title', 'Title')
            ->options([
                'Mr' => 'Mr',
                'Ms' => 'Ms',
                'Mrs' => 'Mrs',
                'Dr' => 'Dr',
                'Prof' => 'Prof',
                'Haji' => 'Haji',
                'Hajjat' => 'Hajjat',
                'Imam' => 'Imam',
                'Shaykh' => 'Shaykh',
                'Mufti' => 'Mufti',
            ])
            ->rules('required');

        $form->text('first_name', 'First name')->rules('required');
        $form->text('last_name', 'Last name')->rules('required');
        $form->radio('sex', 'Sex')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');
        $form->date('dob', 'Date of birth');

        $form->textarea('intro', 'Breifly Introduce yourself')->rules('required')
            ->help('Write a very short bio about yourself');




        $form->select('country', 'Nationality')
            ->help('Your country of origin')
            ->options(Utils::COUNTRIES())->rules('required');


        $form->text('occupation', 'Occupation');



        $form->image('avatar', 'Porfile photo');
        $form->file('cv', 'CV File')->rules('mimes:doc,docx,pdf');




        $form->divider('Contact information');
        $form->text('address', 'Current Address')->help('Leave this field empty if you don\'t want it to appear on your profile.');
        $form->mobile('phone_number', 'Phone number')->options(['mask' => '+999 9999 99999'])->help('Leave this field empty if you don\'t want it to appear on your profile.');


        $form->select('language', 'Fluent Language')
            ->options([
                'English' => 'English',
                'Arabic' => 'Arabic',
                'Swahili' => 'Swahili',
                'French' => 'French',
                'Other' => 'Other',
            ]);

        $form->url('website', 'Personal website');
        $form->text('twitter', 'Twitter hundle (Username)');
        $form->text('facebook', 'Facebook username');
        $form->text('linkedin', 'Linkedin username');
        $form->mobile('whatsapp', 'Whatsapp number')->options(['mask' => '+999 9999 99999']);




        $form->divider('System account information');


        $form->display('username', trans('admin.username'));
        $form->display('email', 'Email address');

        $form->radio('change_password', 'Do you want to change password?')->options(['No' => 'No', 'Yes' => 'Yes'])
            ->when('Yes', function ($form) {



                $form->password('password', trans('admin.password'))->rules('confirmed|required');
                $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
                    ->default(function ($form) {
                        return $form->model()->password;
                    });


                $form->ignore(['password_confirmation']);
                $form->ignore(['change_password']);
            })
            ->default('No');

        $form->setAction(admin_url('auth/setting'));
        $form->ignore(['password_confirmation']);
        $form->ignore(['change_password']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_url('auth/setting'));
        });

        return $form;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }
}
