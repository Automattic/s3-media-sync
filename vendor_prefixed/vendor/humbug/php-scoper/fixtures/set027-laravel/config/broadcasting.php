<?php

namespace WPCOM_VIP;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "redis", "log", "null"
    |
    */
    'default' => \WPCOM_VIP\env('BROADCAST_DRIVER', 'null'),
    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */
    'connections' => ['pusher' => ['driver' => 'pusher', 'key' => \WPCOM_VIP\env('PUSHER_APP_KEY'), 'secret' => \WPCOM_VIP\env('PUSHER_APP_SECRET'), 'app_id' => \WPCOM_VIP\env('PUSHER_APP_ID'), 'options' => ['cluster' => \WPCOM_VIP\env('PUSHER_APP_CLUSTER'), 'encrypted' => \true]], 'redis' => ['driver' => 'redis', 'connection' => 'default'], 'log' => ['driver' => 'log'], 'null' => ['driver' => 'null']],
];
