<?php

namespace App\Controller;

use App\Entity\Anker\Daily as AnkerDaily;
use App\Entity\Anker\Hourly as AnkerHourly;
use App\Entity\Gas\Daily as GasDaily;
use App\Entity\Gas\Hourly as GasHourly;
use App\Entity\Photovoltaics\Daily as PvDaily;
use App\Entity\Photovoltaics\Hourly as PvHourly;
use App\Entity\Power\Daily as PowerDaily;
use App\Entity\Power\Hourly as PowerHourly;
use App\Entity\Sensor;
use App\Entity\Solar\Daily as SolarDaily;
use App\Entity\Solar\Hourly as SolarHourly;
use App\Lib\AnkerDailyDto;
use App\Lib\AnkerHourlyDto;
use App\Lib\SmlParser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/gas/add', name: 'gas_add', methods: ['POST'])]
    public function gasAddAction(LoggerInterface $logger, EntityManagerInterface $em): Response
    {
        try {
            $em->getRepository(GasDaily::class)->update();
            $em->getRepository(GasHourly::class)->update();
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_OK);
    }

    #[Route('/power/add', name: 'power_add', methods: ['POST'])]
    public function powerAddAction(
        Request $request,
        LoggerInterface $logger,
        EntityManagerInterface $em,
        SmlParser $parser,
    ): Response {
        try {
            $record = $parser->parse_sml_hexdata($request->getContent(), 'CRC16_X_25');
            $values = $record['body']['vallist'];

            foreach ($values as $value) {
                $obis = $value['OBIS'] ?? '';

                if ('1-0:1.8.0*255' === $obis) {
                    $em->getRepository(PowerDaily::class)->update($value['value'], (int) log10($value['scaler']));
                } elseif ('1-0:16.7.0*255' === $obis) {
                    $em->getRepository(PowerHourly::class)->update($value['value'], (int) log10($value['scaler']));
                }
            }
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_OK);
    }

    #[Route('/pv/add', name: 'pv_add', methods: ['POST'])]
    public function photovoltaicsAddAction(
        Request $request,
        LoggerInterface $logger,
        EntityManagerInterface $em,
    ): Response {
        try {
            $content = json_decode($request->getContent(), true);
            if (false === $content || is_null($content)) {
                throw new \Exception('Could not decode content!');
            }

            $current = $content['switch:0']['apower'] ?? 0;
            $total = $content['switch:0']['aenergy']['total'] ?? 0;

            // format the numbers
            $current = round($current * 100);
            $current = (int) $current;

            $total = round($total);
            $total = (int) $total;

            $em->getRepository(PvDaily::class)->update($total);
            $em->getRepository(PvHourly::class)->update($current);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_OK);
    }

    #[Route('/solar/add', name: 'solar_add', methods: ['POST'])]
    public function solarAddAction(Request $request, LoggerInterface $logger, EntityManagerInterface $em): Response
    {
        try {
            $content = json_decode($request->getContent(), true);
            if (false === $content || is_null($content)) {
                throw new \Exception(sprintf('Could not decode content! Content was %s', $request->getContent()));
            }

            if (1 !== count($content)) {
                throw new \Exception('Invalid amount of input params!');
            }

            $key = array_key_first($content);
            $yieldTodayTotal = intval($content[$key] ?? 0);

            $em->getRepository(SolarDaily::class)->update($yieldTodayTotal);
            $em->getRepository(SolarHourly::class)->update($yieldTodayTotal);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_OK);
    }

    #[Route('/sensor/add', name: 'sensor_add', methods: ['POST'])]
    public function sensorAddAction(Request $request, LoggerInterface $logger, EntityManagerInterface $em): Response
    {
        try {
            $content = json_decode($request->getContent(), true);
            if (false === $content || is_null($content) || !array_key_exists('m', $content)) {
                throw new \Exception('Could not decode content!');
            }

            $mac = $content['m'];
            unset($content['m']);

            $em->getRepository(Sensor::class)->update($mac, $content);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_OK);
    }

    #[Route('/anker/add/daily', name: 'anker_add_daily', methods: ['POST'])]
    public function ankerAddDailyAction(Request $request, LoggerInterface $logger, EntityManagerInterface $em): Response
    {
        try {
            $ankerDto = AnkerDailyDto::fromJson($request->getContent());
            $em->getRepository(AnkerDaily::class)->update($ankerDto);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_OK);
    }

    #[Route('/anker/add/hourly', name: 'anker_add_hourly', methods: ['POST'])]
    public function ankerAddCurrentAction(Request $request, LoggerInterface $logger, EntityManagerInterface $em): Response
    {
        try {
            $ankerDto = AnkerHourlyDto::fromJson($request->getContent());
            $em->getRepository(AnkerHourly::class)->add($ankerDto);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_OK);
    }
}
