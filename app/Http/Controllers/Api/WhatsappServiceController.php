<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Support\Str;

class WhatsappServiceController extends Controller
{
    public function __invoke()
    {
        $services = Service::orderBy('name')
            ->select(['id', 'name', 'price'])
            ->hasEmployees()
            ->get()
            ->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'name' => Str::title($service->name),
                    'price' => $service->price,
                    'agent_description' => $service->name, // Fallback to name since agent_description field doesn't exist
                ];
            });

        return response()->json($services);
    }
}

