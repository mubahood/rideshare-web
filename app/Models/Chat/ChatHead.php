<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHead extends Model
{
    use HasFactory;

    public function getCustomerUnreadMessagesCountAttribute()
    {
        return ChatMessage::where('chat_head_id', $this->id)
            ->where('receiver_id', $this->customer_id)
            ->where('status', 'sent')
            ->count();
    }
    public function getProductOwnerUnreadMessagesCountAttribute()
    {
        return ChatMessage::where('chat_head_id', $this->id)
            ->where('receiver_id', $this->product_owner_id)
            ->where('status', 'sent')
            ->count();
    }

    protected $appends = ['customer_unread_messages_count', 'product_owner_unread_messages_count'];
}
