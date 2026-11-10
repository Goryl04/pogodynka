<?php

namespace App\Command;

use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:country-and-city',
    description: 'Pobiera prognozę pogody na podstawie kodu kraju i nazwy miasta',
)]
class WeatherCountryAndCityCommand
{
    public function __construct(
        private readonly WeatherUtil $weatherUtil,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Country code')] string $countryCode,
        #[Argument('City name')] string $cityName,
    ): int {
        $forecasts = $this->weatherUtil->getWeatherForCountryAndCity($countryCode, $cityName);

        $io->writeln(sprintf('Location: %s', $cityName));
        $io->table(['Date', 'Temperature', 'Wind', 'Humidity', 'Precipitation'], array_map(fn($m) => [
            $m->getDate()->format('Y-m-d'),
            $m->getTemperature(),
            $m->getWind(),
            $m->getHumidity(),
            $m->getPrecipitation(),
        ], $forecasts));

        return Command::SUCCESS;
    }
}
