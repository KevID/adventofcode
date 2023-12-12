<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:11:A')]
class Day11ACommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 11 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day11.txt');
        $grid = $this->createGrid($lines);
        $gridExpended = $this->expandGrid($grid);
        $this->printGrid($gridExpended, $io);
        $galaxies = $this->searchGalaxies($gridExpended);
        $sumDistances = $this->calculateSumDistances($galaxies);
        
        
        $io->success('Sum of all of the calibration values: ' . $sumDistances);
        
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
    
    //
    
    /**
     * Expands the grid.
     * Si une ligne ou une colonne ne possède pas de galaxie représentée par le symbole #, on dédouble la ligne ou la colonne.
     * @param array<int, array<int, string>> $grid The grid to expand.
     * @return array<int, array<int, string>> Returns the expanded grid.
     */
    private function expandGrid(array $grid): array
    {
        $newGrid = $this->expandLineGrid($grid);
        $newGrid = $this->inverse_array($newGrid);
        $newGrid = $this->expandLineGrid($newGrid);
        $newGrid = $this->inverse_array($newGrid);
        
        return $newGrid;
    }
    
    /**
     * Expands the grid.
     * Si une ligne ou une colonne ne possède pas de galaxie représentée par le symbole #, on dédouble la ligne ou la colonne.
     * @param array<int, array<int, string>> $grid The grid to expand.
     * @return array<int, array<int, string>> Returns the expanded grid.
     */
    private function expandLineGrid(array $grid): array
    {
        $newGrid = [];
        
        foreach ($grid as $line) {
            if (!in_array('#', $line)) {
                $newGrid[] = $line;
                $newGrid[] = $line;
            } else {
                $newGrid[] = $line;
            }
        }
        
        return $newGrid;
    }
    
    /**
     * Returns the expanded grid.
     * @param array<int, array<int, string>> $grid The grid to expand.
     * @return array<int, array<int, string>> Returns the expanded grid.
     */
    function inverse_array(array $grid): array
    {
        $return_array = [];
        
        foreach ($grid as $cle1 => $arr_elem) {
            foreach ($arr_elem as $cle2 => $elem)
                $return_array[$cle2][$cle1] = $elem;
        }
        
        return $return_array;
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
     * Calculer la distance entre deux galaxies
     * @param array<string, int> $galaxy1 The first galaxy.
     * @param array<string, int> $galaxy2 The second galaxy.
     * @return int Returns the distance between the two galaxies.
     */
    private function calculateDistance(array $galaxy1, array $galaxy2): int
    {
        $distance = 0;
        
        $distance = abs($galaxy1['x'] - $galaxy2['x']) + abs($galaxy1['y'] - $galaxy2['y']);
        
        return $distance;
    }
    
    /**
     * Calculer la somme des distances entre toutes les galaxies
     * @param array<array<string, int>> $galaxies The galaxies.
     * @return int Returns the sum of all distances.
     */
    private function calculateSumDistances(array $galaxies): int
    {
        $sumDistances = 0;
        $nbGalaxies = count($galaxies);
        
        for ($i = 0; $i < $nbGalaxies; $i++) {
            for ($j = $i + 1; $j < $nbGalaxies; $j++) {
                $sumDistances += $this->calculateDistance($galaxies[$i], $galaxies[$j]);
            }
        }
        
        return $sumDistances;
    }
}
