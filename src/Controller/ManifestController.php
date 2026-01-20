<?php

namespace App\Controller;

use App\Lib\DashboardAdapter;
use App\Lib\DewpointSensorAdapter;
use App\Lib\HumiditySensorAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ManifestController extends AbstractController
{
    #[Route('/manifest.json', name: 'app_manifest')]
    public function manifest(AssetMapperInterface $assetMapper): JsonResponse
    {
        $manifest = [
            'name' => 'Envirosense',
            'short_name' => 'Envirosense',
            'description' => 'Monitor your home\'s energy consumption, solar yield, and temperature data',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#2563eb',
            'orientation' => 'portrait-primary',
            'icons' => [],
            'categories' => ['utilities', 'productivity'],
        ];

        $sizes = [192, 512];
        foreach ($sizes as $size) {
            $asset = $assetMapper->getAsset("images/icons/web-app-manifest-{$size}x{$size}.png");
            if ($asset) {
                $manifest['icons'][] = [
                    'src' => $asset->publicPath,
                    'sizes' => "{$size}x{$size}",
                    'type' => 'image/png',
                    'purpose' => 'any'
                ];
            }
        }

        return new JsonResponse($manifest);
    }
}