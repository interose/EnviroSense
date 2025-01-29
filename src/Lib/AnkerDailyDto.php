<?php

 namespace App\Lib;

 class AnkerDailyDto
 {
     public function __construct(
         public \DateTimeImmutable $ts,
         public int $batteryDischarge,
         public int $homeUsage,
         public int $gridToHome,
         public int $solarProduction,
         public int $batteryCharge,
         public int $solarToGrid,
         public int $batteryPercentage,
         public int $solarPercentage,
     )
     {
     }

     public static function fromJson(string $json): self
     {
         $content = json_decode($json, true);

         $obj = new self(
             new \DateTimeImmutable($content['date']),
             floatval($content['battery_discharge'] ?? 0) * 100,
             floatval($content['home_usage'] ?? 0) * 100,
             floatval($content['grid_to_home'] ?? 0) * 100,
             floatval($content['solar_production'] ?? 0) * 100,
             floatval($content['battery_charge'] ?? 0) * 100,
             floatval($content['solar_to_grid'] ?? 0) * 100,
             floatval($content['battery_percentage'] ?? 0) * 100,
             floatval($content['solar_percentage'] ?? 0) * 100
         );

         return $obj;
     }

     public static function fromCsv(array $line): self
     {
         $obj = new self(
             \DateTimeImmutable::createFromFormat('Y-m-d', $line[0]),
             ($line[1] ?? 0) * 100,
             ($line[2] ?? 0) * 100,
             ($line[3] ?? 0) * 100,
             ($line[5] ?? 0) * 100,
             ($line[6] ?? 0) * 100,
             ($line[7] ?? 0) * 100,
             ($line[8] ?? 0) * 100,
             ($line[9] ?? 0) * 100,
         );

         return $obj;
     }
 }