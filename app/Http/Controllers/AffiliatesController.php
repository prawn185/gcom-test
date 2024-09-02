<?php

namespace App\Http\Controllers;

use App\Models\Affiliates;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliatesController extends Controller
{
    /**
     * Display a listing of the affiliates.
     *
     * @return Factory|View|Application
     */
    public function index()
    {
        $affiliates = new Affiliates();
        $affiliates = $affiliates->getAffiliates();
        return view('welcome', ['affiliates' => $affiliates]);
    }

    /**
     * Save all affiliates in the database
     *
     * @return JsonResponse
     *
     */
    public function save()
    {
        $affiliates = new Affiliates();
        $affiliates = $affiliates->getAffiliates();
        if (Affiliates::count() > 0) {
            return response()->json(['message' => 'Affiliates already saved']);
        }
        foreach ($affiliates as $affiliate) {
            Affiliates::create([
                'name' => $affiliate['name'],
                'latitude' => $affiliate['latitude'],
                'longitude' => $affiliate['longitude'],
                'external_id' => $affiliate['affiliate_id']
            ]);
        }

        return response()->json(['message' => count($affiliates) . ' affiliates saved']);
    }


    /**
     * Find the closest affiliate to the given coordinates also within the given distance
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function find(Request $request)
    {
        $latitude = $request->input('lat');
        $longitude = $request->input('lon');
        $distance = $request->input('dis');


        return response()->json($this->findAffiliatesWithinDistance($latitude, $longitude, $distance));
    }

    public function findAffiliatesWithinDistance($latitude, $longitude, $distance): \Illuminate\Support\Collection
    {
        // Get all affiliates from the database
        $affiliates = Affiliates::all();

        $affiliatesWithinDistance = collect();

        foreach ($affiliates as $affiliate) {
            $calculatedDistance = $this->calculateDistance($latitude, $longitude, $affiliate->latitude, $affiliate->longitude);
            $affiliate->distance = round($calculatedDistance, 2);
            if ($calculatedDistance <= $distance) {
                $affiliatesWithinDistance->push($affiliate);
            }
        }

        return $affiliatesWithinDistance;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Convert all latitudes and longitudes to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Haversine formula
        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)));
        return $angle * 6371;


    }
}

