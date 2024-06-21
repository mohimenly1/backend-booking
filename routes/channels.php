<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::routes(['middleware' => 'auth:sanctum']);

// routes/channels.php

Broadcast::channel('reservations', function ($user) {
    return true; // Adjust as per your authorization logic
});

Broadcast::channel('item-channel', function ($user) {
    // This callback can be used to authorize users to listen to private channels.
    // Here you can define your authorization logic based on $user or any other context.

    return true;// Example: only authenticated users can listen to this channel
});
