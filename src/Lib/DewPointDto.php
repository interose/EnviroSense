<?php

namespace App\Lib;

class DewPointDto
{
    public function __construct(
        public float $outsideTemperature,
        public float $outsideHumidity,
        public float $outsideDewPoint,
        public float $insideTemperature,
        public float $insideHumidity,
        public float $insideDewPoint,
        public string $ventilation,
    ) {
    }

    public static function hydrateFromArray(array $data): self
    {
        return new self(
            $data['te2'] ?? 0,
            $data['h2'] ?? 0,
            $data['dp2'] ?? 0,
            $data['te1'] ?? 0,
            $data['h1'] ?? 0,
            $data['dp1'] ?? 0,
            ($data['vent'] ?? '') === 'on' ? 'On' : 'Off',
        );
    }
}
