<?php

namespace App\Http\Controllers;

use App\Models\Chat\ChatHead;
use App\Models\Chat\ChatMessage;
use App\Models\Negotiation;
use App\Models\NegotiationRecord;
use App\Models\Trip;
use App\Models\User;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiChatController extends Controller
{

    use ApiResponser;

    public function negotiation_create(Request $r)
    {

        $customer = auth('api')->user();
        if ($customer == null) {
            return $this->error('User not found.');
        }

        if ($customer == null) {
            return $this->error('User not found.');
        }

        if ($customer == null) {
            return $this->error('User not found.');
        }

        $driver = Administrator::find($r->driver_id);
        if ($driver == null) {
            return $this->error('Driver not found.');
        }

        $old = Negotiation::where([
            'customer_id' => $customer->id,
            'driver_id' => $driver->id,
            'status' => 'Active'
        ])->first();

        $old = Negotiation::where([
            'driver_id' => $driver->id,
            'status' => 'Active'
        ])->first();

        if ($old == null) {
            $negotiation = new Negotiation();
        } else {
            $negotiation = $old;
        }
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
        $negotiation->records = null;
        $negotiation->details = null;
        $negotiation->save();
        if ($negotiation->id < 1) {
            return $this->error('Negotiation not created.');
        }

        $price = ((int)($r->price));

        $record = new NegotiationRecord();
        $record->price = $price;
        $record->negotiation_id = $negotiation->id;
        $record->customer_id = $customer->id;
        $record->driver_id = $driver->id;
        $record->last_negotiator_id = $customer->id;
        $record->first_negotiator_id = $customer->id;
        $record->price_accepted = 'No';
        $record->message_type = 'Negotiation';
        $record->message_body = null;
        $record->image_url = null;
        $record->audio_url = null;
        $record->is_received = 'No';
        $record->is_seen = 'No';
        $record->latitude = null;
        $record->longitude = null;
        $record->save();
        return $this->success($negotiation, 'Success');
    }


    public function negotiations_records_create(Request $r)
    {

        $sender = auth('api')->user();
        if ($sender == null) {
            return $this->error('User not found.');
        }

        if (!isset($r->negotiation_id)) {
            return $this->error('Neg id not found.');
        }

        if (!isset($r->message_type)) {
            return $this->error('Neg type not found.');
        }

        $neg = Negotiation::find($r->negotiation_id);
        if ($neg == null) {
            return $this->error('Neg not found.');
        }

        if ($neg->message_type == 'Negotiation') {
            $lasts = NegotiationRecord::where([
                'negotiation_id' => $neg->id
            ])->orderBy('id', 'desc')
                ->get();
            if ($lasts->count() > 0) {
                if ($lasts[0]->last_negotiator_id == $sender->id) {
                    //return $this->error('Wait for the other party to reply.');
                }
            }
        }




        $price = ((int)($r->price));

        $record = new NegotiationRecord();
        $record->price = $price;
        $record->negotiation_id = $neg->id;
        $record->customer_id = $neg->customer_id;
        $record->driver_id = $neg->driver_id;
        $record->last_negotiator_id = $sender->id;
        $record->first_negotiator_id = $neg->customer_id;
        $record->price_accepted = $r->price_accepted;
        $record->message_type = $r->message_type;
        $record->message_body = $r->message_body;
        $record->image_url = null;
        $record->audio_url = null;
        $record->is_received = 'No';
        $record->is_seen = 'No';
        $record->latitude = $r->latitude;
        $record->longitude = $r->longitude;
        $record->save();

        return $this->success($record, 'Success');
    }

    public function negotiations_accept(Request $r)
    {

        $sender = auth('api')->user();
        if ($sender == null) {
            return $this->error('User not found.');
        }

        if (!isset($r->negotiation_id)) {
            return $this->error('Neg id not found.');
        }

        if (!isset($r->message_type)) {
            return $this->error('Neg type not found.');
        }

        $neg = Negotiation::find($r->negotiation_id);
        if ($neg == null) {
            return $this->error('Neg not found.');
        }

        if (
            $r->customer_accepted == 'Yes' && $r->customer_driver == 'Yes'
        ) {
            $neg->status = 'Accepted';
            $neg->customer_accepted = 'Yes';
            $neg->customer_driver = 'Yes';
            $neg->save();
            return $this->success($neg, 'Success');
        } else  if (
            $r->customer_accepted == 'No' && $r->customer_driver == 'No'
        ) {
            $neg->status = 'Canceled';
            $neg->customer_accepted = 'No';
            $neg->customer_driver = 'No';
            $neg->save();
            return $this->success($neg, 'Success');
        } else  if (
            $r->status == 'Completed'
        ) {
            $neg->status = 'Completed';
            $neg->save();
            return $this->success($neg, 'Success');
        } else {
            return $this->error('Invalid status.');
        }
    }


    public function negotiations()
    {
        $user = auth('api')->user();
        if ($user == null) {
            return $this->error('User not found.');
        }
        $negotiations = Negotiation::where([
            'customer_id' => $user->id,
        ])->orWhere([
            'driver_id' => $user->id
        ])->get();
        return $this->success($negotiations, 'Success');
    }

    public function negotiations_records(Request $r)
    {
        $user = auth('api')->user();
        if ($user == null) {
            return $this->error('User not found.');
        }
        $recs = [];

        if (isset($r->negotiation_id) && $r->negotiation_id != null) {
            $recs = NegotiationRecord::where([
                'negotiation_id' => $r->negotiation_id,
            ])->get();
            return $this->success($recs, 'Success');
        }

        $recs = NegotiationRecord::where([
            'customer_id' => $user->id,
        ])->orWhere([
            'driver_id' => $user->id
        ])->get();


        return $this->success($recs, 'Success');
    }


    public function chat_heads_create(Request $r)
    {

        $sender = auth('api')->user();
        if ($sender == null) {
            return $this->error('User not found.');
        }

        if ($sender == null) {
            return $this->error('User not found.');
        }

        if ($sender == null) {
            return $this->error('User not found.');
        }
        $receiver = User::find($r->receiver_id);
        if ($receiver == null) {
            return $this->error('Receiver not found.');
        }

        if ($r->product_id != null && trim($r->product_id) != "") {
            $chat_head = ChatHead::where([
                'product_owner_id' => $receiver->id,
                'customer_id' => $sender->id,
                'product_id' => $r->product_id,
            ])->first();

            if ($chat_head != null) {
                return $this->success($chat_head, 'Success');
            }

            $chat_head = ChatHead::where([
                'product_owner_id' => $sender->id,
                'customer_id' => $receiver->id,
                'product_id' => $r->product_id,
            ])->first();

            if ($chat_head != null) {
                return $this->success($chat_head, 'Success');
            }
        } else {
            $chat_head = ChatHead::where([
                'product_owner_id' => $receiver->id,
                'customer_id' => $sender->id,
            ])->first();

            if ($chat_head != null) {
                return $this->success($chat_head, 'Success');
            }

            $chat_head = ChatHead::where([
                'product_owner_id' => $sender->id,
                'customer_id' => $receiver->id,
            ])->first();

            if ($chat_head != null) {
                return $this->success($chat_head, 'Success');
            }
        }


        $trip = Trip::find($r->product_id);
        $chat_head = new ChatHead();
        if ($trip != null) {
            $driver = Administrator::find($trip->driver_id);
            $chat_head->product_id = $trip->id;
            $chat_head->product_owner_id = $receiver->id;
            $chat_head->customer_id = $sender->id;
            $chat_head->product_name = $trip->start_stage_text . " to " . $trip->end_stage_text . " on " . date('d M Y', strtotime($trip->scheduled_start_time));
            $chat_head->product_photo = $receiver->avatar;
            $chat_head->product_owner_name = $receiver->name;
            $chat_head->product_owner_photo = $receiver->avatar;
            $chat_head->customer_name = $sender->name;
            $chat_head->customer_photo = $sender->avatar;
        } else {
            $chat_head->product_id = null;
            $chat_head->product_owner_id = $receiver->id;
            $chat_head->customer_id = $sender->id;
            $chat_head->product_name = $receiver->name;
            $chat_head->product_photo = $receiver->avatar;
            $chat_head->product_owner_name = $receiver->name;
            $chat_head->product_owner_photo = $receiver->avatar;
            $chat_head->customer_name = $sender->name;
            $chat_head->customer_photo = $sender->avatar;
        }
        $chat_head->last_message_body = 'No messages yet.';
        $chat_head->last_message_time = Carbon::now();
        $chat_head->last_message_status = 'sent';
        $chat_head->save();
        $chat_head = ChatHead::find($chat_head->id);
        return $this->success($chat_head, 'Success');
    }


    public function chat_send(Request $r)
    {

        $sender = auth('api')->user();
        if ($sender == null) {
            return $this->error('User not found.');
        }

        if ($sender == null) {
            return $this->error('User not found.');
        }

        if ($sender == null) {
            return $this->error('User not found.');
        }
        $receiver = User::find($r->receiver_id);
        if ($receiver == null) {
            return $this->error('Receiver not found.');
        }

        $chat_head = ChatHead::find($r->chat_head_id);
        if ($chat_head == null) {
            return $this->error('Chat head not found.');
        }

        $chat_message = new ChatMessage();
        $chat_message->chat_head_id = $chat_head->id;
        $chat_message->sender_id = $sender->id;
        $chat_message->receiver_id = $receiver->id;
        $chat_message->sender_name = $sender->name;
        $chat_message->sender_photo = $sender->photo;
        $chat_message->receiver_name = $receiver->name;
        $chat_message->receiver_photo = $receiver->photo;
        $chat_message->body = $r->body;
        $chat_message->type = 'text';
        $chat_message->status = 'sent';
        $chat_message->save();
        $chat_head->last_message_body = $r->body;
        $chat_head->last_message_time = Carbon::now();
        $chat_head->last_message_status = 'sent';
        $chat_head->save();
        return $this->success($chat_message, 'Success');
    }




    public function chat_messages(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (isset($r->chat_head_id) && $r->chat_head_id != null) {
            $messages = ChatMessage::where([
                'chat_head_id' => $r->chat_head_id
            ])->get();
            return $this->success($messages, 'Success');
        }
        $messages = ChatMessage::where([
            'sender_id' => $u->id
        ])->orWhere([
            'receiver_id' => $u->id
        ])->get();
        return $this->success($messages, 'Success');
    }




    public function chat_heads(Request $r)
    {

        $u = auth('api')->user();

        if ($u == null) {
            return $this->error('User not found.');
        }

        $chat_heads = ChatHead::where([
            'product_owner_id' => $u->id
        ])->orWhere([
            'customer_id' => $u->id
        ])->get();
        $chat_heads->append('customer_unread_messages_count');
        $chat_heads->append('product_owner_unread_messages_count');
        return $this->success($chat_heads, 'Success');
    }
}
