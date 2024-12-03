<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\DrawingService;

class DrawingController extends AbstractController
{
    private $drawingService;

    public function __construct(DrawingService $drawingService)
    {
        $this->drawingService = $drawingService;
    }

    #[Route('/drawing', name: 'draw_form', methods: ['GET'])]
    public function showForm(): Response
    {
        return $this->render('drawing/index.html.twig');
    }

    #[Route('/drawing', name: 'app_drawing', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $drawing = null;

        $file = $request->files->get('inputFile');

        if ($file instanceof UploadedFile) {
            try {
                $commands = file($file->getPathname(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $drawing = $this->drawingService->processCommands($commands);
                $outputFilePath = $this->getParameter('kernel.project_dir') . '/public/output.txt';
                $this->saveOutputFile($drawing, $outputFilePath);

            } catch (FileException $e) {
                $this->addFlash('error', 'Error al procesar el archivo.');
            }
        } else {
            $this->addFlash('error', 'Archivo no válido.');
        }

        return $this->render('drawing/index.html.twig', [
            'drawing' => $drawing,
        ]);
            
    }


    private function saveOutputFile(array $drawing, string $outputFilePath): void
    {
        $fileContent = '';

        foreach ($drawing as $row) {
            $fileContent .= implode('', $row) . PHP_EOL;
        }

        file_put_contents($outputFilePath, $fileContent);
    }
}

