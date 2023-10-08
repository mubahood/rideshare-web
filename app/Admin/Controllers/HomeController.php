<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Faker\Factory as Faker;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use SplFileObject;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        $admin = Auth::user();

        $faker = Faker::create();

        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class, 'driver_id')->nullable();
            $table->foreignIdFor(Administrator::class, 'customer_id')->nullable();
            $table->foreignIdFor(RouteStage::class, 'start_stage_id')->nullable();
            $table->foreignIdFor(RouteStage::class, 'end_stage_id')->nullable();
            $table->string('scheduled_start_time')->nullable()->comment('Start time of the trip')->nullable();
            $table->string('scheduled_end_time')->nullable()->comment('End time of the trip')->nullable();
            $table->string('start_time')->nullable()->comment('Start time of the trip')->nullable();
            $table->string('end_time')->nullable()->comment('End time of the trip')->nullable();
            $table->string('status')->nullable()->comment('Status of the trip')->nullable();
            $table->string('vehicel_reg_number')->nullable()->comment('Status of the trip')->nullable();
            $table->integer('slots')->nullable()->comment('Number of slots')->nullable();
            $table->text('details')->nullable()->comment('Details of the trip')->nullable();
            $table->text('car_model')->nullable()->comment('Details of the trip')->nullable();
        });

        //example list of dental appointment titles
/* 
        $dental_appointment_titles = [
            "Teeth Cleaning",
            "Cavity Filling",
            "Root Canal",
            "Tooth Extraction",
            "Braces Adjustment",
            "Teeth Whitening",
            "Dental Implant Consultation",
            "Gum Disease Treatment",
            "Oral Surgery Consultation",
            "Orthodontic Consultation"
        ];
        $admins = Administrator::all();
        foreach (Event::all() as $key => $event) {

            $event->created_at = $faker->dateTimeBetween('-1 months', 'now');
            $event->updated_at = $faker->dateTimeBetween('-1 months', 'now');
            $event->administrator_id = $admins[rand(0, (count($admins)-1))]->id;
            $event->reminder_state = $faker->randomElement(['On', 'Off']);
            $event->priority = $faker->randomElement(['Low', 'Medium', 'High']);
            $event->event_date = $faker->dateTimeBetween('-1 months', '+1 months');
            $event->reminder_date = $faker->dateTimeBetween('-1 months', '+1 months');
            $event->description = $faker->text(200);
            $event->company_id = $admin->company_id;
            $event->remind_beofre_days = $faker->randomElement([1, 2, 3, 4, 5, 6, 7]);
            $event->name = $faker->randomElement($dental_appointment_titles);
            $event->event_conducted = $faker->randomElement([
                'Pending' => 'Pending',
                'Conducted' => 'Conducted',
                'Cancelled' => 'Cancelled',
            ]); 
            $event->save();
        }
 */
        $u = Admin::user();

        $content
            ->title('<b>' . Utils::greet() . " " . $u->last_name . '!</b>');



        $content->row(function (Row $row) {
            $row->column(6, function (Column $column) {
                $u = Admin::user();
                $column->append(view('widgets.dashboard-segment-1', [
                    'events' => Event::where([
                        'company_id' => $u->company_id,
                    ])->where('event_date', '>=', Carbon::now()->format('Y-m-d'))->orderBy('id', 'desc')->limit(8)->get()
                ]));
            });
            $row->column(6, function (Column $column) {
                $column->append(Dashboard::dashboard_calender());
            });
        });

        return $content;
    }

    public function calendar(Content $content)
    {
        $u = Auth::user();
        $content
            ->title('Company Calendar');
        $content->row(function (Row $row) {
            $row->column(8, function (Column $column) {
                $column->append(Dashboard::dashboard_calender());
            });
            $row->column(4, function (Column $column) {
                $u = Admin::user();
                $column->append(view('dashboard.upcoming-events', [
                    'items' => Event::where([
                        'company_id' => $u->company_id,
                    ])
                        ->where('event_date', '>=', Carbon::now()->format('Y-m-d'))
                        ->orderBy('id', 'desc')->limit(8)->get()
                ]));
            });
        });
        return $content;


        return $content;
    }
}
