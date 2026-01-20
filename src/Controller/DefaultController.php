<?php

namespace App\Controller;

use App\Lib\DashboardAdapter;
use App\Lib\DewpointSensorAdapter;
use App\Lib\HumiditySensorAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $current = $em->getRepository(\App\Entity\Power\Hourly::class)->getLastHours(2);
        $lastDays = $daily->getLastDays();
        $lastYears = $daily->getGroupedByYear();
        $lastMonths = $daily->getLastMonthsByMonths();
        $lastMonthsYearBefore = $daily->getLastMonthsByMonthsYearBefore();

        return $this->render('default/power.html.twig', [
            'current' => $current,
            'lastDays' => $lastDays,
            'lastYears' => $lastYears,
            'lastMonths' => $lastMonths,
            'lastMonthsYearBefore' => $lastMonthsYearBefore,
        ]);
    }

    #[Route('/photovoltaics', name: 'app_photovoltaics')]
    public function photovoltaicsAction(EntityManagerInterface $em): Response
    {
        $daily = $em->getRepository(\App\Entity\Photovoltaics\Daily::class);

        $current = $em->getRepository(\App\Entity\Photovoltaics\Hourly::class)->getLastHours(12);
        $lastDays = $daily->getLastDays();
        $lastYears = $daily->getGroupedByYear();
        $lastMonths = $daily->getLastMonthsByMonths();
        $lastMonthsYearBefore = $daily->getLastMonthsByMonthsYearBefore();

        return $this->render('default/photovoltaics.html.twig', [
            'current' => $current,
            'lastDays' => $lastDays,
            'lastYears' => $lastYears,
            'lastMonths' => $lastMonths,
            'lastMonthsYearBefore' => $lastMonthsYearBefore,
        ]);
    }

    #[Route('/gas', name: 'app_gas')]
    public function gasAction(EntityManagerInterface $em): Response
    {
        $daily = $em->getRepository(\App\Entity\Gas\Daily::class);

        $current = $em->getRepository(\App\Entity\Gas\Hourly::class)->getLastHours(12);
        $lastDays = $daily->getLastDays();
        $lastYears = $daily->getGroupedByYear();
        $lastMonths = $daily->getLastMonthsByMonths();
        $lastMonthsYearBefore = $daily->getLastMonthsByMonthsYearBefore();

        return $this->render('default/gas.html.twig', [
            'current' => $current,
            'lastDays' => $lastDays,
            'lastYears' => $lastYears,
            'lastMonths' => $lastMonths,
            'lastMonthsYearBefore' => $lastMonthsYearBefore,
        ]);
    }

    #[Route('/solar', name: 'app_solar')]
    public function solarAction(EntityManagerInterface $em): Response
    {
        $daily = $em->getRepository(\App\Entity\Solar\Daily::class);

        $current = $em->getRepository(\App\Entity\Solar\Hourly::class)->getLastHours(12);
        $lastDays = $daily->getLastDays();
        $lastYears = $daily->getGroupedByYear();
        $lastMonths = $daily->getLastMonthsByMonths();
        $lastMonthsYearBefore = $daily->getLastMonthsByMonthsYearBefore();

        return $this->render('default/solar.html.twig', [
            'current' => $current,
            'lastDays' => $lastDays,
            'lastYears' => $lastYears,
            'lastMonths' => $lastMonths,
            'lastMonthsYearBefore' => $lastMonthsYearBefore,
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
            'dewpointSeries' => $dewSensorAdapter->pastSeries,
        ]);
    }
}
