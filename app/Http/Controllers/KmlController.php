<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

class KmlController extends Controller
{
    public function serveKml()
    {
        $filePath = public_path('kml/butuan/bxu_brgy_boundary/bxu_brgy_boundary.kml');

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'KML file not found'], 404);
        }

        return Response::make(file_get_contents($filePath), 200, [
            'Content-Type' => 'application/vnd.google-earth.kml+xml',
        ]);
    }
    public function serveKml2()
    {
        $filePath = public_path('kml/butuan/bxu_zones.kml');

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'KML file not found'], 404);
        }

        return Response::make(file_get_contents($filePath), 200, [
            'Content-Type' => 'application/vnd.google-earth.kml+xml',
        ]);
    }
}

