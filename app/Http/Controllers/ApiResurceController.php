<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\CounsellingCentre;
use App\Models\Crop;
use App\Models\CropProtocol;
use App\Models\Event;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\Group;
use App\Models\Institution;
use App\Models\Job;
use App\Models\NewsPost;
use App\Models\Person;
use App\Models\Product;
use App\Models\RouteStage;
use App\Models\Sacco;
use App\Models\ServiceProvider;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiResurceController extends Controller
{

    use ApiResponser;

    public function saccos(Request $r)
    {
        return $this->success(
            Sacco::where([])->orderby('id', 'desc')->get(),
            $message = "Sussesfully",
            200
        );
    }

    public function sacco_join_request(Request $r)
    {

        $u = auth('api')->user();

        if ($u == null) {
            return $this->error('User not found.');
        }
        $sacco = Sacco::find($r->sacco_id);
        if ($sacco == null) {
            return $this->error('Sacco not found.');
        }
        $user = Administrator::find($u->id);
        $user->sacco_join_status = 'Pending';
        $user->save();
        return $this->success(
            'Sussesfully',
            $message = "Request submitted successfully.",
        );
    }

    public function crops(Request $r)
    {
        $items = [];

        foreach (Crop::all() as $key => $crop) {
            $protocols = CropProtocol::where([
                'crop_id' => $crop->id
            ])->get();
            $crop->protocols = json_encode($protocols);

            $items[] = $crop;
        }

        return $this->success(
            $items,
            $message = "Sussesfully",
            200
        );
    }

    public function garden_activities(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }

        $gardens = [];
        if ($u->isRole('agent')) {
            $gardens = GardenActivity::where([])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $gardens = GardenActivity::where(['user_id' => $u->id])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        }

        return $this->success(
            $gardens,
            $message = "Sussesfully",
            200
        );
    }

    public function my_sacco_membership(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        $members = Administrator::where(['id' => $u->id])
            ->limit(1)
            ->orderBy('id', 'desc')
            ->get();
        return $this->success(
            $members,
            $message = "Sussesfully",
            200
        );
    }
    public function sacco_members(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        $members = Administrator::where(['sacco_id' => $u->sacco_id])
            ->limit(1000)
            ->orderBy('id', 'desc')
            ->get();
        return $this->success(
            $members,
            $message = "Sussesfully",
            200
        );
    }
    public function sacco_members_review(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        $member = Administrator::find($r->member_id);
        if ($member == null) {
            return $this->error('Member not found.');
        }
        $member->sacco_join_status = $r->sacco_join_status;
        $member->save();
        return $this->success(
            null,
            $message = "Sussesfully",
            200
        );
    }

    public function gardens(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }

        $gardens = [];
        if ($u->isRole('agent')) {
            $gardens = Garden::where([])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $gardens = Garden::where(['user_id' => $u->id])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        }

        return $this->success(
            $gardens,
            $message = "Sussesfully",
            200
        );
    }



    public function people(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }

        return $this->success(
            Person::where(['administrator_id' => $u->id])
                ->limit(100)
                ->orderBy('id', 'desc')
                ->get(),
            $message = "Sussesfully",
            200
        );
    }
    public function jobs(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }

        return $this->success(
            Job::where([])
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get(),
            $message = "Sussesfully",
        );
    }


    public function activity_submit(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->activity_id == null ||
            $r->farmer_activity_status == null ||
            $r->farmer_comment == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $activity = GardenActivity::find($r->activity_id);

        if ($activity == null) {
            return $this->error('Activity not found.');
        }

        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
                $image = 'images/' . $image;
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        $activity->photo = $image;
        $activity->farmer_activity_status = $r->farmer_activity_status;
        $activity->farmer_comment = $r->farmer_comment;
        if ($r->activity_date_done != null && strlen($r->activity_date_done) > 2) {
            $activity->activity_date_done = Carbon::parse($r->activity_date_done);
            $activity->farmer_submission_date = Carbon::now();
            $activity->farmer_has_submitted = 'Yes';
        }



        try {
            $activity->save();
            return $this->success(null, $message = "Sussesfully created!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save activity, becase ' . $th->getMessage() . '');
        }
    }

    public function garden_create(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->planting_date == null ||
            $r->crop_id == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }


        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
                $image = 'images/' . $image;
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        $obj = new Garden();
        $obj->name = $r->name;
        $obj->user_id = $u->id;
        $obj->status = $r->status;
        $obj->production_scale = $r->production_scale;
        $obj->planting_date = Carbon::parse($r->planting_date);
        $obj->land_occupied = $r->planting_date;
        $obj->crop_id = $r->crop_id;
        $obj->details = $r->details;
        $obj->photo = $image;
        $obj->save();


        return $this->success(null, $message = "Sussesfully created!", 200);
    }

    public function product_create(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->category == null ||
            $r->price == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
                $image = 'images/' . $image;
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }




        $obj = new Product();
        $obj->name = $r->name;
        $obj->administrator_id = $u->id;
        $obj->type = $r->category;
        $obj->details = $r->details;
        $obj->price = $r->price;
        $obj->offer_type = $r->offer_type;
        $obj->state = $r->state;
        $obj->district_id = $r->district_id;
        $obj->subcounty_id = 1;
        $obj->photo = $image;

        try {
            $obj->save();
            return $this->success(null, $message = "Product Uploaded Sussesfully!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save product, becase ' . $th->getMessage() . '');
            //throw $th;
        }
    }

    public function otp_verify(Request $r)
    {
        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }
        $phone_number = Utils::prepare_phone_number(trim($r->phone_number));
        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        if ($r->otp == null) {
            return $this->error('OTP is required.');
        }

        $u = Administrator::where('phone_number', $phone_number)
            ->orWhere('username', $phone_number)->first();
        if ($u == null) {
            return $this->error('User account not found.');
        }

        $otp = ((int)($r->otp));
        if (((int)($u->otp)) != ((int)($r->otp))) {
            return $this->error('Invalid OTP.');
        }

        $u->password = password_hash($otp, PASSWORD_DEFAULT);
        $u->save();

        Config::set('jwt.ttl', 60 * 24 * 30 * 365);
        JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);
        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => $otp,
        ]);

        if ($token == null) {
            return $this->error('Wrong credentials.');
        }
        $u->token = $token;
        $u->remember_token = $token;

        return $this->success($u, 'Logged in successfully.');
    }

    public function request_otp(Request $r)
    {
        $admin = Administrator::find($r->user_id);
        if ($admin == null) {
            return $this->error('User not found.');
        }
        if ($admin->phone_number == null) {
            return $this->error('Phone number is required.');
        }
        $phone_number = $admin->phone_number;
        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        $otp = rand(1000, 9999);
        if (str_contains($phone_number, '783204')) {
            $otp = '1234';
            Utils::send_message($phone_number, 'Testing account detected. Use 1234 as OTP.');
        } else {
            $otp = rand(1000, 9999); 
            Utils::send_message($phone_number, $otp . ' is your CRSS verification code.');
        }

        return $this->success($otp, 'Verification code sent to your ' . $phone_number . '.');
    }




    public function otp_request(Request $r)
    {
        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }
        $phone_number = Utils::prepare_phone_number(trim($r->phone_number));
        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }
        $platform = $r->platform;


        $u = Administrator::where('phone_number', $phone_number)
            ->orWhere('username', $phone_number)->first();
        if ($u == null) {
            return $this->error('User account not found.');
        }

        $u->phone_number = $phone_number;

        if (str_contains($phone_number, '783204')) {
            //is testing account
            $u->status = 1;
            $u->password = password_hash('1234', PASSWORD_DEFAULT);
            $u->otp = '1234';
            $u->save();
            Utils::send_message($phone_number, 'Testing account detected. Use 1234 as OTP.');
            return $this->success($u, 'Testing account detected. Use 1234 as OTP.');
        } else {
            $resp = Utils::send_otp($u);
        }

        $u->save();
        $resp = Utils::send_otp($u);
        if (strlen($resp) > 0) {
            return $this->error($resp);
        }
        if ($platform == 'web') {
            die('OTP sent to ' . $phone_number . ".");
        }
        return $this->success(null, 'Verification code sent to your phone number.');
    }



    public function person_create(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->sex == null ||
            $r->subcounty_id == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
                $image = 'images/' . $image;
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        $obj = new Person();
        $obj->id = $r->id;
        $obj->created_at = $r->created_at;
        $obj->association_id = $r->association_id;
        $obj->administrator_id = $u->id;
        $obj->group_id = $r->group_id;
        $obj->name = $r->name;
        $obj->address = $r->address;
        $obj->parish = $r->parish;
        $obj->village = $r->village;
        $obj->phone_number = $r->phone_number;
        $obj->email = $r->email;
        $obj->district_id = $r->district_id;
        $obj->subcounty_id = $r->subcounty_id;
        $obj->disability_id = $r->disability_id;
        $obj->phone_number_2 = $r->phone_number_2;
        $obj->dob = $r->dob;
        $obj->sex = $r->sex;
        $obj->education_level = $r->education_level;
        $obj->employment_status = $r->employment_status;
        $obj->has_caregiver = $r->has_caregiver;
        $obj->caregiver_name = $r->caregiver_name;
        $obj->caregiver_sex = $r->caregiver_sex;
        $obj->caregiver_phone_number = $r->caregiver_phone_number;
        $obj->caregiver_age = $r->caregiver_age;
        $obj->caregiver_relationship = $r->caregiver_relationship;
        $obj->photo = $image;
        $obj->save();


        return $this->success(null, $message = "Sussesfully registered!", 200);
    }

    public function groups()
    {
        return $this->success(Group::get_groups(), 'Success');
    }


    public function associations()
    {
        return $this->success(Association::where([])->orderby('id', 'desc')->get(), 'Success');
    }

    public function institutions()
    {
        return $this->success(Institution::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function service_providers()
    {
        return $this->success(ServiceProvider::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function counselling_centres()
    {
        return $this->success(CounsellingCentre::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function products()
    {
        return $this->success(Product::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function events()
    {
        return $this->success(Event::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function news_posts()
    {
    }
    public function route_stages()
    {
        return $this->success(RouteStage::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function trips()
    {
        return $this->success(Trip::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function trips_bookings()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        return $this->success(
            TripBooking::where('driver_id', $u->id)
                ->orWhere('customer_id', $u->id)
                ->orderby('id', 'desc')->get(),
            'Success'
        );
    }


    public function index(Request $r, $model)
    {

        $className = "App\Models\\" . $model;
        $obj = new $className;

        if (isset($_POST['_method'])) {
            unset($_POST['_method']);
        }
        if (isset($_GET['_method'])) {
            unset($_GET['_method']);
        }

        $conditions = [];
        foreach ($_GET as $k => $v) {
            if (substr($k, 0, 2) == 'q_') {
                $conditions[substr($k, 2, strlen($k))] = trim($v);
            }
        }
        $is_private = true;
        if (isset($_GET['is_not_private'])) {
            $is_not_private = ((int)($_GET['is_not_private']));
            if ($is_not_private == 1) {
                $is_private = false;
            }
        }
        if ($is_private) {

            $u = $r->user;
            $administrator_id = $u->id;

            if ($u == null) {
                return $this->error('User not found.');
            }
            $conditions['administrator_id'] = $administrator_id;
        }

        $items = [];
        $msg = "";

        try {
            $items = $className::where($conditions)->get();
            $msg = "Success";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        if ($success) {
            return $this->success($items, 'Success');
        } else {
            return $this->error($msg);
        }
    }





    public function delete(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item already deleted.",
            ]);
        }


        try {
            $obj->delete();
            $msg = "Deleted successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }


    public function update(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item not found.",
            ]);
        }


        unset($_POST['_method']);
        if (isset($_POST['online_id'])) {
            unset($_POST['online_id']);
        }

        foreach ($_POST as $key => $value) {
            $obj->$key = $value;
        }


        $success = false;
        $msg = "";
        try {
            $obj->save();
            $msg = "Updated successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }
}
