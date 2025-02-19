<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching real weather data from OpenWeather API.
     *
     * This test makes a real API call and should be used sparingly.
     * Run it manually using: php artisan test --filter=test_real_weather_api_call
     */
    public function test_real_weather_api_call(): void
    {
        if (!env('RUN_REAL_API_TESTS', false)) {
            $this->markTestSkipped('Skipping real API call test.');
        }
        $city = 'Perth';

        $response = $this->getJson("/api/weather?city={$city}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'city',
                         'temperature',
                         'humidity',
                         'weather',
                         'wind_speed',
                     ],
                 ]);
    }

    #[DataProvider('validCityProvider')]
    public function test_weather_endpoint_returns_mocked_data(string $city): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'weather' => [['description' => 'clear sky']],
                'main' => ['temp' => 22.5, 'humidity' => 65],
                'wind' => ['speed' => 3.2],
                'name' => $city,
            ], 200),
        ]);

        $response = $this->getJson("/api/weather?city={$city}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => 200,
                     'message' => "Weather data for {$city} retrieved successfully",
                     'data'    => [
                         'city'        => $city,
                         'temperature' => 22.5,
                         'humidity'    => 65,
                         'weather'     => 'clear sky',
                         'wind_speed'  => 3.2,
                     ],
                 ]);
    }

    #[DataProvider('invalidCityProvider')]
    public function test_weather_endpoint_handles_failure(string $city): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'cod' => 400,
                'message' => 'Nothing to geocode'
            ], 400),
        ]);

        $response = $this->getJson("/api/weather?city={$city}");

        $response->assertStatus(400)
                 ->assertJson([
                     'status'  => 400,
                     'message' => 'Failed to fetch weather data',
                     'data'    => [],
                 ]);
    }

    /**
     * Data provider for valid cities.
     */
    public static function validCityProvider(): array
    {
        return [
            ['Perth'],
            ['Sydney'],
            ['Melbourne'],
        ];
    }

    /**
     * Data provider for invalid cases.
     */
    public static function invalidCityProvider(): array
    {
        return [
            [''], // Empty city name
            ['InvalidCity123'], // Non-existent city
        ];
    }
}
