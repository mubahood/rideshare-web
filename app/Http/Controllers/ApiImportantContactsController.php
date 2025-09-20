<?php

namespace App\Http\Controllers;

use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiImportantContactsController extends ApiResurceController
{
    /**
     * Get important contacts based on user's current_address location
     * Returns contacts ordered by distance (nearest first)
     */
    public function getImportantContacts(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->error('User not authenticated.');
            }

            // Get user's current location from current_address field
            $userLocation = $user->current_address;
            if (!$userLocation || !str_contains($userLocation, ',')) {
                return $this->error('User location not available. Please update your location first.');
            }

            // Extract user coordinates
            $userCoords = explode(',', $userLocation);
            if (count($userCoords) != 2) {
                return $this->error('Invalid user location format.');
            }
            
            $userLat = (float) trim($userCoords[0]);
            $userLng = (float) trim($userCoords[1]);

            // Query parameters
            $limit = $request->input('limit', 1000);
            $serviceType = $request->input('service_type', null);
            $searchQuery = $request->input('search', null);

            // Build query for important contacts
            $query = Administrator::select([
                'id',
                'name',
                'email', 
                'phone_number',
                'phone_number_2',
                'current_address',
                'home_address',
                'is_ambulance',
                'is_police',
                'is_delivery', 
                'is_breakdown',
                'is_firebrugade',
                'sex',
                'avatar'
            ])
            ->whereNotNull('current_address')
            ->where('current_address', '!=', '')
            ->where(function($q) {
                $q->where('is_ambulance', '1')
                  ->orWhere('is_police', '1') 
                  ->orWhere('is_delivery', '1')
                  ->orWhere('is_breakdown', '1')
                  ->orWhere('is_firebrugade', '1');
            });

            // Apply service type filter
            if ($serviceType && $serviceType !== 'all') {
                switch ($serviceType) {
                    case 'ambulance':
                        $query->where('is_ambulance', '1');
                        break;
                    case 'police':
                        $query->where('is_police', '1');
                        break;
                    case 'delivery':
                        $query->where('is_delivery', '1');
                        break;
                    case 'breakdown':
                        $query->where('is_breakdown', '1');
                        break;
                    case 'firebrugade':
                        $query->where('is_firebrugade', '1');
                        break;
                }
            }

            // Apply search filter
            if ($searchQuery) {
                $query->where(function($q) use ($searchQuery) {
                    $q->where('name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('phone_number', 'like', '%' . $searchQuery . '%')
                      ->orWhere('phone_number_2', 'like', '%' . $searchQuery . '%');
                });
            }

            $contacts = $query->limit($limit)->get();

            // Calculate distances and sort by proximity
            $contactsWithDistance = [];
            foreach ($contacts as $contact) {
                if ($contact->current_address && str_contains($contact->current_address, ',')) {
                    // Calculate distance using existing haversineDistance method
                    $distance = Utils::haversineDistance($userLocation, $contact->current_address);
                    
                    // Extract contact coordinates for frontend use
                    $contactCoords = explode(',', $contact->current_address);
                    $contactLat = (float) trim($contactCoords[0]);
                    $contactLng = (float) trim($contactCoords[1]);

                    $contactsWithDistance[] = [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'email' => $contact->email,
                        'phone_number' => $contact->phone_number,
                        'phone_number_2' => $contact->phone_number_2,
                        'latitude' => $contactLat,
                        'longitude' => $contactLng,
                        'current_address' => $contact->current_address,
                        'home_address' => $contact->home_address,
                        'distance_km' => round($distance, 2),
                        'is_ambulance' => $contact->is_ambulance === '1',
                        'is_police' => $contact->is_police === '1',
                        'is_delivery' => $contact->is_delivery === '1',
                        'is_breakdown' => $contact->is_breakdown === '1',
                        'is_firebrugade' => $contact->is_firebrugade === '1',
                        'sex' => $contact->sex,
                        'avatar' => $contact->avatar,
                        'is_emergency' => ($contact->is_ambulance === '1' || $contact->is_police === '1' || $contact->is_firebrugade === '1'),
                    ];
                }
            }

            // Sort by distance (nearest first)
            usort($contactsWithDistance, function($a, $b) {
                return $a['distance_km'] <=> $b['distance_km'];
            });

            return $this->success([
                'contacts' => $contactsWithDistance,
                'user_location' => [
                    'latitude' => $userLat,
                    'longitude' => $userLng,
                ],
                'total_count' => count($contactsWithDistance),
                'filters_applied' => [
                    'service_type' => $serviceType,
                    'search_query' => $searchQuery,
                    'limit' => $limit
                ]
            ], 'Important contacts retrieved successfully.');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve contacts: ' . $e->getMessage());
        }
    }

    /**
     * Update user's current location in current_address field
     */
    public function updateLocation(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->error('User not authenticated.');
            }

            // Validate location data
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180'
            ]);

            if ($validator->fails()) {
                return $this->error('Invalid location parameters: ' . $validator->errors()->first());
            }

            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            // Update user's current_address with lat,lng format
            $currentAddress = $latitude . "," . $longitude;
            
            Administrator::where('id', $user->id)->update([
                'current_address' => $currentAddress,
                'updated_at' => Carbon::now()
            ]);

            return $this->success([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current_address' => $currentAddress,
                'updated_at' => Carbon::now(),
            ], 'Location updated successfully.');

        } catch (\Exception $e) {
            return $this->error('Failed to update location: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics about important contacts by service type
     */
    public function getStatistics(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->error('User not authenticated.');
            }

            // Get counts for each service type
            $statistics = [
                'total_contacts' => Administrator::where(function($q) {
                    $q->where('is_ambulance', 1)
                      ->orWhere('is_police', 1) 
                      ->orWhere('is_delivery', 1)
                      ->orWhere('is_breakdown', 1)
                      ->orWhere('is_firebrugade', 1);
                })->count(),
                
                'by_service_type' => [
                    'ambulance' => Administrator::where('is_ambulance', 1)->count(),
                    'police' => Administrator::where('is_police', 1)->count(),
                    'delivery' => Administrator::where('is_delivery', 1)->count(),
                    'breakdown' => Administrator::where('is_breakdown', 1)->count(),
                    'fire_brigade' => Administrator::where('is_firebrugade', 1)->count(),
                ],
                
                'contacts_with_location' => Administrator::whereNotNull('current_address')
                    ->where('current_address', '!=', '')
                    ->where(function($q) {
                        $q->where('is_ambulance', 1)
                          ->orWhere('is_police', 1) 
                          ->orWhere('is_delivery', 1)
                          ->orWhere('is_breakdown', 1)
                          ->orWhere('is_firebrugade', 1);
                    })->count(),
            ];

            return $this->success($statistics, 'Statistics retrieved successfully.');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve statistics: ' . $e->getMessage());
        }
    }
}