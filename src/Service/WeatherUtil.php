<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Location;
use App\Entity\Forecast;
use App\Repository\LocationRepository;
use App\Repository\ForecastRepository;

class WeatherUtil
{
    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly ForecastRepository $forecastRepository,
    ) {
    }

    public function getWeatherForLocation(Location $location): array
    {
        $forecasts = $this->forecastRepository->findByLocation($location);
        return $forecasts;
    }

    public function getWeatherForCountryAndCity(string $countryCode, string $city): array
    {
        $location = $this->locationRepository->findOneBy([
            'country' => $countryCode,
            'name' => $city,
        ]);

        $forecasts = $this->getWeatherForLocation($location);
        return $forecasts;
    }
}
