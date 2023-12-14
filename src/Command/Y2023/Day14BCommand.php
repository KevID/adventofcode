<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:14:B')]
class Day14BCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 14 - Challenge B');
        
        $lines = $this->readLinesFromFile('2023/day14.txt');
        $grid = $this->createGrid($lines);
        
        $cycles = $this->getCycles($io, $grid, 1000000000);
        
        $this->printGrid($io, $cycles);
        
        $totalLoadNorthSupport = $this->getTotalLoad($cycles);
        
        $io->success('Result: ' . $totalLoadNorthSupport);
        
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
     * Tilt the grid.
     * @param array<int, array<int, string>> $grid The grid to tilt.
     * @return array<int, array<int, string>> Returns the tilted grid.
     */
    private function getTilt(array $grid): array
    {
        foreach ($grid as $keyY => $line) {
            foreach ($line as $keyX => $value) {
                if ($value === 'O') {
                    for ($i = $keyY - 1; $i >= 0; $i--) {
                        // On remonte la pierre tant qu'il n'y a pas d'obstacle au dessus
                        if ($grid[$i][$keyX] !== '.') {
                            $grid[$keyY][$keyX] = '.';
                            $grid[$i + 1][$keyX] = $value;
                            break;
                        }
                        // Cas particulier lorsqu'on est tout en haut
                        if ($i === 0) {
                            $grid[$keyY][$keyX] = '.';
                            $grid[$i][$keyX] = $value;
                        }
                    }
                }
            }
        }
        
        return $grid;
    }
    
    /**
     * Get the total load of the grid.
     * @param array<int, array<int, string>> $grid The grid to get the total load from.
     * @return int Returns the total load.
     */
    private function getTotalLoad(array $grid): int
    {
        $total = 0;
        $nbLines = count($grid);
        
        foreach ($grid as $keyY => $line) {
            foreach ($line as $value) {
                if ($value === 'O') {
                    $total += $nbLines - $keyY;
                }
            }
        }
        
        return $total;
    }
    
    /**
     * Rotate the grid to the right by 90 degrees.
     * @param array<int, array<int, string>> $grid The grid to rotate.
     * @return array<int, array<int, string>> Returns the rotated grid.
     */
    private function rotateGrid(array $grid): array
    {
        $newGrid = [];
        $nbLines = count($grid);
        $nbColumns = count($grid[0]);
        
        for ($i = 0; $i < $nbColumns; $i++) {
            for ($j = 0; $j < $nbLines; $j++) {
                $newGrid[$i][$j] = $grid[$nbLines - $j - 1][$i];
            }
        }
        
        return $newGrid;
    }
    
    /**
     * Print the grid in the terminal.
     * @param SymfonyStyle $io The SymfonyStyle object.
     * @param array<int, array<int, string>> $grid The grid to print.
     */
    private function printGrid(SymfonyStyle $io, array $grid): void
    {
        foreach ($grid as $line) {
            $io->writeln(implode('', $line));
        }
    }
    
    /**
     * Get the result after one cycle (North, West, South, East).
     * @param array<int, array<int, string>> $grid The grid to get the cycle from.
     * @return array<int, array<int, string>> Returns the cycle.
     */
    private function getCycle(array $grid): array
    {
        $cycle = $grid;
        
        for ($i = 1; $i <= 4; $i++) {
            if ($i > 1) {
                $cycle = $this->rotateGrid($cycle);
            }
            
            $cycle = $this->getTilt($cycle);
            
            if ($i === 4) {
                $cycle = $this->rotateGrid($cycle);
            }
        }
        
        return $cycle;
    }
    
    /**
     * Get the result after a number of cycles.
     * @param SymfonyStyle $io The SymfonyStyle object.
     * @param array<int, array<int, string>> $grid The grid to get the cycles from.
     * @param int $nbCycles The number of cycles to get.
     * @return array<int, array<int, string>> Returns the cycles.
     */
    private function getCycles(SymfonyStyle $io, array $grid, int $nbCycles): array
    {
        $loop = $this->getCyclesLoop($io, $grid, $nbCycles);
        $nbCyclesLoop = $loop[1] - $loop[0];
        $lastCycleStart = (int)((floor(($nbCycles - $loop[0]) / $nbCyclesLoop) * $nbCyclesLoop) + $loop[0] + 1);
        $lastNbCycles = $nbCycles - $lastCycleStart;
        
        $lastCycle = $loop[2];
        for ($i = 0; $i < $lastNbCycles; $i++) {
            $lastCycle = $this->getCycle($lastCycle);
        }
        
        return $lastCycle;
    }
    
    /**
     * Get the loop of the cycles.
     * @param SymfonyStyle $io The SymfonyStyle object.
     * @param array<int, array<int, string>> $grid The grid to get the cycles from.
     * @param int $max The maximum number of cycles to get.
     * @return array<int, int, array<int, array<int, string>>>|null Returns the loop.
     */
    private function getCyclesLoop(SymfonyStyle $io, array $grid, int $max = 10000): ?array
    {
        $cycle = $grid;
        $seenStates = [];
        $nbCycles = 0;
        $io->newLine(2);
        $io->writeln('Searching for a loop...');
        $io->progressStart();
        
        while ($nbCycles < $max) {
            $io->progressAdvance();
            $cycle = $this->getCycle($cycle);
            $serializedState = serialize($cycle);
            $nbCycles++;
            
            if (in_array($serializedState, $seenStates)) {
                $io->progressFinish();
                $io->note('Loop found !');
                return [
                    array_search($serializedState, $seenStates),
                    array_key_last($seenStates) + 1,
                    $cycle,
                ];
            }
            $seenStates[] = $serializedState;
        }
        
        return null;
    }
    
}
