<?php

namespace App\Command\Y2023;

use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:18:A')]
class Day18ACommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 18 - Challenge A');
        
        $digPlan = $this->readLinesFromFile('2023/day18.txt');
        $grid = $this->getGrid($digPlan);
        $fillGrid = $this->getFillGrid($grid);
        $cubicMeters = $this->getCubicMeters($fillGrid);
        
        if ($output->isVerbose()) {
            $this->printGrid($fillGrid);
        }
        
        $io->success('Result: ' . $cubicMeters);
        
        return Command::SUCCESS;
    }
    
    /**
     * Reads lines from a file and returns them.
     * @return array<int, array<int, string>> Array of lines from the file.
     * @throws UnavailableStream
     * @throws InvalidArgument
     */
    private function readLinesFromFile(string $filePath): array
    {
        $reader = Reader::createFromPath('%kernel.root_dir%/../import/' . $filePath, 'r');
        $reader->setDelimiter(' ');
        $results = iterator_to_array($reader);
        
        return array_map(function ($value) {
            if (!is_array($value) || count($value) == 0) {
                throw new \UnexpectedValueException('Invalid row format');
            }
            
            return $value;
        }, $results);
    }
    
    /**
     * Returns the grid for the given dig plan.
     * @param int $direction The direction to move.
     * @param int $distance The distance to move.
     * @param int $x The current X position.
     * @param int $y The current Y position.
     * @param array<int, array<int, string>> $grid The grid.
     * @return array<int, array<int, string>> The grid for the given dig plan.
     */
    private function executeMovement($direction, $distance, &$x, &$y, &$grid): array
    {
        switch ($direction) {
            case 'U':
                $this->moveUp($distance, $x, $y, $grid);
                break;
            case 'D':
                $this->moveDown($distance, $x, $y, $grid);
                break;
            case 'L':
                $this->moveLeft($distance, $x, $y, $grid);
                break;
            case 'R':
                $this->moveRight($distance, $x, $y, $grid);
                break;
        }
        
        return $grid;
    }
    
    /**
     * Returns the movement UP on a 2D grid.
     * @param int $distance The distance to move.
     * @param int $x The current X position.
     * @param int $y The current Y position.
     * @param array<int, array<int, string>> $grid The grid.
     */
    private function moveUp($distance, &$x, &$y, &$grid): void
    {
        for ($i = $y; $i >= ($y - $distance); $i--) {
            $grid[$i][$x] = '#';
        }
        $y -= $distance;
    }
    
    /**
     * Returns the movement DOWN on a 2D grid.
     * @param int $distance The distance to move.
     * @param int $x The current X position.
     * @param int $y The current Y position.
     * @param array<int, array<int, string>> $grid The grid.
     */
    private function moveDown($distance, &$x, &$y, &$grid): void
    {
        for ($i = $y; $i <= ($y + $distance); $i++) {
            $grid[$i][$x] = '#';
        }
        $y += $distance;
    }
    
    /**
     * Returns the movement LEFT on a 2D grid.
     * @param int $distance The distance to move.
     * @param int $x The current X position.
     * @param int $y The current Y position.
     * @param array<int, array<int, string>> $grid The grid.
     */
    private function moveLeft($distance, &$x, &$y, &$grid): void
    {
        for ($i = $x; $i >= ($x - $distance); $i--) {
            $grid[$y][$i] = '#';
        }
        $x -= $distance;
    }
    
    /**
     * Returns the movement RIGHT on a 2D grid.
     * @param int $distance The distance to move.
     * @param int $x The current X position.
     * @param int $y The current Y position.
     * @param array<int, array<int, string>> $grid The grid.
     */
    private function moveRight($distance, &$x, &$y, &$grid): void
    {
        for ($i = $x; $i <= ($x + $distance); $i++) {
            $grid[$y][$i] = '#';
        }
        $x += $distance;
    }
    
    /**
     * Returns a grid with the borders of the lagoon.
     * @param array<int, array<int, string>> $digPlan The dig plan.
     * @return array<int, array<int, string>> The grid with the borders of the lagoon.
     */
    private function getBorders(array $digPlan): array
    {
        $grid = [];
        $x = 0;
        $y = 0;
        
        foreach ($digPlan as $dig) {
            $this->executeMovement((int)$dig[0], (int)$dig[1], $x, $y, $grid);
        }
        
        return $grid;
    }
    
    /**
     * Returns the limits of the grid.
     * @param array<int, array<int, string>> $borders The borders of the grid.
     * @return array<int, int> The limits of the grid.
     */
    private function getGridLimits(array $borders): array
    {
        $minX = 0;
        $maxX = 0;
        $minY = min(array_keys($borders));
        $maxY = max(array_keys($borders));
        
        foreach ($borders as $y => $line) {
            $minX = min($minX, min(array_keys($line)));
            $maxX = max($maxX, max(array_keys($line)));
        }
        
        return [$minX, $maxX, $minY, $maxY];
    }
    
    /**
     * Returns the grid for the given dig plan.
     * @param array<int, array<int, string>> $digPlan The dig plan.
     * @return array<int, array<int, string>> The grid for the given dig plan.
     */
    private function getGrid(array $digPlan): array
    {
        $grid = [];
        $borders = $this->getBorders($digPlan);
        
        [$minX, $maxX, $minY, $maxY] = $this->getGridLimits($borders);
        
        for ($y = 0; $y <= ($maxY - $minY); $y++) {
            for ($x = 0; $x <= ($maxX - $minX); $x++) {
                if (array_key_exists($minY + $y, $borders) && array_key_exists($minX + $x, $borders[$minY + $y])) {
                    $grid[$y][$x] = $borders[$minY + $y][$minX + $x];
                } else {
                    $grid[$y][$x] = '.';
                }
            }
        }
        
        return $grid;
    }
    
    /**
     * Returns the filled grid for the given grid.
     * @param array<int, array<int, string>> $grid The grid.
     * @return array<int, array<int, string>> The filled grid for the given grid.
     */
    private function getFillGrid(array $grid): array
    {
        $filledGrid = $grid;
        
        foreach ($grid as $y => $line) {
            $isInLagoon = false;
            
            foreach ($line as $x => $tile) {
                $tileBefore = $y > 0 ? $grid[$y - 1][$x] : '.';
                $isInLagoon = $this->isTileInLagoon($tile, $tileBefore, $isInLagoon);
                
                if ($isInLagoon) {
                    $filledGrid[$y][$x] = '#';
                }
            }
        }
        
        return $filledGrid;
    }
    
    /**
     * Returns whether the given tile is in the lagoon.
     * @param string $tile The tile.
     * @param string $tileBefore The tile before.
     * @param bool $isInsideLagoon Whether the tile is inside the lagoon.
     * @return bool Whether the given tile is in the lagoon.
     */
    private function isTileInLagoon(string $tile, string $tileBefore, bool $isInsideLagoon): bool
    {
        if ($tile === '#' && $tileBefore === '#') {
            $isInsideLagoon = !$isInsideLagoon;
        }
        
        return $isInsideLagoon;
    }
    
    /**
     * Returns the number of cubic meters of water.
     * @param array<int, array<int, string>> $fill The fill.
     * @return int The number of cubic meters of water.
     */
    private function getCubicMeters(array $fill): int
    {
        $cubicMeters = 0;
        
        foreach ($fill as $line) {
            foreach ($line as $tile) {
                if ($tile == '#') {
                    $cubicMeters++;
                }
            }
        }
        
        return $cubicMeters;
    }
    
    /**
     * Prints the grid in the console.
     * @param array<int, array<int, string>> $grid The grid.
     */
    private function printGrid(array $grid): void
    {
        foreach ($grid as $line) {
            foreach ($line as $tile) {
                echo $tile;
            }
            echo "\n";
        }
    }
}
