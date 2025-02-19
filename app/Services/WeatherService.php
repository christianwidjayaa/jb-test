<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class WeatherService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openweathermap.key') ?? '';
        $this->baseUrl = config('services.openweathermap.base_url') ?? 'https://api.openweathermap.org/data/2.5/weather';

        if (empty($this->apiKey)) {
            Log::error("Weather API Key is missing. Please check the .env file.");
            throw new Exception("Weather API Key is missing.");
        }
    }

    /**
     * Retrieve weather data with caching.
     *
     * @param string $city The city name.
     * @return array Weather data or error message.
     */
    public function getWeather(string $city = 'Perth'): array
    {
        $cacheKey = "weather_{$city}";

        return Cache::remember($cacheKey, 900, function () use ($city) {
            try {
                $response = Http::get($this->baseUrl, [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]);

                if ($response->failed()) {
                    Log::error("Weather API failed for city: {$city}, Response: " . $response->body());
                    return [
                        'status'  => 400,
                        'message' => 'Failed to fetch weather data',
                        'data'    => [],
                    ];
                }

                $weatherData = $response->json();

                return [
                    'status'  => 200,
                    'message' => "Weather data for {$city} retrieved successfully",
                    'data'    => [
                        'city'        => $weatherData['name'] ?? $city,
                        'temperature' => $weatherData['main']['temp'] ?? null,
                        'humidity'    => $weatherData['main']['humidity'] ?? null,
                        'weather'     => $weatherData['weather'][0]['description'] ?? null,
                        'wind_speed'  => $weatherData['wind']['speed'] ?? null,
                    ],
                ];
            } catch (Exception $e) {
                Log::error("Exception when fetching weather for city {$city}: " . $e->getMessage());
                return [
                    'status'  => 400,
                    'message' => 'Failed to fetch weather',
                    'data'    => [],
                ];
            }
        });
    }
}
