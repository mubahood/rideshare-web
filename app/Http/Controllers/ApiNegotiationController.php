<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Negotiation;
use App\Models\NegotiationRecord;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ApiNegotiationController extends Controller
{
    use ApiResponser;

    /**
     * Simple test endpoint for debugging
     */
    public function debugTest(Request $r)
    {
        $user = auth('api')->user();
        $authHeader = $r->header('Authorization');
        $bearerToken = $r->bearerToken();
        $allHeaders = function_exists('getallheaders') ? getallheaders() : [];
        
        return response()->json([
            'authenticated' => $user !== null,
            'user_id' => $user ? $user->id : null,
            'request_data' => $r->all(),
            'auth_header' => $authHeader,
            'bearer_token' => $bearerToken ? substr($bearerToken, 0, 20) . '...' : null,
            'laravel_headers' => $r->headers->all(),
            'getallheaders' => $allHeaders,
            'server_vars' => [
                'HTTP_AUTHORIZATION' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
                'REDIRECT_HTTP_AUTHORIZATION' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null
            ]
        ]);
    }

    /**
     * Test endpoint to verify controller is working
     */
    public function test()
    {
        $user = auth('api')->user();
        return $this->success([
            'message' => 'Controller is working',
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'total_negotiations' => Negotiation::count()
        ], 'Test successful');
    }
    

    /**
     * Create a new negotiation between customer and driver
     */
    public function create(Request $r)
    {
        // Get authenticated user
        $customer = auth('api')->user();
        if ($customer == null) {
            return $this->error('User not authenticated.');
        }

        // Validate input data
        $validator = Validator::make($r->all(), [
            'driver_id' => 'required|integer|exists:admin_users,id',
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'pickup_address' => 'required|string|max:500',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
            'dropoff_address' => 'required|string|max:500',
            'initial_price' => 'required|numeric|min:1000',
            'message_body' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        // Find driver
        $driver = Administrator::find($r->driver_id);
        if ($driver == null) {
            return $this->error('Driver not found.');
        }

        // Check if driver is online and available
        if ($driver->ready_for_trip !== 'Yes') {
            return $this->error('Driver is currently not available for trips.');
        }

        // Check if customer already has an active negotiation
        $existingCustomerNeg = Negotiation::where('customer_id', $customer->id)
            ->where('is_active', 'Yes')
            ->first();
        
        if ($existingCustomerNeg) {
            return $this->error('You already have an active trip negotiation. Please complete or cancel it first.');
        }

        // Check if driver already has an active negotiation
        $existingDriverNeg = Negotiation::where('driver_id', $driver->id)
            ->where('is_active', 'Yes')
            ->first();
        
        if ($existingDriverNeg) {
            return $this->error('Driver is currently busy with another trip. Please try another driver.');
        }

        try {
            // Create new negotiation
            $negotiation = new Negotiation();
            $negotiation->customer_id = $customer->id;
            $negotiation->customer_name = $customer->name;
            $negotiation->driver_id = $driver->id;
            $negotiation->driver_name = $driver->name;
            $negotiation->status = 'Active';
            $negotiation->customer_accepted = 'Pending';
            $negotiation->customer_driver = 'Pending';
            $negotiation->pickup_lat = $r->pickup_lat;
            $negotiation->pickup_lng = $r->pickup_lng;
            $negotiation->pickup_address = $r->pickup_address;
            $negotiation->dropoff_lat = $r->dropoff_lat;
            $negotiation->dropoff_lng = $r->dropoff_lng;
            $negotiation->dropoff_address = $r->dropoff_address;
            $negotiation->details = $r->message_body;
            $negotiation->save();

            if ($negotiation->id < 1) {
                return $this->error('Failed to create negotiation.');
            }

            // Debug log the created negotiation
            Log::info('Negotiation created successfully', [
                'negotiation_id' => $negotiation->id,
                'customer_id' => $negotiation->customer_id,
                'driver_id' => $negotiation->driver_id
            ]);

            // Create initial negotiation record
            $record = new NegotiationRecord();
            $record->negotiation_id = $negotiation->id;
            $record->customer_id = $customer->id;
            $record->driver_id = $driver->id;
            $record->last_negotiator_id = $customer->id;
            $record->first_negotiator_id = $customer->id;
            $record->price = (float)$r->initial_price;
            $record->price_accepted = 'No';
            $record->message_type = 'Negotiation';
            $record->message_body = $r->message_body;
            $record->is_received = 'No';
            $record->is_seen = 'No';
            $record->save();

            // Send notification to driver
            if (!empty($driver->phone_number)) {
                try {
                    Utils::send_message(
                        $driver->phone_number,
                        "RIDESHARE! New trip request from {$customer->name}. Price: UGX " . number_format($r->initial_price) . ". Open the app to respond."
                    );
                } catch (\Throwable $smsError) {
                    Log::warning('Failed to send SMS notification: ' . $smsError->getMessage());
                }
            }

            return $this->success($negotiation, 'Negotiation created successfully.');

        } catch (\Throwable $e) {
            Log::error('Negotiation creation failed: ' . $e->getMessage());
            return $this->error('Failed to create negotiation: ' . $e->getMessage());
        }
    }

    /**
     * Get negotiation updates
     */
    public function updates(Request $r)
    {
        // Try to authenticate manually first
        $user = auth('api')->user();
        if ($user == null) {
            // Try manual authentication with JWT
            try {
                $headers = function_exists('getallheaders') ? getallheaders() : [];
                $authHeader = '';
                
                if (isset($headers['Authorization'])) {
                    $authHeader = $headers['Authorization'];
                } elseif (isset($headers['authorization'])) {
                    $authHeader = $headers['authorization'];
                } elseif (isset($headers['Tok'])) {
                    $authHeader = $headers['Tok'];
                } elseif (isset($headers['tok'])) {
                    $authHeader = $headers['tok'];
                }
                
                if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                    $token = substr($authHeader, 7);
                    $user = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->authenticate();
                }
            } catch (Exception $e) {
                // Ignore JWT errors for now
            }
        }
        
        if ($user == null) {
            return $this->error('User not authenticated.');
        }

        // Support both 'id' and 'negotiation_id' parameters for compatibility
        $negotiationId = $r->negotiation_id ?? $r->id;
        
        if (empty($negotiationId)) {
            return $this->error('Negotiation ID is required.');
        }

        // Debug: Log the request details
        Log::info('Negotiation updates request', [
            'user_id' => $user->id,
            'requested_negotiation_id' => $negotiationId,
            'request_data' => $r->all()
        ]);

        // Try to find the negotiation with detailed debugging
        $negotiation = Negotiation::find($negotiationId);
        if ($negotiation == null) {
            // Simple debug - log the requested ID and count total negotiations
            $totalCount = Negotiation::count();
            $latestNegotiations = Negotiation::orderBy('id', 'desc')->limit(5)->get(['id', 'customer_id', 'driver_id', 'status']);
            $userNegotiations = Negotiation::where(function($query) use ($user) {
                $query->where('customer_id', $user->id)->orWhere('driver_id', $user->id);
            })->get(['id', 'customer_id', 'driver_id', 'status']);
            
            // Return debug info for now
            return response()->json([
                'code' => 0,
                'message' => 'Negotiation not found.',
                'data' => null,
                'debug' => [
                    'requested_id' => $negotiationId,
                    'user_id' => $user->id,
                    'total_count' => $totalCount,
                    'latest_negotiations' => $latestNegotiations->toArray(),
                    'user_negotiations' => $userNegotiations->toArray(),
                    'specific_negotiation' => Negotiation::find($negotiationId) ? 'Found' : 'Not Found',
                    'negotiation_40_exists' => Negotiation::where('id', 40)->exists() ? 'Yes' : 'No'
                ]
            ]);
        }

        // Check if user is part of this negotiation
        if ($negotiation->customer_id != $user->id && $negotiation->driver_id != $user->id) {
            Log::warning('User not authorized for negotiation', [
                'user_id' => $user->id,
                'negotiation_customer_id' => $negotiation->customer_id,
                'negotiation_driver_id' => $negotiation->driver_id,
                'negotiation_id' => $negotiation->id
            ]);
            return $this->error('You are not authorized to access this negotiation.');
        }

        // Manually construct the response to ensure all fields are present
        $response = [
            'id' => $negotiation->id,
            'created_at' => $negotiation->created_at,
            'updated_at' => $negotiation->updated_at,
            'customer_id' => $negotiation->customer_id,
            'customer_name' => $negotiation->customer_name,
            'driver_id' => $negotiation->driver_id,
            'driver_name' => $negotiation->driver_name,
            'status' => $negotiation->status,
            'customer_accepted' => $negotiation->customer_accepted,
            'customer_driver' => $negotiation->customer_driver,
            'pickup_lat' => $negotiation->pickup_lat,
            'pickup_lng' => $negotiation->pickup_lng,
            'pickup_address' => $negotiation->pickup_address,
            'dropoff_lat' => $negotiation->dropoff_lat,
            'dropoff_lng' => $negotiation->dropoff_lng,
            'dropoff_address' => $negotiation->dropoff_address,
            'records' => $negotiation->records,
            'details' => $negotiation->details,
            'is_active' => $negotiation->is_active ?? 'Yes',
            'driver_phone' => $negotiation->driver_phone ?? '',
            'customer_phone' => $negotiation->customer_phone ?? ''
        ];

        return $this->success($response, 'Success');
    }

    /**
     * Cancel negotiation
     */
    public function cancel(Request $r)
    {
        $user = auth('api')->user();
        if ($user == null) {
            return $this->error('User not authenticated.');
        }

        if (empty($r->negotiation_id)) {
            return $this->error('Negotiation ID is required.');
        }

        $negotiation = Negotiation::find($r->negotiation_id);
        if ($negotiation == null) {
            return $this->error('Negotiation not found.');
        }

        // Check if user is part of this negotiation
        if ($negotiation->customer_id != $user->id && $negotiation->driver_id != $user->id) {
            return $this->error('You are not authorized to cancel this negotiation.');
        }

        // Check if negotiation can be canceled
        if ($negotiation->status === 'Started') {
            return $this->error('Cannot cancel a trip that has already started.');
        }

        if ($negotiation->status === 'Completed') {
            return $this->error('Cannot cancel a completed trip.');
        }

        try {
            $negotiation->status = 'Canceled';
            $negotiation->is_active = 'No';
            $negotiation->save();

            // Notify the other party
            $otherPartyId = ($negotiation->customer_id == $user->id) ? $negotiation->driver_id : $negotiation->customer_id;
            $otherParty = Administrator::find($otherPartyId);
            
            if ($otherParty && !empty($otherParty->phone_number)) {
                try {
                    Utils::send_message(
                        $otherParty->phone_number,
                        "RIDESHARE! Trip negotiation has been canceled by {$user->name}."
                    );
                } catch (\Throwable $smsError) {
                    Log::warning('Failed to send SMS notification: ' . $smsError->getMessage());
                }
            }

            return $this->success(null, 'Negotiation canceled successfully.');

        } catch (\Throwable $e) {
            Log::error('Negotiation cancellation failed: ' . $e->getMessage());
            return $this->error('Failed to cancel negotiation: ' . $e->getMessage());
        }
    }

    /**
     * Get all negotiations for authenticated user
     */
    public function index(Request $r)
    {
        $user = auth('api')->user();
        if ($user == null) {
            return $this->error('User not authenticated.');
        }

        $negotiations = Negotiation::where(function($query) use ($user) {
            $query->where('customer_id', $user->id)
                  ->orWhere('driver_id', $user->id);
        })
        ->orderBy('created_at', 'desc')
        ->get();

        return $this->success($negotiations, 'Success');
    }
}