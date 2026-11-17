<?php

namespace App\Controller;

use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class WeatherApiController extends AbstractController
{
    #[Route('/api/v1/weather', name: 'app_weather_api', methods: ['GET'])]
    public function index(
        #[MapQueryParameter] string $country,
        #[MapQueryParameter] string $city,
        #[MapQueryParameter] string $format = 'json',
        #[MapQueryParameter('twig')] bool $twig = false,
        WeatherUtil $weatherUtil,
    ): Response|JsonResponse
    {
        $forecasts = $weatherUtil->getWeatherForCountryAndCity($country, $city);

        if ($twig) {
            if ($format === 'csv') {
                $response = $this->render('weather_api/index.csv.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $forecasts,
                ]);
                $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
                return $response;
            }

            $response = $this->render('weather_api/index.json.twig', [
                'city' => $city,
                'country' => $country,
                'measurements' => $forecasts,
            ]);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if ($format === 'csv') {
            $rows = ['city,country,date,celsius,fahrenheit,wind,humidity,precipitation'];
            foreach ($forecasts as $m) {
                $rows[] = sprintf(
                    '%s,%s,%s,%s,%s,%s,%s,%s',
                    $city,
                    $country,
                    $m->getDate()->format('Y-m-d'),
                    $m->getTemperature(),
                    method_exists($m, 'getFahrenheit') ? $m->getFahrenheit() : '',
                    $m->getWind(),
                    $m->getHumidity(),
                    $m->getPrecipitation()
                );
            }
            return new Response(implode("\n", $rows), 200, [
                'Content-Type' => 'text/plain; charset=UTF-8'
            ]);
        }

        return $this->json([
            'city' => $city,
            'country' => $country,
            'measurements' => array_map(fn($m) => [
                'date' => $m->getDate()->format('Y-m-d'),
                'celsius' => $m->getTemperature(),
                'fahrenheit' => method_exists($m, 'getFahrenheit') ? $m->getFahrenheit() : null,
                'wind' => $m->getWind(),
                'humidity' => $m->getHumidity(),
                'precipitation' => $m->getPrecipitation(),
            ], $forecasts),
        ]);
    }
}
