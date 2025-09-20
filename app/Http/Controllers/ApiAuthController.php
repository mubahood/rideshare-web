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
use Illuminate\Support\Facades\Log;
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
        if ($query == null) {
            return $this->error('User not found.');
        }
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

        // Enhanced input validation
        if (empty($r->trip_id)) {
            return $this->error('Trip not found.');
        }
        if (empty($r->slot_count)) {
            return $this->error('You have not specified the number of slots.');
        }

        // Validate slot count is a positive integer
        $slotCount = (int)$r->slot_count;
        if ($slotCount <= 0) {
            return $this->error('Invalid number of slots. Must be greater than 0.');
        }

        $trip = Trip::find($r->trip_id);
        if ($trip == null) {
            return $this->error('Trip not found.');
        }
        if ($trip->status != 'Pending') {
            return $this->error('Trip is not in pending status.');
        }

        // Check if user already has a booking for this trip
        $existingBooking = TripBooking::where('trip_id', $trip->id)
            ->where('customer_id', $u->id)
            ->where('status', '!=', 'Canceled')
            ->first();
        if ($existingBooking) {
            return $this->error('You already have a booking for this trip.');
        }

        // Calculate available slots considering existing bookings
        $bookedSlots = TripBooking::where('trip_id', $trip->id)
            ->where('status', '!=', 'Canceled')
            ->sum('slot_count');
        $availableSlots = $trip->slots - $bookedSlots;

        if ($slotCount > $availableSlots) {
            return $this->error("Only {$availableSlots} slots are available for this trip.");
        }

        // Validate route stages exist before creating booking
        $start_stage = RouteStage::find($trip->start_stage_id);
        $end_stage = RouteStage::find($trip->end_stage_id);
        if ($start_stage == null) {
            return $this->error("Start stage not found.");
        }
        if ($end_stage == null) {
            return $this->error("End stage not found.");
        }

        // Get driver information
        $driver = Administrator::find($trip->driver_id);
        if ($driver == null) {
            return $this->error("Driver not found.");
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
        $booking->slot_count = $slotCount;
        $booking->price = $trip->price * $slotCount;
        $booking->customer_note = $r->customer_note ?? '';

        // Populate text fields
        $booking->start_stage_text = $start_stage->name;
        $booking->end_stage_text = $end_stage->name;
        $booking->customer_text = $u->name;
        $booking->driver_text = $driver->name;
        $booking->trip_text = $trip->details ?? $trip->name ?? 'Trip';
        // Note: customer_contact and driver_contact are computed attributes, not database columns

        try {
            $booking->save();

            // Send notification to driver (if SMS functionality is available)
            if (method_exists('App\Models\Utils', 'send_message') && !empty($driver->phone_number)) {
                try {
                    Utils::send_message(
                        $driver->phone_number,
                        "RIDESHARE! New booking received for your trip. {$slotCount} seat(s) booked by {$u->name}. Open the app to view details."
                    );
                } catch (\Throwable $smsError) {
                    // Log SMS error but don't fail the booking
                    Log::warning('Failed to send SMS notification: ' . $smsError->getMessage());
                }
            }
        } catch (\Throwable $th) {
            return $this->error('Failed to create booking: ' . $th->getMessage());
        }

        return $this->success($booking, "Trip booking created successfully.", 1);
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

        $_automobileType = strtolower(trim($r->automobile));
        $accepted_automobiles = [
            'car', 
            'bodaboda', 
            'ambulance', 
            'pickup', 
            'police', 
            'firebrugade', 
            'delivery', 
            'breakdown'
        ];
        if (!in_array($_automobileType, $accepted_automobiles)) {
            return $this->error('Invalid automobile type. Accepted types are: car, boda, ambulance.');
        }
        $automobileFieldValue = null;
        $automobileFieldKey = null;
        if ($_automobileType == 'car') {
            $automobileFieldKey = 'is_car';
            $automobileFieldValue = 'is_car_approved';
        } else if ($_automobileType == 'bodaboda' || $_automobileType == 'boda') {
            $automobileFieldKey = 'is_boda';
            $automobileFieldValue = 'is_boda_approved';
        } else if ($_automobileType == 'ambulance') {
            $automobileFieldKey = 'is_ambulance';
            $automobileFieldValue = 'is_ambulance_approved';
        } else if ($_automobileType == 'pickup') {
            $automobileFieldKey = 'is_pickup';
            $automobileFieldValue = 'is_pickup_approved';
        } else if ($_automobileType == 'police') {
            $automobileFieldKey = 'is_police';
            $automobileFieldValue = 'is_police_approved';
        } else if ($_automobileType == 'firebrugade' || $_automobileType == 'firebrigade' || $_automobileType == 'firetruck') {
            $automobileFieldKey = 'is_firebrugade';
            $automobileFieldValue = 'is_firebrugade_approved';
        } else if ($_automobileType == 'delivery') {
            $automobileFieldKey = 'is_delivery';
            $automobileFieldValue = 'is_delivery_approved';
        } else if ($_automobileType == 'breakdown') {
            $automobileFieldKey = 'is_breakdown';
            $automobileFieldValue = 'is_breakdown_approved';
        }
        if ($automobileFieldKey == null || $automobileFieldValue == null) {
            return $this->error('Invalid automobile type specified.');
        }

        if ($r->current_address == null) {
            return $this->error('You have not specified your current address.');
        }

        // Validate GPS coordinates format
        if (!str_contains($r->current_address, ',')) {
            return $this->error('Invalid current address format. GPS coordinates required.');
        }

        // Extract customer coordinates for distance calculation
        $customerCoords = explode(',', $r->current_address);
        if (count($customerCoords) != 2) {
            return $this->error('Invalid GPS coordinates format.');
        }


        $customerLat = (float) trim($customerCoords[0]);
        $customerLng = (float) trim($customerCoords[1]);

        // Fetch up to 1000 online drivers with valid GPS coordinates
        $drivers = Administrator::where('status', 1)
            ->where('ready_for_trip', 'Yes')
            ->where('id', '!=', $u->id)
            ->whereNotNull('current_address') 
            ->where($automobileFieldKey, 'Yes') 
            ->where($automobileFieldValue, 'Yes')
            ->limit(1000) // Increased from 25 to 1000
            ->orderBy('updated_at', 'desc') // Get most recently active drivers first
            ->get();

        $data = [];
        $driversWithDistance = [];

        // Calculate distance for each driver and prepare data
        foreach ($drivers as $key => $driver) {
            // Skip drivers with invalid coordinates
            if (!str_contains($driver->current_address, ',')) {
                continue;
            }

            $driverCoords = explode(',', $driver->current_address);
            if (count($driverCoords) != 2) {
                continue;
            }

            $driverLat = (float) trim($driverCoords[0]);
            $driverLng = (float) trim($driverCoords[1]);

            // Skip if coordinates are invalid (0,0 or malformed)
            if ($driverLat == 0 && $driverLng == 0) {
                continue;
            }

            // Calculate distance using Haversine formula
            $distance = Utils::haversineDistance($r->current_address, $driver->current_address);

            // Skip drivers that are too far away (optional: limit to reasonable distance)
            // You can uncomment this to limit to drivers within 50km
            // if ($distance > 50) {
            //     continue;
            // }

            // Add distance to driver object
            $driver->distance = round($distance, 2); // Round to 2 decimal places

            // Calculate estimated travel time
            $min_speed = 60; // km/h
            $max_speed = 80; // km/h

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

            // Store driver with distance for sorting
            $driversWithDistance[] = [
                'driver' => $driver,
                'distance' => $distance
            ];
        }

        // Sort drivers by distance (closest first)
        usort($driversWithDistance, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        // Extract sorted drivers
        foreach ($driversWithDistance as $item) {
            $data[] = $item['driver'];
        }

        $message = count($data) > 0
            ? "Found " . count($data) . " drivers nearby, sorted by distance."
            : "No available drivers found in your area.";

        return $this->success($data, $message, 1);
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

        // Comprehensive validation for trip creation
        if ($r->price == null || trim($r->price) == '') {
            return $this->error('Price is required.');
        }

        if (!is_numeric($r->price) || $r->price <= 0) {
            return $this->error('Price must be a valid number greater than 0.');
        }

        if ($r->slots == null || trim($r->slots) == '') {
            return $this->error('Available slots is required.');
        }

        if (!is_numeric($r->slots) || $r->slots <= 0) {
            return $this->error('Available slots must be a valid number greater than 0.');
        }

        if ($r->slots > 50) {
            return $this->error('Available slots cannot exceed 50.');
        }

        if ($r->start_time == null || trim($r->start_time) == '') {
            return $this->error('Departure date is required.');
        }

        if ($r->start_name == null || trim($r->start_name) == '') {
            return $this->error('Start location name is required.');
        }

        if ($r->end_name == null || trim($r->end_name) == '') {
            return $this->error('End location name is required.');
        }

        if ($r->start_gps == null || trim($r->start_gps) == '') {
            return $this->error('Start GPS coordinates are required.');
        }

        if ($r->end_pgs == null || trim($r->end_pgs) == '') {
            return $this->error('End GPS coordinates are required.');
        }

        if ($r->vehicel_reg_number == null || trim($r->vehicel_reg_number) == '') {
            return $this->error('Vehicle registration number is required.');
        }

        if ($r->car_model == null || trim($r->car_model) == '') {
            return $this->error('Car model is required.');
        }

        // Validate date format
        try {
            $departureDate = Carbon::parse($r->start_time);
            if ($departureDate->isPast()) {
                return $this->error('Departure date cannot be in the past.');
            }
        } catch (\Exception $e) {
            return $this->error('Invalid departure date format.');
        }

        $trip = new Trip();
        $trip->driver_id = $u->id;
        $trip->customer_id = $u->id;
        $trip->start_stage_id = 1;
        $trip->end_stage_id = 1;
        $trip->scheduled_start_time = Carbon::parse($r->start_time);
        $trip->scheduled_end_time = $r->arrival_date;
        $trip->start_time = null;
        $trip->end_time = null;
        $trip->status = 'Pending';
        $trip->vehicel_reg_number = $r->vehicel_reg_number;
        $trip->slots = (int)$r->slots; // Ensure it's stored as integer
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
        try {
            JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);

            // Generate token directly for the authenticated user
            $customClaims = ['sub' => $u->id, 'user_id' => $u->id];
            $token = JWTAuth::claims($customClaims)->fromUser($u);

            if ($token == null) {
                // Fallback: try with different approach
                $payload = JWTAuth::factory()->sub($u->id)->make();
                $token = JWTAuth::encode($payload);
            }

            if ($token == null) {
                return $this->error('Authentication failed. Please try again.');
            }
        } catch (\Exception $e) {
            return $this->error('Authentication failed: ' . $e->getMessage());
        }

        $u->remember_token = $token;
        $u->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $u->save();

        // Prepare response data with token
        $responseData = $u->toArray();
        $responseData['token'] = $token; // Add token field that mobile app expects

        return $this->success($responseData, 'Logged in successfully.');
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

        // Refresh the user model to ensure all attributes are properly loaded
        $user = $user->fresh();

        // Generate JWT token for immediate login
        try {
            JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);

            // Try alternative token generation approach
            $customClaims = ['sub' => $user->id, 'user_id' => $user->id];
            $token = JWTAuth::claims($customClaims)->fromUser($user);

            if ($token == null) {
                // Fallback: try with different approach
                $payload = JWTAuth::factory()->sub($user->id)->make();
                $token = JWTAuth::encode($payload);
            }

            if ($token == null) {
                Log::error('JWT token generation failed for user: ' . $user->id);
                return $this->error('Account created but failed to generate authentication token. Please sign in manually.');
            }

            $user->remember_token = $token;
            $user->save();

            // Prepare response data with token
            $responseData = $user->toArray();
            $responseData['token'] = $token; // Add token field that mobile app expects

            Log::info('User registered successfully with token: ' . $user->id);
            return $this->success($responseData, 'Account created and logged in successfully.');
        } catch (\Exception $e) {
            // If JWT generation fails, still return success for account creation
            Log::error('JWT token generation exception for user ' . $user->id . ': ' . $e->getMessage());
            return $this->error('Account created successfully but automatic login failed: ' . $e->getMessage() . '. Please sign in manually.');
        }
    }

    /**
     * Get all bookings for trips owned by the authenticated driver
     * This endpoint allows drivers to manage passenger requests for their trips
     */
    public function trips_driver_bookings(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        // Get trip ID filter if provided
        $tripId = $r->trip_id;

        // Base query: Get bookings for trips where user is the driver
        $bookingsQuery = TripBooking::whereHas('trip', function ($query) use ($u) {
            $query->where('driver_id', $u->id);
        });

        // Filter by specific trip if requested
        if ($tripId) {
            $bookingsQuery->where('trip_id', $tripId);
        }

        // Get bookings with related data
        $bookings = $bookingsQuery->with(['trip', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group bookings by trip for better organization
        $groupedBookings = [];
        foreach ($bookings as $booking) {
            $tripId = $booking->trip_id;
            if (!isset($groupedBookings[$tripId])) {
                $groupedBookings[$tripId] = [
                    'trip' => $booking->trip,
                    'bookings' => []
                ];
            }
            $groupedBookings[$tripId]['bookings'][] = $booking;
        }

        return $this->success([
            'bookings' => $bookings,
            'grouped_by_trip' => array_values($groupedBookings),
            'total_requests' => $bookings->count()
        ], 'Success');
    }

    /**
     * Enhanced trip update with detailed editing capabilities for drivers
     */
    public function trips_update_detailed(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (empty($r->id)) {
            return $this->error('Trip ID is required.');
        }

        $trip = Trip::find($r->id);
        if ($trip == null) {
            return $this->error('Trip not found.');
        }

        // Verify the user is the driver of this trip
        if ($trip->driver_id != $u->id) {
            return $this->error('You are not authorized to update this trip.');
        }

        // Update basic trip information
        if ($r->has('status')) {
            // Validate status transitions
            $validStatuses = ['Pending', 'Active', 'Completed', 'Canceled'];
            if (!in_array($r->status, $validStatuses)) {
                return $this->error('Invalid status provided.');
            }

            // Check if status change is allowed
            if ($trip->status == 'Completed' && $r->status != 'Completed') {
                return $this->error('Cannot change status of a completed trip.');
            }

            $oldStatus = $trip->status;
            $trip->status = $r->status;

            // Handle status-specific logic
            if ($r->status == 'Active' && $oldStatus != 'Active') {
                $trip->start_time = now();
                // Notify all passengers
                $bookings = TripBooking::where('trip_id', $trip->id)
                    ->where('status', 'Reserved')
                    ->get();
                foreach ($bookings as $booking) {
                    if ($booking->customer && $booking->customer->phone_number) {
                        Utils::send_message(
                            $booking->customer->phone_number,
                            "RIDESHARE! Your trip has started. Driver: {$u->name}, Vehicle: {$trip->vehicel_reg_number}"
                        );
                    }
                }
            } elseif ($r->status == 'Completed' && $oldStatus != 'Completed') {
                $trip->end_time = now();
                // Mark all active bookings as completed
                TripBooking::where('trip_id', $trip->id)
                    ->where('status', 'Reserved')
                    ->update(['status' => 'Completed']);
            }
        }

        // Update trip details if provided
        if ($r->has('price') && is_numeric($r->price)) {
            $trip->price = $r->price;
        }

        if ($r->has('slots') && is_numeric($r->slots)) {
            // Check if reducing slots would conflict with existing bookings
            $bookedSlots = TripBooking::where('trip_id', $trip->id)
                ->whereIn('status', ['Pending', 'Reserved'])
                ->sum('slot_count');

            if ($r->slots < $bookedSlots) {
                return $this->error("Cannot reduce slots below {$bookedSlots} (currently booked).");
            }
            $trip->slots = $r->slots;
        }

        if ($r->has('details')) {
            $trip->details = $r->details;
        }

        if ($r->has('scheduled_start_time')) {
            try {
                $trip->scheduled_start_time = Carbon::parse($r->scheduled_start_time);
            } catch (\Exception $e) {
                return $this->error('Invalid scheduled start time format.');
            }
        }

        if ($r->has('scheduled_end_time')) {
            try {
                $trip->scheduled_end_time = Carbon::parse($r->scheduled_end_time);
            } catch (\Exception $e) {
                return $this->error('Invalid scheduled end time format.');
            }
        }

        try {
            $trip->save();
        } catch (\Throwable $th) {
            return $this->error('Failed to update trip: ' . $th->getMessage());
        }

        return $this->success($trip, 'Trip updated successfully.');
    }

    /**
     * Enhanced booking status update for drivers with comprehensive passenger management
     */
    public function trips_booking_status_update(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (empty($r->booking_id)) {
            return $this->error('Booking ID is required.');
        }

        if (empty($r->status)) {
            return $this->error('Status is required.');
        }

        $booking = TripBooking::find($r->booking_id);
        if ($booking == null) {
            return $this->error('Booking not found.');
        }

        // Verify the user is the driver of this trip
        if ($booking->trip->driver_id != $u->id) {
            return $this->error('You are not authorized to update this booking.');
        }

        // Validate status
        $validStatuses = ['Pending', 'Reserved', 'Canceled', 'Completed'];
        if (!in_array($r->status, $validStatuses)) {
            return $this->error('Invalid status provided.');
        }

        $oldStatus = $booking->status;
        $newStatus = $r->status;

        // Prevent invalid status transitions
        if ($oldStatus == 'Completed' && $newStatus != 'Completed') {
            return $this->error('Cannot change status of a completed booking.');
        }

        // Handle slot management for status changes
        if ($oldStatus != $newStatus) {
            if ($newStatus == 'Canceled' && $oldStatus != 'Canceled') {
                // Return slots to trip when canceled
                $booking->trip->slots += $booking->slot_count;
                $booking->trip->save();
            } elseif ($oldStatus == 'Canceled' && $newStatus != 'Canceled') {
                // Check if slots are available when un-canceling
                $availableSlots = $booking->trip->slots;
                if ($availableSlots < $booking->slot_count) {
                    return $this->error("Not enough available slots. Available: {$availableSlots}, Required: {$booking->slot_count}");
                }
                $booking->trip->slots -= $booking->slot_count;
                $booking->trip->save();
            }
        }

        $booking->status = $newStatus;

        // Add optional driver notes
        if ($r->has('driver_notes')) {
            $booking->driver_notes = $r->driver_notes;
        }

        try {
            $booking->save();
        } catch (\Throwable $th) {
            return $this->error('Failed to update booking: ' . $th->getMessage());
        }

        // Send notification to customer
        $customer = $booking->customer;
        if ($customer && $customer->phone_number) {
            $statusMessages = [
                'Reserved' => "Your trip booking has been confirmed by the driver. Get ready!",
                'Canceled' => "Your trip booking has been canceled by the driver. Please book another trip.",
                'Completed' => "Your trip has been completed. Thank you for riding with us!"
            ];

            if (isset($statusMessages[$newStatus])) {
                Utils::send_message(
                    $customer->phone_number,
                    "RIDESHARE! " . $statusMessages[$newStatus]
                );
            }
        }

        return $this->success($booking, 'Booking status updated successfully.');
    }

    /**
     * Get all trips created by the authenticated driver with booking statistics
     */
    public function trips_my_driver_trips(Request $r)
    {
        $query = auth('api')->user();
        $u = Administrator::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        // Get trips created by this driver
        $trips = Trip::where('driver_id', $u->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Add booking statistics to each trip
        foreach ($trips as $trip) {
            $bookings = TripBooking::where('trip_id', $trip->id)->get();

            $trip->total_bookings = $bookings->count();
            $trip->pending_bookings = $bookings->where('status', 'Pending')->count();
            $trip->reserved_bookings = $bookings->where('status', 'Reserved')->count();
            $trip->completed_bookings = $bookings->where('status', 'Completed')->count();
            $trip->canceled_bookings = $bookings->where('status', 'Canceled')->count();

            $trip->booked_slots = $bookings->whereIn('status', ['Pending', 'Reserved'])
                ->sum('slot_count');
            $trip->available_slots = $trip->slots - $trip->booked_slots;

            $trip->total_revenue = $bookings->whereIn('status', ['Reserved', 'Completed'])
                ->sum('price');
        }

        return $this->success([
            'trips' => $trips,
            'total_trips' => $trips->count(),
            'summary' => [
                'active_trips' => $trips->where('status', 'Pending')->count(),
                'completed_trips' => $trips->where('status', 'Completed')->count(),
                'total_revenue' => $trips->sum('total_revenue')
            ]
        ], 'Success');
    }
}
