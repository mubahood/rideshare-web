<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Negotiation;
use App\Models\RouteStage;
use App\Models\Trip;
use App\Models\TripBooking;
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


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function users()
    {
        return $this->success(Administrator::all(), $message = "Success", 200);
    }
    public function me()
    {
        $query = auth('api')->user();
        $data = [];
        $admin = Administrator::find($query->id);
        if ($admin != null) {
            $admin->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $admin->save();
        }
        $data[] = $admin;
        return $this->success($data, $message = "Profile details", 200);
    }


    public function trips_bookings_create(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        if ($r->trip_id == null) {
            return $this->error('Trop not dound.');
        }
        if ($r->slot_count == null) {
            return $this->error('You have not specified the number of slots.');
        }
        $trip = Trip::find($r->trip_id);
        if ($trip == null) {
            return $this->error('Trip not found.');
        }
        if ($trip->status != 'Pending') {
            return $this->error('Trip is not in pending status.');
        }
        $booking = new TripBooking();
        $booking->trip_id = $trip->id;
        $booking->customer_id = $u->id;
        $booking->driver_id = $trip->driver_id;
        $booking->start_stage_id = $trip->start_stage_id;
        $booking->end_stage_id = $trip->end_stage_id;
        $booking->status = 'Pending';
        $booking->payment_status = 'Pending';
        $booking->start_time = null;
        $booking->end_time = null;
        $booking->slot_count = ((int)($r->slot_count));
        if ($booking->slot_count > $trip->slots) {
            return $this->error('You have exceeded the available slots.');
        }
        $booking->price = $trip->price * $booking->slot_count;
        $booking->customer_note = $r->customer_note;

        $start_stage = RouteStage::find($booking->start_stage_id);
        $end_stage = RouteStage::find($booking->end_stage_id);
        if ($start_stage == null) {
            throw new \Exception("Start stage not found.");
        }
        if ($end_stage == null) {
            throw new \Exception("End stage not found.");
        }
        $booking->start_stage_text = $start_stage->name;
        $booking->end_stage_text = $end_stage->name;
        $booking->customer_text = $u->name;
        $booking->driver_text = $trip->details;


        try {
            $booking->save();
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        } finally {
            //$trip->slots = $trip->slots - $booking->slot_count;
            //$trip->save();
        }
        return $this->success(null, $message = "Trip booking created successfully.", 1);
    }



    public function trips_bookings_update(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        if ($r->id == null) {
            return $this->error('Trip ID  missing.');
        }
        $booking = TripBooking::find($r->id);
        if ($booking == null) {
            return $this->error('Booking not found.');
        }

        if ($r->status != null) {

            if ($r->status == 'Reserved' && $booking->status != 'Reserved') {
                $booking->status = $r->status;
                Utils::send_message(
                    $booking->customer->phone_number,
                    "RIDESAHRE! Your trip booking has been reserved. Open the app to view it."
                );
            }
            if ($r->status == 'Canceled' && $booking->status != 'Canceled') {
                $booking->status = $r->status;
                if ($u->id == $booking->customer_id) {
                    Utils::send_message(
                        $booking->driver->phone_number,
                        "RIDESAHRE! Booking has been canceled by the customer. Open the app to view it."
                    );
                } else {
                    Utils::send_message(
                        $booking->customer->phone_number,
                        "RIDESAHRE! Your trip booking has been canceled by the driver. Open the app to view it."
                    );
                }

                $booking->trip->slots = $booking->trip->slots + $booking->slot_count;
            }

            if ($r->status == 'Completed' && $booking->status != 'Completed') {
                $booking->status = $r->status;
                Utils::send_message(
                    $booking->customer->phone_number,
                    "RIDESAHRE! Your trip has been completed. Open the app to view it."
                );
            }
        }


        try {
            $booking->save();
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
        return $this->success(null, $message = "Trip booking updated successfully.", 1);
    }


    public function go_on_off(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        if ($r->lati == null) {
            return $this->error('lati  missing.');
        }
        if ($r->long == null) {
            return $this->error('long  missing.');
        }
        if ($r->status == null) {
            return $this->error('status  missing.');
        }
        if ($r->status != 'online' && $r->status != 'offline') {
            return $this->error('Submitted status is invalid.');
        }

        if ($u->status != 1) {
            return $this->error('Your driver account is not active.');
        }

        $u->current_address = $r->lati . "," . $r->long;
        $status = 'offline';
        if ($r->status == 'online') {
            $t = Negotiation::where('driver_id', $u->id)
                ->where('is_active', 'Yes')
                ->first();
            if ($t != null) {
                $u->ready_for_trip = 'No';
                $u->save();
                $status = 'offline';
                return $this->error('You have an active negotiation. You cannot go online. First end the active trip and then try again.');
            }
            $status = 'online';
            $u->ready_for_trip = 'Yes';
            $u->save();
        } else {
            $status = 'offline';
            $u->ready_for_trip = 'No';
            $u->save();
        }
        return $this->success($status, $message = "Success!, you are now $status.", 1);
    }

    public function negotiation_updates(Request $r)
    {
        $neg = Negotiation::find($r->id);
        if ($neg == null) {
            return $this->error('Negotiation not found.');
        }
        return $this->success($neg, $message = "success.", 1);
    }

    public function refresh_status(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        if ($r->lati == null) {
            return $this->error('lati  missing.');
        }
        if ($r->long == null) {
            return $this->error('long  missing.');
        }

        if ($u->status != 1) {
            return $this->error('Your driver account is not active.');
        }
        $data = [];
        $data['status'] = 'offline';
        if ($u->ready_for_trip != 'Yes') {
            $data['status'] = 'offline';
        } else {
            $data['status'] = 'online';
        }

        $t = Negotiation::where('driver_id', $u->id)
            ->where('is_active', 'Yes')
            ->first();

        $data['has_trip'] = null;
        if ($t == null) {
            $data['trip'] = null;
            $data['has_trip'] = 'No';
        } else {
            $data['has_trip'] = 'Yes';
            $data['trip'] = $t;
            $data['status'] = 'online';
        }

        return $this->success($data, $message = "", 1);
    }


    public function trips_drivers(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if ($r->automobile == null) {
            return $this->error('You have not specified your automobile.');
        }
        if ($r->current_address == null) {
            return $this->error('You have not specified your current address.');
        }

        $drivers = Administrator::where('user_type', 'Driver')
            ->where('status', 1)
            /* ->where('automobile', $r->automobile) */
            ->where('ready_for_trip', 'Yes')
            ->where('id', '!=', $u->id)
            ->limit(25)
            ->orderBy('updated_at', 'desc')
            ->get();


        $data = [];
        //calculate distance
        foreach ($drivers as $key => $driver) {

            //check if $driver->current_address contains ,
            if (!str_contains($driver->current_address, ',')) {
                continue;
            }
            //check if $r->current_address contains ,
            if (!str_contains($r->current_address, ',')) {
                continue;
            }

            $distance = Utils::haversineDistance($r->current_address, $driver->current_address);
            $driver->distance = $distance;

            $min_speed = 60;
            $max_speed = 80;

            $min_time = $distance / $max_speed;
            $max_time = $distance / $min_speed;

            $min_hours = floor($min_time);
            $min_minutes = ($min_time - $min_hours) * 60;
            $min_word = $min_hours . "hr ";
            if ($min_hours < 1) {
                $min_word = ((int)($min_minutes)) . " minutes";
            } else {
                $min_word = $min_hours . "hr and " . ((int)($min_minutes)) . "min";
            }

            $max_hours = floor($max_time);
            $max_minutes = ($max_time - $max_hours) * 60;
            $max_word = $max_hours . "hr ";
            if ($max_hours < 1) {
                $max_word = ((int)($max_minutes)) . " minutes";
            } else {
                $max_word = $max_hours . "hr " . ((int)($max_minutes)) . "min";
            }
            $driver->min_time = $min_word;
            $driver->max_time = $max_word;
            $driver->distance = $distance;

            $data[] = $driver;
        }

        return $this->success($data, $message = "Trip booking updated successfully.", 1);
    }
    public function trips_update(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        if ($r->id == null) {
            return $this->error('Trip ID  missing.');
        }
        $trip = Trip::find($r->id);
        if ($trip == null) {
            return $this->error('Trip found.');
        }

        $trip->status = $r->status;
        try {
            $trip->save();
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
        return $this->success(null, $message = "Trip booking updated successfully.", 1);
    }


    public function trips_create(Request $r)
    {
        $query = auth('api')->user();
        $data = [];
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        if ($u->user_type != 'Driver') {
            return $this->error('You are not a driver.');
        }
        if ($r->price == null) {
            return $this->error('Price is required.');
        }


        $trip = new Trip();
        $trip->driver_id = $u->id;
        $trip->customer_id = $u->id;
        $trip->start_stage_id = 1;
        $trip->end_stage_id = 1;
        $trip->scheduled_start_time = Carbon::parse($r->departure_date);
        $trip->scheduled_end_time = $r->arrival_date;
        $trip->start_time = null;
        $trip->end_time = null;
        $trip->status = 'Pending';
        $trip->vehicel_reg_number = $r->car_reg_number;
        $trip->slots = $r->available_slots;
        $trip->details = $r->details;
        $trip->car_model = $r->car_model;
        $trip->price = $r->price;
        $trip->start_gps = $r->start_gps;
        $trip->end_pgs = $r->end_pgs;
        $trip->start_name = $r->start_name;
        $trip->end_name = $r->end_name;


        try {
            $trip->save();
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
        return $this->success(null, $message = "Trip created successfully.", 1);
    }
    public function become_driver(Request $r)
    {
        $u = auth('api')->user();
        $admin = Administrator::find($u->id);
        if ($admin == null) {
            return $this->error('User not found.');
        }
        
        // Validate required fields
        if ($r->driving_license_number == null) {
            return $this->error('Driving license number is required.');
        }
        if ($r->driving_license_issue_date == null) {
            return $this->error('Driving license issue date is required.');
        }
        if ($r->driving_license_validity == null) {
            return $this->error('Driving license validity is required.');
        }
        if ($r->driving_license_issue_authority == null) {
            return $this->error('Driving license issue authority is required.');
        }
        if ($r->nin == null) {
            return $this->error('National ID number is required.');
        }
        if ($r->automobile == null) {
            return $this->error('Automobile not specified, download the new app update and try again.');
        }

        // Validate that at least one service is selected
        $services = ['car', 'boda', 'ambulance', 'police', 'delivery', 'breakdown', 'firebrugade'];
        $hasService = false;
        foreach ($services as $service) {
            if ($r->input("is_{$service}") === 'Yes') {
                $hasService = true;
                break;
            }
        }
        
        if (!$hasService) {
            return $this->error('Please select at least one service you want to provide.');
        }

        // Update basic driver information
        $admin->driving_license_number = $r->driving_license_number;
        $admin->nin = $r->nin;
        $admin->driving_license_issue_date = Carbon::parse($r->driving_license_issue_date);
        $admin->driving_license_validity = Carbon::parse($r->driving_license_validity);
        $admin->driving_license_issue_authority = $r->driving_license_issue_authority;
        $admin->automobile = $r->automobile;

        // Update personal information if provided
        if ($r->first_name) {
            $admin->first_name = $r->first_name;
        }
        if ($r->last_name) {
            $admin->last_name = $r->last_name;
        }
        if ($r->first_name && $r->last_name) {
            $admin->name = $r->first_name . ' ' . $r->last_name;
        }
        if ($r->date_of_birth) {
            $admin->date_of_birth = Carbon::parse($r->date_of_birth);
        }
        if ($r->home_address) {
            $admin->home_address = $r->home_address;
        }

        // Update service selections
        foreach ($services as $service) {
            $serviceField = "is_{$service}";
            $approvalField = "is_{$service}_approved";
            
            $admin->$serviceField = $r->input($serviceField, 'No');
            
            // Reset approval status when service selection changes
            if ($admin->$serviceField === 'Yes') {
                $admin->$approvalField = 'No'; // Will be approved by admin later
            } else {
                $admin->$approvalField = 'No';
            }
        }

        // Handle file upload for driving license photo
        $image = Utils::upload_images_1($_FILES, true);
        if ($image != null) {
            if (strlen($image) > 3) {
                $admin->driving_license_photo = $image;
            }
        }

        // Update status and user type
        $admin->status = 2; // Pending approval
        $admin->user_type = 'Pending Driver';
        $admin->save();

        // Prepare response data with service information
        $responseData = $admin->toArray();
        $responseData['requested_services'] = [];
        foreach ($services as $service) {
            if ($admin->{"is_{$service}"} === 'Yes') {
                $responseData['requested_services'][] = $service;
            }
        }

        return $this->success($responseData, "Driver request submitted successfully. Your application will be reviewed for the selected services.", 200);
    }





    public function login(Request $r)
    {
        if ($r->username == null) {
            return $this->error('Email or phone number is required.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $identifier = trim($r->username);
        $password = trim($r->password);

        // Find user by email, phone number, or username
        $u = User::where('email', $identifier)
            ->orWhere('phone_number', $identifier)
            ->orWhere('phone_number', $identifier)
            ->orWhere('phone_number_2', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('id', $identifier)
            ->first();

        // If not found in User model, try Administrator model
        if ($u == null) {
            $u = Administrator::where('email', $identifier)
                ->orWhere('phone_number', $identifier)
                ->orWhere('username', $identifier)
                ->orWhere('id', $identifier)
                ->first();
        }

        if ($u == null) {
            return $this->error('User account not found.');
        }

        // Verify password
        if (!password_verify($password, $u->password)) {
            return $this->error('Invalid password.');
        }

        // Check if account is active
        if ($u->status != 1) {
            return $this->error('Your account is not active. Please contact support.');
        }

        // Generate JWT token
        JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => $password,
        ]);

        if ($token == null) {
            return $this->error('Authentication failed. Please try again.');
        }

        $u->remember_token = $token;
        $u->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $u->save();

        return $this->success($u, 'Logged in successfully.');
    }


    public function register(Request $r)
    {
        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }
        $phone_number = trim($r->phone_number);
        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        if ($r->first_name == null || $r->last_name == null) {
            return $this->error('Name is required.');
        }
        if ($r->gender == null) {
            return $this->error('Gender is required.');
        }
        
        // Password validation
        if ($r->password == null) {
            return $this->error('Password is required.');
        }
        
        if (strlen(trim($r->password)) < 6) {
            return $this->error('Password must be at least 6 characters long.');
        }

        // Email validation (optional but if provided should be valid)
        if ($r->email != null) {
            $email = trim($r->email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('Invalid email address.');
            }
            
            // Check if email already exists
            $existing_email = Administrator::where('email', $email)->first();
            if ($existing_email != null) {
                return $this->error('Email address already exists.');
            }
        }

        // Check if user already exists
        $u = Administrator::where('phone_number', $phone_number)
            ->orWhere('username', $phone_number)->first();
        if ($u != null) {
            return $this->error('Phone number already registered. Please sign in instead.');
        }

        // Create new user
        $user = new Administrator();
        $user->first_name = trim($r->first_name);
        $user->last_name = trim($r->last_name);
        $user->sex = trim($r->gender);
        $user->username = $phone_number;
        $user->phone_number = $phone_number;
        $user->name = trim($r->first_name) . " " . trim($r->last_name);
        $user->password = password_hash(trim($r->password), PASSWORD_DEFAULT);
        $user->email = $r->email ? trim($r->email) : null;
        $user->status = 1; // Active status
        $user->user_type = 'customer'; // Default user type

        if (!$user->save()) {
            return $this->error('Failed to create account. Please try again.');
        }

        // Generate JWT token for immediate login
        JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'id' => $user->id,
            'password' => trim($r->password),
        ]);

        if ($token == null) {
            return $this->error('Account created but failed to log in automatically. Please sign in manually.');
        }

        $user->remember_token = $token;

        return $this->success($user, 'Account created and logged in successfully.');
    }
}
