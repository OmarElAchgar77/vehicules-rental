<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        $rentals = Rental::where('user_id', $request->user()->id)
            ->with('vehicle')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($rentals);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'license_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehicle = Vehicle::find($request->vehicle_id);

        if (!$vehicle->is_available) {
            return response()->json([
                'message' => 'This vehicle is not available for rental'
            ], 400);
        }

        if (!$vehicle->isAvailableForDates($request->start_date, $request->end_date)) {
            return response()->json([
                'message' => 'Vehicle is not available for the selected dates'
            ], 400);
        }

        // Calcul du prix total
        $start = \Carbon\Carbon::parse($request->start_date);
        $end = \Carbon\Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1;
        $totalPrice = $days * $vehicle->daily_price;

        // Upload de l'image du permis
        $licensePath = $request->file('license_image')->store('licenses', 'public');

        $rental = Rental::create([
            'user_id' => $request->user()->id,
            'vehicle_id' => $request->vehicle_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_price' => $totalPrice,
            'license_image' => $licensePath,
            'status' => 'pending',
        ]);

        $rental->load('vehicle');

        return response()->json($rental, 201);
    }

    public function show(Request $request, $id)
    {
        $rental = Rental::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('vehicle')
            ->first();

        if (!$rental) {
            return response()->json([
                'message' => 'Rental not found'
            ], 404);
        }

        return response()->json($rental);
    }
}