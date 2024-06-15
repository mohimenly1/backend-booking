<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Playground;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        return Reservation::all();
    }

    
    // File: app/Http/Controllers/ReservationController.php

    public function getSchedule($playground_id, $date)
    {
        // Fetch playground details
        $playground = Playground::findOrFail($playground_id);
    
        // Fetch reservations for the specified playground and date
        $reservations = Reservation::where('playground_id', $playground_id)
            ->whereDate('start_time', $date)
            ->get(['start_time', 'end_time']);
    
        // Fetch playground working hours
        $open_time = $playground->open_time;
        $close_time = $playground->close_time;
    
        // Map busy times
        $busy_times = $reservations->map(function($reservation) {
            return [
                'start_time' => $reservation->start_time->format('h:i A'),
                'end_time' => $reservation->end_time->format('h:i A'),
            ];
        });
    
        // Calculate available times
        $available_times = $this->calculateAvailableTimes($open_time, $close_time, $busy_times);
    
        return response()->json([
            'busy_times' => $busy_times,
            'available_times' => $available_times,
            'open_time' => (new \DateTime($open_time))->format('h:i A'),
            'close_time' => (new \DateTime($close_time))->format('h:i A')
        ]);
    }
    
    // Helper function to calculate available times
    private function calculateAvailableTimes($open_time, $close_time, $busy_times)
    {
        $available_times = [];
        $start_time = new \DateTime($open_time);
        $end_time = new \DateTime($close_time);
    
        while ($start_time < $end_time) {
            $slot_start = clone $start_time;
            $slot_end = (clone $start_time)->modify('+1 hour');
    
            $is_busy = false;
            foreach ($busy_times as $busy) {
                $busy_start = \DateTime::createFromFormat('h:i A', $busy['start_time']);
                $busy_end = \DateTime::createFromFormat('h:i A', $busy['end_time']);
    
                if (($slot_start >= $busy_start && $slot_start < $busy_end) || ($slot_end > $busy_start && $slot_end <= $busy_end)) {
                    $is_busy = true;
                    break;
                }
            }
    
            if (!$is_busy && $slot_end <= $end_time) {
                $available_times[] = [
                    'start_time' => $slot_start->format('h:i A'),
                    'end_time' => $slot_end->format('h:i A')
                ];
            }
    
            $start_time->modify('+1 hour');
        }
    
        return $available_times;
    }
    


    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'playground_id' => 'required|exists:playgrounds,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'total_price' => 'required|numeric',
            'status' => 'required|string',
        ]);

        return Reservation::create($request->all());
    }

    public function show(Reservation $reservation)
    {
        return $reservation;
    }

    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'playground_id' => 'sometimes|exists:playgrounds,id',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'total_price' => 'sometimes|numeric',
            'status' => 'sometimes|string',
        ]);

        $reservation->update($request->all());

        return $reservation;
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return response()->json(['message' => 'Reservation deleted successfully']);
    }


        // Method to confirm a reservation
        public function confirm(Reservation $reservation)
        {
            $this->authorizeOwner($reservation);
    
            $reservation->update(['status' => 'confirmed']);
    
            return response()->json(['message' => 'Reservation confirmed successfully']);
        }
    
        // Method to cancel a reservation
        public function cancel(Reservation $reservation)
        {
            $this->authorizeOwner($reservation);
    
            $reservation->update(['status' => 'canceled']);
    
            return response()->json(['message' => 'Reservation canceled successfully']);
        }
    
        // Helper method to authorize owner
        protected function authorizeOwner(Reservation $reservation)
        {
            $user = Auth::user();
            $playground = $reservation->playground;
    
            if ($user->role !== 'owner' || $user->id !== $playground->owner_id) {
             
                abort(403, 'Unauthorized action.');
            }
        }
}
