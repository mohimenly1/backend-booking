<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Playground;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Events\ReservationCreated;
class ReservationController extends Controller
{

    public function getStatusReservations()
    {
        // Get the authenticated owner
     // Get the authenticated owner
     $owner = Auth::user();

     $ownedPlaygroundIds = $owner->playgrounds()->pluck('id');
 
     // Debug: Log owned playground IDs
     Log::info('Owned Playground IDs: ' . $ownedPlaygroundIds->toJson());
 
     // Total reservations for the owner's playgrounds
     $totalReservations = Reservation::whereIn('playground_id', $ownedPlaygroundIds)->count();
 
     // Pending reservations
     $pendingReservations = Reservation::whereIn('playground_id', $ownedPlaygroundIds)
         ->where('status', 'pending')->count();
 
     // Confirmed reservations
     $confirmedReservations = Reservation::whereIn('playground_id', $ownedPlaygroundIds)
         ->where('status', 'confirmed')->count();
 
     // Canceled reservations
     $canceledReservations = Reservation::whereIn('playground_id', $ownedPlaygroundIds)
         ->where('status', 'canceled')->count();
 
     // Debug: Log counts
     Log::info('Total Reservations: ' . $totalReservations);
     Log::info('Pending Reservations: ' . $pendingReservations);
     Log::info('Confirmed Reservations: ' . $confirmedReservations);
     Log::info('Canceled Reservations: ' . $canceledReservations);
 
     return response()->json([
         'total_reservations' => $totalReservations,
         'pending_reservations' => $pendingReservations,
         'confirmed_reservations' => $confirmedReservations,
         'canceled_reservations' => $canceledReservations,
     ]);
    }
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
            ->where(function ($query) use ($date) {
                $query->whereDate('start_time', $date)
                      ->orWhereDate('end_time', $date);
            })
            ->get(['start_time', 'end_time']);
    
        // Fetch playground working hours
        $open_time = $playground->open_time;
        $close_time = $playground->close_time;
    
        // Map busy times
        $busy_times = $reservations->map(function ($reservation) {
            return [
                'start_time' => $reservation->start_time->format('h:i A'),
                'end_time' => $reservation->end_time->format('h:i A'),
            ];
        });
    
        // Calculate available times
        $available_times = $this->calculateAvailableTimes($open_time, $close_time, $busy_times, $date);
    
        return response()->json([
            'busy_times' => $busy_times,
            'available_times' => $available_times,
            'open_time' => (new \DateTime($open_time))->format('h:i A'),
            'close_time' => (new \DateTime($close_time))->format('h:i A')
        ]);
    }
    
    private function calculateAvailableTimes($open_time, $close_time, $busy_times, $date)
    {
        $available_times = [];
        $start_time = new \DateTime($open_time);
        $end_time = new \DateTime($close_time);
    
        // If close_time is less than open_time, it means it closes the next day
        if ($end_time <= $start_time) {
            $end_time->modify('+1 day');
        }
    
        while ($start_time < $end_time) {
            $slot_start = clone $start_time;
            $slot_end = (clone $start_time)->modify('+1 hour');
    
            $is_busy = false;
            foreach ($busy_times as $busy) {
                $busy_start = \DateTime::createFromFormat('h:i A', $busy['start_time']);
                $busy_end = \DateTime::createFromFormat('h:i A', $busy['end_time']);
    
                // Adjust busy times if they span midnight
                if ($busy_end < $busy_start) {
                    $busy_end->modify('+1 day');
                }
    
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
            'status' => 'required|string',
        ]);
    
        $user_id = $request->user_id;
        $playground_id = $request->playground_id;
        $start_time = new \DateTime($request->start_time);
        $end_time = new \DateTime($request->end_time);
    
        Log::info('Checking for conflicting reservations', [
            'user_id' => $user_id,
            'playground_id' => $playground_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
        ]);
    
        // Check if the user has a confirmed reservation that conflicts with the requested time
        $hasConfirmedReservation = Reservation::where('user_id', $user_id)
        ->where('status', 'confirmed')
        ->where(function ($query) use ($start_time, $end_time) {
            $query->whereBetween('start_time', [$start_time, $end_time])
                  ->orWhereBetween('end_time', [$start_time, $end_time])
                  ->orWhere(function ($query) use ($start_time, $end_time) {
                      $query->where('start_time', '<=', $start_time)
                            ->where('end_time', '>=', $end_time);
                  });
        })
        ->exists();
    
    
        Log::info('Has confirmed reservation:', [
            'hasConfirmedReservation' => $hasConfirmedReservation,
        ]);
    
        if ($hasConfirmedReservation) {
            return response()->json(['error' => 'You already have a confirmed reservation during this time.'], 400);
        }
    
        // Check if the user can book again one hour after their last reservation
        $lastReservation = Reservation::where('user_id', $user_id)
            ->where('status', 'confirmed')
            ->orderBy('end_time', 'desc')
            ->first();
    
        if ($lastReservation) {
            $lastReservationEndTime = new \DateTime($lastReservation->end_time);
            $oneHourAfterLastReservation = (clone $lastReservationEndTime)->modify('+1 hour');
    
            Log::info('Last reservation details:', [
                'lastReservationEndTime' => $lastReservationEndTime,
                'oneHourAfterLastReservation' => $oneHourAfterLastReservation,
            ]);
    
            if ($start_time < $oneHourAfterLastReservation) {
                return response()->json(['error' => 'You can only book again one hour after your last reservation ends.'], 400);
            }
        }
    
        $playground = Playground::findOrFail($playground_id);
    
        $interval = $start_time->diff($end_time);
        $hours = $interval->h + ($interval->i / 60);
    
        $total_price = $playground->price_per_hour * $hours;
    
        $reservation = Reservation::create([
            'user_id' => $user_id,
            'playground_id' => $playground_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_price' => $total_price,
            'status' => $request->status,
        ]);

        Log::info('Reservation created, dispatching event', ['reservation_id' => $reservation->id]);

        broadcast(new ReservationCreated($reservation))->toOthers();
    
        Log::info('Event dispatched', ['reservation_id' => $reservation->id]);
    
    
        return response()->json($reservation, 201);
    }
    
    

    public function show(Reservation $reservation)
    {
        return $reservation;
    }

    public function userReservations()
{
    // Fetch reservations booked by the authenticated user
    $user_id = auth()->id();
    $reservations = Reservation::with('playground')
        ->where('user_id', $user_id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($reservations);
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
