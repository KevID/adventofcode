<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:11:B')]
class Day11BCommand extends Command
{
    /**
     * @var int The expansion of the grid.
     */
    private const EXPANSION = 1000000;
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 11 - Challenge B');
        
        $grid = $this->createGrid($this->readLinesFromFile('2023/day11.txt'));
        $this->printGrid($grid, $io);
        $galaxies = $this->searchGalaxies($grid);
        $expansion = $this->calculateExpansion($galaxies);
        $sumDistances = $this->calculateSumDistances($galaxies, $expansion);
        
        $io->success('Sum of all of the distances with expansion: ' . $sumDistances);
        
        return Command::SUCCESS;
    }
    
    /**
     * Reads lines from a file and returns them.
     * @return array<string> Array of lines from the file.
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
     * Creates a grid from the lines.
     * @param array<int, string> $lines The lines to create the grid from.
     * @return array<int, array<int, string>> Returns the grid.
     */
    private function createGrid(array $lines): array
    {
        $grid = [];
        foreach ($lines as $line) {
            $grid[] = str_split($line);
        }
        
        return $grid;
    }
    
    /**
     * Recherche des galaxies
     * @param array<int, array<int, string>> $grid The grid to search in.
     * @return array<array<string, int>> Returns the expanded grid.
     */
    private function searchGalaxies(array $grid): array
    {
        $galaxies = [];
        
        foreach ($grid as $y => $line) {
            foreach ($line as $x => $cell) {
                if ($cell == '#') {
                    $galaxies[] = [
                        'y' => $y,
                        'x' => $x,
                    ];
                }
            }
        }
        
        return $galaxies;
    }
    
    /**
     * Calculates the expansion of galaxies based on their coordinates.
     * @param array<array<string, int>> $galaxies Array of galaxies with their coordinates.
     * @return array<string, array<int>> Array of expansion for each axis ('x' and 'y').
     */
    private function calculateExpansion(array $galaxies): array
    {
        $expansion = [];
        $x = [];
        $y = [];
        
        foreach ($galaxies as $galaxy) {
            $x[] = $galaxy['x'];
            $y[] = $galaxy['y'];
        }
        
        $expansion['x'] = array_values(array_diff(range(min($x), max($x)), $x));
        $expansion['y'] = array_values(array_diff(range(min($y), max($y)), $y));
        
        return $expansion;
    }
    
    /**
     * Calculates the sum of all distances between galaxies.
     * @param array<array<string, int>> $galaxies Array of galaxies with their coordinates.
     * @param array<string, array<int>> $expansion Array of expansion for each axis ('x' and 'y').
     * @return int Returns the sum of all distances between galaxies.
     */
    private function calculateSumDistances(array $galaxies, array $expansion): int
    {
        $sumDistances = 0;
        $nbGalaxies = count($galaxies);
        
        for ($i = 0; $i < $nbGalaxies; $i++) {
            for ($j = $i + 1; $j < $nbGalaxies; $j++) {
                $sumDistances += $this->calculateDistanceWithExpansion($galaxies[$i], $galaxies[$j], $expansion);
            }
        }
        
        return $sumDistances;
    }
    
    /**
     * Calculates the distance between two galaxies with expansion.
     * @param array<string, int> $galaxy1 The first galaxy.
     * @param array<string, int> $galaxy2 The second galaxy.
     * @param array<string, array<int>> $expansion Array of expansion for each axis ('x' and 'y').
     * @return int Returns the distance between the two galaxies.
     */
    private function calculateDistanceWithExpansion(array $galaxy1, array $galaxy2, array $expansion): int
    {
        $expandedDistanceX = $this->calculateExpandedDistance($galaxy1['x'], $galaxy2['x'], $expansion['x']);
        $expandedDistanceY = $this->calculateExpandedDistance($galaxy1['y'], $galaxy2['y'], $expansion['y']);
        
        return $expandedDistanceX + $expandedDistanceY;
    }
    
    /**
     * Calculates the distance between two coordinates with expansion.
     * @param int $coordinate1 The first coordinate.
     * @param int $coordinate2 The second coordinate.
     * @param array<int> $expansionAxis Array of expansion points.
     * @return int Returns the distance between the two coordinates.
     */
    private function calculateExpandedDistance($coordinate1, $coordinate2, array $expansionAxis): int
    {
        if ($coordinate1 > $coordinate2) {
            [$coordinate1, $coordinate2] = [$coordinate2, $coordinate1];
        }
        
        $distance = abs($coordinate1 - $coordinate2);
        
        foreach ($expansionAxis as $expansionPoint) {
            if ($coordinate1 < $expansionPoint && $coordinate2 > $expansionPoint) {
                $distance += (self::EXPANSION - 1);
            }
        }
        
        return $distance;
    }
    
    /**
     * Prints the grid.
     * @param array<int, array<int, string>> $grid The grid to print.
     * @param SymfonyStyle $io The SymfonyStyle object.
     */
    private function printGrid(array $grid, SymfonyStyle $io): void
    {
        foreach ($grid as $row) {
            foreach ($row as $cell) {
                $io->write($cell);
            }
            $io->writeln(''); // Nouvelle ligne après chaque ligne de la grille
        }
    }
}
