<?php

namespace App\Http\Controllers\Auth;

use App\Team;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Requests\CreateDemoAccountRequest;
use App\Notifications\RegistrationWelcomeNotification;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    /**
     * Create demo account for newely created users so that the can share the same tenant to test our product 
     * but also we will create an original team for them so that if they decided to continue with us 
     * they must purchase a subscription to continue on 
     *
     * @param CreateDemoAccountRequest $request
     * @note : demo email : demo@fandaqah.com | demo password : Demo1
     * @return void
     */
    public function createDemoAccount(CreateDemoAccountRequest $request)
    {

        /**
         * Avoid spamming the demo account creation 
         */
        if($request->has('honeypot') && !is_null($request->get('honeypot'))){
            return redirect()->back()->with([
                'success' => false,
                'message' => 'The registration process in Fandaqah went wrong. Please try again.'
            ]);
        }
       
        // create a user  
        $user = $this->createUser($request->get('name'),$request->get('email'),$request->get('phone'));
        // create original team 
        // $team = $this->createOriginalTeam($request->get('team') , $user->id);
        // attach user to the demo team in team_users table  
        // DB::table('team_users')->insert([
        //     'team_id' => 44,
        //     'user_id' => $user->id
        // ]);
        
        // login credentials
        $credentials = [
            'email' => 'demo@fandaqah.com',
            'password' => 'Demo1'
        ];
        $data = [
            'to' => $user->email,
            'reply_to' => 'no-reply@app.fandaqah.com',
            'subject' => __('Welcome to fandaqah'),
            'html' => view('email.owner.registration_welcome_email')
                ->with(['data' => $credentials])
                ->render(),
            ];
        $send = sendMailUsingMailMicroservice($data);

        // $user->notify(new RegistrationWelcomeNotification($user, $credentials));

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Registeration process in fandaqah went successfully , login credentials will be sent to the email you\'ve used in the registeration process'
        ]);
    }

    /**
     * Undocumented function
     * @note our demo hotel id is 44 that will be the current team id for newely created users
     * @param [type] $name
     * @param [type] $email
     * @param [type] $phone
     * @return {}
     */
    function createUser($name,$email,$phone)
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => crypt('password',''),
            'current_team_id' => 44,
            'extra_billing_information' => ['is_demo_user' => true],
        ]);
        return $user;
    }
    /**
     * create Original Team
     *
     * @param [type] $name
     * @param [type] $user_id
     * @return {}
     */
    function createOriginalTeam($name,$owner_id)
    {
        $team = Team::create([
            'name' => $name,
            'owner_id' => $owner_id,
            'country_code' => 113
        ]);

        return $team;
    }
}
