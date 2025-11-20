<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // Statistiques
    public function stats()
    {
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('is_available', true)->count();
        $totalRentals = Rental::count();
        $pendingRentals = Rental::where('status', 'pending')->count();
        $confirmedRentals = Rental::where('status', 'confirmed')->count();
        $totalUsers = User::count();

        $recentRentals = Rental::with(['user', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => [
                'total_vehicles' => $totalVehicles,
                'available_vehicles' => $availableVehicles,
                'total_rentals' => $totalRentals,
                'pending_rentals' => $pendingRentals,
                'confirmed_rentals' => $confirmedRentals,
                'total_users' => $totalUsers,
            ],
            'recent_rentals' => $recentRentals
        ]);
    }

    // Gestion des véhicules
    public function vehicleIndex()
    {
        $vehicles = Vehicle::all();
        return response()->json($vehicles);
    }

    public function vehicleStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'registration_number' => 'required|string|unique:vehicles',
            'daily_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'seats' => 'required|integer|min:1',
            'fuel_type' => 'required|string|in:essence,diesel,electrique,hybride',
            'transmission' => 'required|string|in:manuelle,automatique',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle = Vehicle::create($data);

        return response()->json($vehicle, 201);
    }

    public function vehicleUpdate(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'brand' => 'sometimes|required|string|max:255',
            'model' => 'sometimes|required|string|max:255',
            'year' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'registration_number' => 'sometimes|required|string|unique:vehicles,registration_number,' . $id,
            'daily_price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'seats' => 'sometimes|required|integer|min:1',
            'fuel_type' => 'sometimes|required|string|in:essence,diesel,electrique,hybride',
            'transmission' => 'sometimes|required|string|in:manuelle,automatique',
            'is_available' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($vehicle->image) {
                Storage::disk('public')->delete($vehicle->image);
            }
            $data['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle->update($data);

        return response()->json($vehicle);
    }

    public function vehicleDestroy($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        // Vérifier s'il y a des locations en cours
        $activeRentals = $vehicle->rentals()
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($activeRentals) {
            return response()->json([
                'message' => 'Cannot delete vehicle with active or pending rentals'
            ], 400);
        }

        // Supprimer l'image si elle existe
        if ($vehicle->image) {
            Storage::disk('public')->delete($vehicle->image);
        }

        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }

    // Gestion des réservations
    public function rentalIndex()
    {
        $rentals = Rental::with(['user', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($rentals);
    }

    public function rentalUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,rejected,completed',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rental = Rental::with('user', 'vehicle')->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $rental->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json($rental);
    }
}