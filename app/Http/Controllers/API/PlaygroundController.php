<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Playground;
use App\Rules\TimeAfter;
use Illuminate\Http\Request;

class PlaygroundController extends Controller
{
    public function index()
    {
        return Playground::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price_per_half_hour' => 'required|numeric',
            'price_per_hour' => 'required|numeric',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i',
        ]);
    
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('playgrounds', 'public');
                $images[] = $path;
            }
        }
    
        $playground = new Playground($request->except('images'));
        $playground->images = json_encode($images);
        $playground->owner_id = $request->user()->id;
        $playground->save();
    
        return response()->json($playground, 201);
    }
    
    
    

    public function show(Playground $playground)
    {
        return $playground;
    }

    public function update(Request $request, Playground $playground)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price_per_half_hour' => 'sometimes|numeric',
            'price_per_hour' => 'sometimes|numeric',
            'images' => 'sometimes|json',
            'open_time' => 'sometimes|date_format:H:i:s',
            'close_time' => 'sometimes|date_format:H:i:s',
        ]);
    
        // Check if images field is present in the request and decode it if necessary
        if ($request->has('images')) {
            $images = json_decode($request->input('images'), true);
            $playground->images = $images;
        }
    
        // Update the playground with the validated data
        $playground->update($request->except('images'));
    
        return response()->json($playground, 200);
    }
    

    public function destroy(Playground $playground)
    {
        $playground->delete();

        return response()->json(['message' => 'Playground deleted successfully']);
    }


    public function myPlaygrounds(Request $request)
    {
        $user = $request->user();
        $playgrounds = Playground::where('owner_id', $user->id)->get();

        $response = [
            'user' => [
                'subscription_type' => $user->subscription_type,
                'subscription_start_date' => $user->subscription_start_date,
                'subscription_end_date' => $user->subscription_end_date,
            ],
            'playgrounds' => $playgrounds,
        ];

        return response()->json($response);
    }
}
