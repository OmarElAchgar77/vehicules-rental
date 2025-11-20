<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Filtrage par disponibilité
        if ($request->has('available') && $request->available) {
            $query->where('is_available', true);
        }

        // Filtrage par marque
        if ($request->has('brand')) {
            $query->where('brand', 'like', '%' . $request->brand . '%');
        }

        // Filtrage par type de carburant
        if ($request->has('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        // Filtrage par transmission
        if ($request->has('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        $vehicles = $query->get();

        return response()->json($vehicles);
    }

    public function show($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json([
                'message' => 'Vehicle not found'
            ], 404);
        }

        return response()->json($vehicle);
    }

    public function checkAvailability(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        $isAvailable = $vehicle->isAvailableForDates($request->start_date, $request->end_date);

        return response()->json([
            'available' => $isAvailable,
            'vehicle' => $vehicle
        ]);
    }
}