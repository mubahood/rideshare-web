<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use App\Models\Utils;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Auth;
use App\Admin\Extensions\Nav\Shortcut;
use App\Admin\Extensions\Nav\Dropdown;
use App\Models\User;

//generate a users

$faker = Faker\Factory::create();


$coordinates = array(
    "1.3733,32.2903",
    "0.7107,33.6428",
    "0.6175,30.4206",
    "0.9787,31.7749",
    "1.9482,32.4726",
    "0.6975,32.9202",
    "1.4206,33.1206",
    "0.9983,33.9466",
    "1.5854,32.4976",
    "0.5546,32.3669",
    "0.8951,33.3657",
    "0.0907,31.8876",
    "0.3012,32.5807",
    "1.0419,32.3095",
    "1.2996,32.5733",
    "0.0651,33.4712",
    "1.1615,32.8922",
    "1.0564,32.4494",
    "0.4782,32.8972",
    "0.3707,32.0418",
    "0.6243,33.2183",
    "1.6898,33.3424",
    "1.0678,32.6613",
    "1.7337,31.7685",
    "0.8535,31.7935",
    "0.8326,31.4482",
    "1.5092,31.8834",
    "0.3003,32.6033",
    "0.9685,31.8121",
    "1.1408,33.5209",
    "0.9071,32.1962",
    "0.7573,32.7041",
    "1.2485,32.0429",
    "0.3962,31.9488",
    "0.8489,32.3366",
    "0.8784,31.3575",
    "1.1983,32.7786",
    "1.6168,32.4246",
    "0.5509,31.7295",
    "0.6897,33.2904",
    "0.1674,33.6673",
    "1.2092,33.5103",
    "1.2449,31.4674",
    "0.1527,31.9769",
    "0.9206,32.0194",
    "0.3215,31.8692",
    "1.3008,32.0735",
    "1.0902,33.3241",
    "1.5404,32.6989",
    "0.6545,31.7875",
    "0.3421,32.6457",
    "0.4103,32.2643",
    "1.0679,32.1792",
    "1.5527,31.7613",
    "1.4716,31.9849",
    "1.1249,32.7748",
    "1.0519,33.1275",
    "1.3083,31.7053",
    "1.4691,32.5088",
    "1.0968,31.7131",
    "1.2719,31.8768",
    "0.8414,31.6655",
    "1.4743,32.8897",
    "1.4829,32.3361",
    "1.6484,32.0998",
    "1.7394,31.6307",
    "1.3938,32.2156",
    "1.2927,31.6498",
    "0.4917,32.1761",
    "1.2536,32.9357",
    "1.0148,31.9281",
    "1.4992,32.7272",
    "1.6513,33.0434",
    "0.6371,32.0072",
    "0.7063,31.5739",
    "1.2478,32.5208",
    "1.3647,32.1574",
    "1.7624,31.8641",
    "1.3708,32.9894",
    "0.7823,31.5203",
    "1.1803,32.0661",
    "0.2815,31.6211",
    "1.2286,33.3978",
    "1.7456,32.3067",
    "0.8341,33.4542",
    "0.9376,32.5709",
    "0.5899,31.8202",
    "1.4863,31.7811",
    "0.4779,32.7689",
    "1.3815,32.6426",
    "1.4063,32.3953",
    "0.4069,33.3013",
    "1.7268,32.4436",
    "1.1492,32.2706",
    "1.5405,32.9964",
    "1.1041,31.6507",
    "1.0971,32.8425",
    "0.9984,31.6608",
    "1.3012,32.2437",
    "1.0569,32.8698"
);

// Example usage
$my_coord = "0.302258,32.609356";


foreach (User::all() as $key => $value) {
    $value->phone_number = str_replace('+255', '+25', $value->phone_number);
    $value->phone_number_2 = str_replace('+255', '+25', $value->phone_number_2);
    $value->save();
}


/* foreach (User::where([])->orderBy('id', 'desc')->get()->take(100) as $key => $u) {

    $distance = Utils::haversineDistance($my_coord, $u->current_address);

    $min_speed = 30;
    $max_speed = 50;

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

    echo $distance . " - " . $min_word . " - " . $max_word . "<br>";
    continue;


    $u->current_address = $coordinates[rand(0, count($coordinates) - 1)];
    //$u->user_type = 'Driver';
    $u->status = 1;
    $u->save();
}
 */
//die();

/* foreach (range(1, 100) as $index) {
    $u = new \App\Models\User();
    $u->username = $faker->email;
    $u->password = bcrypt('1234');
    $u->name = 'Test ' . $faker->name;
    $u->avatar = 'https://randomuser.me/api/portraits/' . ['women', 'men'][rand(0, 1)] . '/' . rand(1, 100) . '.jpg';
    $u->created_at = $faker->dateTimeBetween('-1 years', 'now');
    $u->updated_at = $faker->dateTimeBetween('-1 months', 'now');
    $u->enterprise_id = 1;
    $u->first_name = $faker->firstName;
    $u->last_name = $faker->lastName;
    $u->date_of_birth = $faker->dateTimeBetween('-30 years', '-18 years');
    $u->place_of_birth = $faker->address();
    $u->home_address = $faker->address();
    $u->sex = ['Women', 'Men'][rand(0, 1)];

    $lstDigit = $index;
    if(strlen($lstDigit) == 1){
        $lstDigit = '00'.$lstDigit;
    }else if(strlen($lstDigit) == 2){
        $lstDigit = '0'.$lstDigit;
    }
    $u->phone_number = '+2556783204'.$lstDigit;
    $u->phone_number_2 = $u->phone_number;
    $u->current_address = $coordinates[rand(0, count($coordinates) - 1)];
    $u->email = $faker->email;
    $u->otp = '1234';
    $u->nin = $faker->ean8;
    $u->user_type = ['Admin', 'Driver', 'Customer'][rand(0, 2)];
    $u->status = [1, 0][rand(0, 1)];
    $u->driving_license_number = $faker->ean8;
    $u->driving_license_issue_date = $faker->dateTimeBetween('-1 years', 'now');
    $u->driving_license_validity = $faker->dateTimeBetween('now', '+1 years');
    $u->driving_license_issue_authority = $faker->company;
    $u->driving_license_photo = $faker->imageUrl();
    $u->automobile = ['Special Car', 'Taxi', 'Ambulance', 'Bodaboda'][rand(0, 3)];
    $u->save();
} */

Utils::system_boot();


Admin::css('/assets/js/calender/main.css');
Admin::js('/assets/js/calender/main.js');

Admin::css('/css/jquery-confirm.min.css');
Admin::js('/assets/js/jquery-confirm.min.js');
Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {

    /*     $u = Auth::user();
    $navbar->left(view('admin.search-bar', [
        'u' => $u
    ]));

    $navbar->left(Shortcut::make([
        'News post' => 'news-posts/create',
        'Products or Services' => 'products/create',
        'Jobs and Opportunities' => 'jobs/create',
        'Event' => 'events/create',
    ], 'fa-plus')->title('ADD NEW'));
    $navbar->left(Shortcut::make([
        'Person with disability' => 'people/create',
        'Association' => 'associations/create',
        'Group' => 'groups/create',
        'Service provider' => 'service-providers/create',
        'Institution' => 'institutions/create',
        'Counselling Centre' => 'counselling-centres/create',
    ], 'fa-wpforms')->title('Register new'));

    $navbar->left(new Dropdown());

    $navbar->right(Shortcut::make([
        'How to update your profile' => '',
        'How to register a new person with disability' => '',
        'How to register as service provider' => '',
        'How to register to post a products & services' => '',
        'How to register to apply for a job' => '',
        'How to register to use mobile App' => '',
        'How to register to contact us' => '',
        'How to register to give a testimonial' => '',
        'How to register to contact counselors' => '',
    ], 'fa-question')->title('HELP')); */
});




Encore\Admin\Form::forget(['map', 'editor']);
Admin::css(url('/assets/css/bootstrap.css'));
Admin::css('/css/styles.css');
Admin::css('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css');
