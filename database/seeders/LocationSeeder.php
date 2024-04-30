<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $natal = [
            'lat' => -5.7945,
            'lng' => -35.351,
        ];

        $mossoro = [
            'lat' => -5.1833,
            'lng' => -37.35,
        ];

        $nearNatal = $this->generateNear($natal, 100, 0.04);
        $nearMossoro = $this->generateNear($mossoro, 1000, 0.05);
        Location::insert($nearNatal);
        Location::insert($nearMossoro);
    }

    private function generateNear($location, $qtd, $stdDev) {
        $near = [];
        for ($i = 0; $i < $qtd; $i++) {
            $near[] = [
                'lat' => $this->getRandomNormal($location['lat'], $stdDev),
                'lng' => $this->getRandomNormal($location['lng'], $stdDev),
            ];
        }
        return $near;
    } 

    private function getRandomNormal($mean, $stdDev)
    {
        $u = 0;
        $v = 0;
        while ($u === 0) $u = mt_rand() / mt_getrandmax();
        while ($v === 0) $v = mt_rand() / mt_getrandmax();
        return $mean + $stdDev * sqrt(-2.0 * log($u)) * cos(2.0 * M_PI * $v);
    }
}
