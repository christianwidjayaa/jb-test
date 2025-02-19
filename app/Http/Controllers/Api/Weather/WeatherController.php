<?php
namespace App\Http\Controllers\Api\Weather;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WeatherController extends Controller
{
    protected WeatherService $weatherService;

    /**
     * Inject WeatherService.
     */
    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * GET /api/weather
     * Fetch the current weather for a specified city (defaults to Perth).
     *
     * @param Request $request The HTTP request instance.
     * @return JsonResponse
     */
    public function getWeather(Request $request): JsonResponse
    {
        $city = $request->query('city', 'Perth');
        $weather = $this->weatherService->getWeather($city);

        return response()->json($weather, $weather['status']);
    }
}
