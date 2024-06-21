<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Log;

class ReservationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        Log::info('ReservationCreated event instantiated', ['reservation_id' => $reservation->id]);
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting on channel', ['channel' => 'reservations']);
        return new PrivateChannel('reservations');
    }

    public function broadcastWith()
    {
        Log::info('Broadcast data', ['data' => ['reservation' => $this->reservation]]);
        return ['reservation' => $this->reservation];
    }
}

