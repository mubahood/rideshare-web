<?php

namespace App\Http\Controllers;

use App\Models\Chat\ChatHead;
use App\Models\Chat\ChatMessage;
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
        }else{
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
