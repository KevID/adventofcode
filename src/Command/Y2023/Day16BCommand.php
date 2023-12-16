<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:16:B')]
class Day16BCommand extends Command
{
    private const TILES = [
        '.' => [
            'N' => ['N'],
            'E' => ['E'],
            'S' => ['S'],
            'W' => ['W'],
        ],
        '|' => [
            'N' => ['N'],
            'E' => ['N', 'S'],
            'S' => ['S'],
            'W' => ['N', 'S'],
        ],
        '-' => [
            'N' => ['E', 'W'],
            'E' => ['E'],
            'S' => ['E', 'W'],
            'W' => ['W'],
        ],
        '/' => [
            'N' => ['E'],
            'E' => ['N'],
            'S' => ['W'],
            'W' => ['S'],
        ],
        '\\' => [
            'N' => ['W'],
            'E' => ['S'],
            'S' => ['E'],
            'W' => ['N'],
        ],
    ];
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 16 - Challenge B');
        
        $lines = $this->readLinesFromFile('2023/day16.txt');
        $grid = $this->getGrid($lines);
        $maxTilesEnergized = $this->getMaxTilesEnergized($io, $grid);
        
        $io->success('Result: ' . $maxTilesEnergized);
        
        return Command::SUCCESS;
    }
    
    /**
     * Reads lines from a file and returns them.
     * @return array<int, string> Array of lines from the file.
     * @throws UnavailableStream
     */
    private function readLinesFromFile(string $filePath): array
    {
        $reader = Reader::createFromPath('%kernel.root_dir%/../import/' . $filePath, 'r');
        $results = iterator_to_array($reader);
        
        return array_map(function ($value) {
            if (!is_array($value) || count($value) == 0) {
                throw new \UnexpectedValueException('Invalid row format');
            }
            
            return (string)$value[0];
        }, $results);
    }
    
    /**
     * Returns the grid for the given lines.
     * @param array<int, string> $lines The lines to get the grid for.
     * @return array<int, array<int, string>> The grid for the given lines.
     */
    private function getGrid(mixed $lines): array
    {
        $grid = [];
        
        foreach ($lines as $line) {
            $grid[] = str_split($line);
        }
        
        return $grid;
    }
    
    /**
     * Returns the light path for the given grid.
     * The path can split when encountering certain tiles.
     * @param array<int, array<int, string>> $grid The grid to get the light path for.
     * @param array<int, int> $startingTile The starting tile coordinates.
     * @param string $initialDirection The initial direction.
     * @return array<int, array<int, string>> The light path for the given grid.
     */
    private function getLightPath(array $grid, array $startingTile, string $initialDirection): array
    {
        $lightPath = [];
        $visitedTiles = [];
        $stack[] = [$startingTile[0], $startingTile[1], $initialDirection];  // Initial tile and direction
        
        while (!empty($stack)) {
            /** @var int $tileY */
            /** @var int $tileX */
            /** @var string $direction */
            [$tileY, $tileX, $direction] = array_pop($stack);
            
            if ($this->isTileOutOfRange($tileX, $tileY, $grid) ||
                isset($visitedTiles[$tileY][$tileX][$direction])) {
                continue;
            }
            
            $visitedTiles[$tileY][$tileX][$direction] = true;
            $tileValue = $grid[$tileY][$tileX];
            $lightPath[$tileY][$tileX] = $tileValue;
            
            if (isset(self::TILES[$tileValue][$direction])) {
                foreach (self::TILES[$tileValue][$direction] as $newDirection) {
                    $newTile = $this->getNextTile($tileY, $tileX, $newDirection);
                    $stack[] = [$newTile[0], $newTile[1], $newDirection];
                }
            }
        }
        
        return $lightPath;
    }
    
    /**
     * Get the next tile coordinates based on the direction.
     * @param int $tileX The tile X coordinate.
     * @param int $tileY The tile Y coordinate.
     * @param string $direction The direction.
     * @return array<int, int> The next tile coordinates.
     */
    private function getNextTile(int $tileY, int $tileX, string $direction): array
    {
        return match ($direction) {
            'N' => [$tileY - 1, $tileX],
            'E' => [$tileY, $tileX + 1],
            'S' => [$tileY + 1, $tileX],
            'W' => [$tileY, $tileX - 1],
            default => throw new \InvalidArgumentException("Unknown direction: $direction")
        };
    }
    
    
    /**
     * Returns whether the given tile is out of range.
     * @param int $tileX The tile X coordinate.
     * @param int $tileY The tile Y coordinate.
     * @param array<int, array<int, string>> $grid The grid.
     * @return bool Whether the given tile is out of range.
     */
    private function isTileOutOfRange(int $tileX, int $tileY, array $grid): bool
    {
        return $tileX < 0 || $tileY < 0 || $tileY > array_key_last($grid) || $tileX > array_key_last($grid[0]);
    }
    
    /**
     * Returns the number of tiles energized.
     * @param array<int, array<int, string>> $lightPath The light path.
     * @return int The number of tiles energized.
     */
    private function getNbTilesEnergized(array $lightPath): int
    {
        $nbTilesEnergized = 0;
        
        foreach ($lightPath as $line) {
            foreach ($line as $tile) {
                $nbTilesEnergized++;
            }
        }
        
        return $nbTilesEnergized;
    }
    
    /**
     * Returns the maximum number of tiles energized.
     * @param SymfonyStyle $io The SymfonyStyle instance.
     * @param array<int, array<int, string>> $grid The grid.
     * @return int The maximum number of tiles energized.
     */
    private function getMaxTilesEnergized(SymfonyStyle $io, array $grid): int
    {
        $maxTilesEnergized = 0;
        $io->progressStart(2 * count($grid) + 2 * count($grid[0]));
        
        // Tester toutes les tuiles du haut et du bas de la grille
        for ($x = 0; $x < count($grid[0]); $x++) {
            $maxTilesEnergized = max($maxTilesEnergized, $this->getNbTilesEnergized($this->getLightPath($grid, [0, $x], 'S')));
            $maxTilesEnergized = max($maxTilesEnergized, $this->getNbTilesEnergized($this->getLightPath($grid, [count($grid) - 1, $x], 'N')));
            $io->progressAdvance(2);
        }
        
        // Tester toutes les tuiles de gauche et de droite de la grille
        for ($y = 0; $y < count($grid); $y++) {
            $maxTilesEnergized = max($maxTilesEnergized, $this->getNbTilesEnergized($this->getLightPath($grid, [$y, 0], 'E')));
            $maxTilesEnergized = max($maxTilesEnergized, $this->getNbTilesEnergized($this->getLightPath($grid, [$y, count($grid[0]) - 1], 'W')));
            $io->progressAdvance(2);
        }
        
        $io->progressFinish();
        return $maxTilesEnergized;
    }
    
}
