<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:14:A')]
class Day14ACommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 14 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day14.txt');
        $grid = $this->createGrid($lines);
        $tilt = $this->getTilt($grid);
        $totalLoad = $this->getTotalLoad($tilt);
        
        $io->success('Result: ' . $totalLoad);
        
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
}
