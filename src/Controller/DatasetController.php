<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\Redis\CacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DatasetController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/process-huge-dataset",
     *     summary="Process a huge dataset with caching",
     *     @OA\Response(
     *         response=200,
     *         description="Returns processed dataset",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     )
     * )
     */
    #[Route('/process-huge-dataset', methods: ['GET'])]
    public function processHugeDataset(CacheService $cacheService): JsonResponse
    {
        $data = [
            ["id" => 1, "name" => "Item One"],
            ["id" => 2, "name" => "Item Two"],
            ["id" => 3, "name" => "Item Three"],
            ["id" => 4, "name" => "Item Four"],
            ["id" => 5, "name" => "Item Five"],
        ];

        return new JsonResponse($cacheService->handle($data));
    }
}