<?php

namespace App\Controller;

use App\Lib\DashboardAdapter;
use App\Lib\DewpointSensorAdapter;
use App\Lib\HumiditySensorAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function dashboardAction(DashboardAdapter $adapter): Response
    {
        return $this->render('default/dashboard.html.twig', [
            'heatingSystem' => $adapter->getHeatingSystemValues(),
            'humiditySensors' => $adapter->getLatestHumSensorValues(),
            'power' => $adapter->getActualPowerValues(),
            'gas' => $adapter->getActualGasValues(),
            'solar' => $adapter->getActualSolarValues(),
            'pv' => $adapter->getActualPvValues(),
            'dewPointSensor' => $adapter->getLatestDewPointSensorValue(),
        ]);
    }

    #[Route('/power', name: 'app_power')]
    public function powerAction(EntityManagerInterface $em): Response
    {
        $daily = $em->getRepository(\App\Entity\Power\Daily::class);

        $lastDays = $daily->getLastDays();
        $lastYears = $daily->getGroupedByYear();
        $lastMonths = $daily->getLastMonthsByMonths();
        $lastMonthsYearBefore = $daily->getLastMonthsByMonthsYearBefore();

        return $this->render('default/power.html.twig', [
            'lastDays' => $lastDays,
            'lastYears' => $lastYears,
            'lastMonths' => $lastMonths,
            'lastMonthsYearBefore' => $lastMonthsYearBefore,
        ]);
    }

    #[Route('/power/current', name: 'app_power_current')]
    public function powerCurrentAction(EntityManagerInterface $em): JsonResponse
    {
        $current = $em->getRepository(\App\Entity\Power\Hourly::class)->getLastHours(2);

        return $this->json([
            'series' => array_map(fn($item) => [
                'timestamp' => $item['timestamp'],
                'value' => $item['consumption']
            ], $current)
        ]);
    }

    #[Route('/photovoltaics', name: 'app_photovoltaics')]
    public function photovoltaicsAction(EntityManagerInterface $em): Response
    {
        $daily = $em->getRepository(\App\Entity\Photovoltaics\Daily::class);

        $lastDays = $daily->getLastDays();
        $lastYears = $daily->getGroupedByYear();
        $lastMonths = $daily->getLastMonthsByMonths();
        $lastMonthsYearBefore = $daily->getLastMonthsByMonthsYearBefore();

        return $this->render('default/photovoltaics.html.twig', [
            'lastDays' => $lastDays,
            'lastYears' => $lastYears,
            'lastMonths' => $lastMonths,
            'lastMonthsYearBefore' => $lastMonthsYearBefore,
        ]);
    }

    #[Route('/photovoltaics/current', name: 'app_photovoltaics_current')]
    public function photovoltaicsCurrentAction(EntityManagerInterface $em): Response
    {
        $current = $em->getRepository(\App\Entity\Photovoltaics\Hourly::class)->getLastHours(12);

        return $this->json([
            'series' => array_map(fn($item) => [
                'timestamp' => $item['timestamp'],
                'value' => $item['yield']
            ], $current)
        ]);
    }

    #[Route('/gas', name: 'app_gas')]
    public function gasAction(EntityManagerInterface $em): Response
    {
        $daily = $em->getRepository(\App\Entity\Gas\Daily::class);

        $lastDays = $daily->getLastDays();
        $lastYears = $daily->getGroupedByYear();
        $lastMonths = $daily->getLastMonthsByMonths();
        $lastMonthsYearBefore = $daily->getLastMonthsByMonthsYearBefore();

        return $this->render('default/gas.html.twig', [
            'lastDays' => $lastDays,
            'lastYears' => $lastYears,
            'lastMonths' => $lastMonths,
            'lastMonthsYearBefore' => $lastMonthsYearBefore,
        ]);
    }

    #[Route('/gas/current', name: 'app_gas_current')]
    public function gasCurrentAction(EntityManagerInterface $em): Response
    {
        $current = $em->getRepository(\App\Entity\Gas\Hourly::class)->getLastHours(12);

        return $this->json([
            'series' => array_map(fn($item) => [
                'timestamp' => $item['timestamp'],
                'value' => $item['consumption']
            ], $current)
        ]);
    }

    #[Route('/solar', name: 'app_solar')]
    public function solarAction(EntityManagerInterface $em): Response
    {
        $daily = $em->getRepository(\App\Entity\Solar\Daily::class);

        $lastDays = $daily->getLastDays();
        $lastYears = $daily->getGroupedByYear();
        $lastMonths = $daily->getLastMonthsByMonths();
        $lastMonthsYearBefore = $daily->getLastMonthsByMonthsYearBefore();

        return $this->render('default/solar.html.twig', [
            'lastDays' => $lastDays,
            'lastYears' => $lastYears,
            'lastMonths' => $lastMonths,
            'lastMonthsYearBefore' => $lastMonthsYearBefore,
        ]);
    }

    #[Route('/solar/current', name: 'app_solar_current')]
    public function solarCurrentAction(EntityManagerInterface $em): Response
    {
        $current = $em->getRepository(\App\Entity\Solar\Hourly::class)->getLastHours(12);

        return $this->json([
            'series' => array_map(fn($item) => [
                'timestamp' => $item['timestamp'],
                'value' => $item['yield']
            ], $current)
        ]);
    }

    #[Route('/sensors', name: 'app_sensors')]
    public function sensorsAction(HumiditySensorAdapter $humSensorAdapter, DewpointSensorAdapter $dewSensorAdapter): Response
    {
        $dewSensorAdapter->fetch();

        return $this->render('default/sensors.html.twig', [
            'currentHumidity' => $humSensorAdapter->getCurrentData(),
            'humiditySeries' => $humSensorAdapter->getPastSeries(),
            'currentDewpoint' => $dewSensorAdapter->current,
            'outsideDewPointSeries' => $dewSensorAdapter->outsideDewPointSeries,
            'insideDewPointSeries' => $dewSensorAdapter->insideDewPointSeries
        ]);
    }
}
