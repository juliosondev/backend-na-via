<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use ExponentPhpSDK\Expo;


class Notifications extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'message',
        'data',
        'type',
        'status',
        'sent_at',
        'user_id',
        'expo_push_token',
        'created_at',
        'updated_at'
    ];
    
    protected $casts = [
        'data' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a notification record and send push notification
     *
     * @param array $attributes ['title', 'message', 'data', 'type', 'user_id', 'expo_push_token']
     * @return Notification
     */
    public static function createAndSend(array $attributes){
        $notification = self::create([
            'title' => $attributes['title'],
            'message' => $attributes['message'],
            'data' => null,
            'type' => $attributes['type'] ?? null,
            'status' => 'pending',
            'user_id' => $attributes['user_id'] ?? null,
            'expo_push_token' => $attributes['expo_push_token'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $expo = Expo::normalSetup();


        $recipient = $attributes['expo_push_token'];
        $expo->subscribe($recipient, $recipient);

        $notificationData = [
            'title' => $attributes['title'],
            'body' => $attributes['message'],
            'sound' => 'default', // Optional
            'data' => $attributes['data'] ?? ['extraData' => 'Default data']
        ];

        $expo->notify([$recipient], $notificationData);
        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $notification;

    }

public static function createAndSendForAllUnlogged(array $attributes)
{
    $users = User::whereNotNull('id')
                 ->where('level', '!=', 4)
                 ->get();

    $recipientTokens = $users->pluck('expo_push_token')->filter()->toArray();

    if (empty($recipientTokens)) {
        return [];
    }

    $channelName = 'group_unlogged_users_' . uniqid();

    $expo = Expo::normalSetup();

    foreach ($recipientTokens as $token) {
        $expo->subscribe($channelName, $token);
    }

    $notification = self::create([
        'title' => $attributes['title'],
        'message' => $attributes['message'],
        'data' => null,
        'type' => $attributes['type'] ?? null,
        'status' => 'activo',
        'user_id' => null,
        'expo_push_token' => null,
        // 'channel_name' => $channelName, 
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $notificationData = [
        'title' => $attributes['title'],
        'body' => $attributes['message'],
        'sound' => 'default',
        'data' => $attributes['data'] ?? ['extraData' => 'Default data']
    ];

    $expo->notify([$channelName], $notificationData);

    $notification->update([
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    return [$notification];
}

public static function createAndSendForAllMotoboys(array $attributes)
{
    $users = User::whereNotNull('id')
                 ->where('level', '==', 4)
                 ->get();

    $recipientTokens = $users->pluck('expo_push_token')->filter()->toArray();

    $userWithId1 = User::find(1);
    $tokensFromUser1 = [];
    if ($userWithId1 && $userWithId1->expo_push_tokens) {
        $tokensFromUser1 = json_decode($userWithId1->expo_push_tokens, true) ?? [];
    }

    $allTokens = array_unique(array_merge($recipientTokens, $tokensFromUser1));


    if (empty($allTokens)) {
        return [];
    }

    $channelName = 'group_unlogged_users_' . uniqid();

    $expo = Expo::normalSetup();

    foreach ($allTokens as $token) {
        $expo->subscribe($channelName, $token);
    }

    $notification = self::create([
        'title' => $attributes['title'],
        'message' => $attributes['message'],
        'data' => null,
        'type' => $attributes['type'] ?? null,
        'status' => 'activo',
        'user_id' => 1,
        'expo_push_token' => null,
        // 'channel_name' => $channelName, 
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $notificationData = [
        'title' => $attributes['title'],
        'body' => $attributes['message'],
        'sound' => 'default',
        'data' => $attributes['data'] ?? ['extraData' => 'Default data']
    ];

    $expo->notify([$channelName], $notificationData);

    $notification->update([
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    return [$notification];
}



}
