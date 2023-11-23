<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiChatController;
use App\Http\Controllers\ApiResurceController;
use App\Http\Middleware\EnsureTokenIsValid;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('chat-heads', [ApiChatController::class, 'chat_heads']); //==>1 
Route::get('chat-messages', [ApiChatController::class, 'chat_messages']); //==>2 
Route::post('chat-send', [ApiChatController::class, 'chat_send']); //==>2 
Route::post('chat-heads-create', [ApiChatController::class, 'chat_heads_create']); //==>2 


Route::get("system-users", [ApiAuthController::class, "users"]);
Route::get("saccos", [ApiResurceController::class, "saccos"]);
Route::post("sacco-join-request", [ApiResurceController::class, "sacco_join_request"]);

Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::get("sacco-members", [ApiResurceController::class, "sacco_members"]);
    Route::post("sacco-members-review", [ApiResurceController::class, "sacco_members_review"]);
    Route::get("my-sacco-membership", [ApiResurceController::class, "my_sacco_membership"]);

    Route::get("gardens", [ApiResurceController::class, "gardens"]);
    Route::get("garden-activities", [ApiResurceController::class, "garden_activities"]);
    Route::get("garden-activities", [ApiResurceController::class, "garden_activities"]);
    Route::POST("gardens", [ApiResurceController::class, "garden_create"]);
    Route::POST("products", [ApiResurceController::class, "product_create"]);
    Route::POST("garden-activities", [ApiResurceController::class, "activity_submit"]);
});
Route::get("crops", [ApiResurceController::class, "crops"]);





Route::POST("users/login", [ApiAuthController::class, "login"]);
Route::POST("users/register", [ApiAuthController::class, "register"]);
Route::POST("otp-verify", [ApiResurceController::class, "otp_verify"]);
Route::POST("otp-request", [ApiResurceController::class, "otp_request"]);
Route::POST("request-otp", [ApiResurceController::class, "request_otp"]);
Route::get("otp-request", [ApiResurceController::class, "otp_request"]);
Route::get("users/me", [ApiAuthController::class, "me"]);
Route::POST("become-driver", [ApiAuthController::class, "become_driver"]);
Route::POST("register-farmer", [ApiAuthController::class, "register_farmer"]);
Route::POST("distribute-seed", [ApiAuthController::class, "distribute_seed"]);
Route::POST("people", [ApiResurceController::class, "person_create"]);
Route::get("jobs", [ApiResurceController::class, "jobs"]);
Route::get('api/{model}', [ApiResurceController::class, 'index']);
Route::get('groups', [ApiResurceController::class, 'groups']);
Route::get('associations', [ApiResurceController::class, 'associations']);
Route::get('institutions', [ApiResurceController::class, 'institutions']);
Route::get('service-providers', [ApiResurceController::class, 'service_providers']);
Route::get('counselling-centres', [ApiResurceController::class, 'counselling_centres']);
Route::get('products', [ApiResurceController::class, 'products']);
Route::get('events', [ApiResurceController::class, 'events']);
Route::get('news-posts', [ApiResurceController::class, 'news_posts']);
Route::get('route-stages', [ApiResurceController::class, 'route_stages']);
Route::get('trips', [ApiResurceController::class, 'trips']);
Route::get('trips-bookings', [ApiResurceController::class, 'trips_bookings']);
Route::POST("trips-create", [ApiAuthController::class, "trips_create"]);
Route::POST("trips-bookings-create", [ApiAuthController::class, "trips_bookings_create"]);
Route::POST("trips-bookings-update", [ApiAuthController::class, "trips_bookings_update"]);
Route::POST("trips-update", [ApiAuthController::class, "trips_update"]);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/user', function (Request $request) {
    return 'Testing';
});
Route::get('/users', function (Request $request) {
    $conditions = [];
    if ($request->has('q')) {
        $conditions[] = ['name', 'like', '%' . $request->q . '%'];
    }
    $districts = Administrator::where($conditions)->get();
    $data = [];
    foreach ($districts as $district) {
        $data[] = [
            'id' => $district->id,
            'text' => $district->name." - ".$district->phone_number
        ];
    }
    return response()->json([
        'data' => $data
    ]);
});

Route::get('/select-distcists', function (Request $request) {
    $conditions = [];
    if ($request->has('q')) {
        $conditions[] = ['name', 'like', '%' . $request->q . '%'];
    }
    $districts = \App\Models\DistrictModel::where($conditions)->get();
    $data = [];
    foreach ($districts as $district) {
        $data[] = [
            'id' => $district->id,
            'text' => $district->name
        ];
    }
    return response()->json([
        'data' => $data
    ]);
});


Route::get('/select-subcounties', function (Request $request) {
    $conditions = [];
    if ($request->has('q')) {
        if ($request->has('by_id')) {
            $conditions['district_id'] = ((int)($request->q));
        } else {
            $conditions[] = ['name', 'like', '%' . $request->q . '%'];
        }
    }
    $districts = \App\Models\SubcountyModel::where($conditions)->get();
    $data = [];
    foreach ($districts as $district) {
        $data[] = [
            'id' => $district->id,
            'text' => $district->name_text
        ];
    }
    return response()->json([
        'data' => $data
    ]);
});

Route::get('ajax', function (Request $r) {

    $_model = trim($r->get('model'));
    $conditions = [];
    foreach ($_GET as $key => $v) {
        if (substr($key, 0, 6) != 'query_') {
            continue;
        }
        $_key = str_replace('query_', "", $key);
        $conditions[$_key] = $v;
    }

    if (strlen($_model) < 2) {
        return [
            'data' => []
        ];
    }

    $model = "App\Models\\" . $_model;
    $search_by_1 = trim($r->get('search_by_1'));
    $search_by_2 = trim($r->get('search_by_2'));

    $q = trim($r->get('q'));

    $res_1 = $model::where(
        $search_by_1,
        'like',
        "%$q%"
    )
        ->where($conditions)
        ->limit(20)->get();
    $res_2 = [];

    if ((count($res_1) < 20) && (strlen($search_by_2) > 1)) {
        $res_2 = $model::where(
            $search_by_2,
            'like',
            "%$q%"
        )
            ->where($conditions)
            ->limit(20)->get();
    }

    $data = [];
    foreach ($res_1 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }
    foreach ($res_2 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }

    return [
        'data' => $data
    ];
});
