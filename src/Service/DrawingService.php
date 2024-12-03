<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class DrawingService
{
    private $canvas;

    public function createCanvas($width, $height)
    {
        $this->canvas = array_fill(0, $height, array_fill(0, $width, ' '));
    }

    public function drawLine($x1, $y1, $x2, $y2)
    {
        if ($x1 == $x2) {
            for ($y = min($y1, $y2); $y <= max($y1, $y2); $y++) {
                $this->canvas[$y - 1][$x1 - 1] = 'x';
            }
        } elseif ($y1 == $y2) {
            for ($x = min($x1, $x2); $x <= max($x1, $x2); $x++) {
                $this->canvas[$y1 - 1][$x - 1] = 'x';
            }
        }
    }

    public function drawRectangle($x1, $y1, $x2, $y2)
    {
        $this->drawLine($x1, $y1, $x2, $y1);
        $this->drawLine($x1, $y2, $x2, $y2);
        $this->drawLine($x1, $y1, $x1, $y2);
        $this->drawLine($x2, $y1, $x2, $y2);
    }

    public function bucketFill($x, $y, $color)
    {
        $targetColor = $this->canvas[$y - 1][$x - 1];
        $this->fill($x - 1, $y - 1, $targetColor, $color);
    }

    private function fill($x, $y, $targetColor, $color)
    {
        if ($x < 0 || $y < 0 || $x >= count($this->canvas[0]) || $y >= count($this->canvas)) {
            return;
        }

        if ($this->canvas[$y][$x] !== $targetColor || $this->canvas[$y][$x] === $color) {
            return;
        }

        $this->canvas[$y][$x] = $color;

        $this->fill($x + 1, $y, $targetColor, $color);
        $this->fill($x - 1, $y, $targetColor, $color);
        $this->fill($x, $y + 1, $targetColor, $color);
        $this->fill($x, $y - 1, $targetColor, $color);
    }

    public function processCommands($commands)
    {
        foreach ($commands as $command) {
            $parts = explode(' ', $command);
            $action = $parts[0];

            if ($action === 'C') {
                $this->createCanvas((int)$parts[1], (int)$parts[2]);
            } elseif ($action === 'L') {
                $this->drawLine((int)$parts[1], (int)$parts[2], (int)$parts[3], (int)$parts[4]);
            } elseif ($action === 'R') {
                $this->drawRectangle((int)$parts[1], (int)$parts[2], (int)$parts[3], (int)$parts[4]);
            } elseif ($action === 'B') {
                $this->bucketFill((int)$parts[1], (int)$parts[2], $parts[3][0]);
            } else {
                throw new \InvalidArgumentException("Comando desconocido: $action");
            }
        }

        return $this->canvas;
    }
}
