<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/*
 * Find the single giant loop starting at S. How many steps along the loop does it take to get from the starting position to the point farthest from the starting position?
 */

/**
 * Class Day10ACommand
 *
 * Documentation for the Day10ACommand class.
 */
#[AsCommand(name: 'app:2023:10:A')]
class Day10ACommand extends Command
{
    /**
     * @var array<mixed, string[]> Pipes and their possible connections.
     */
    private const PIPES = [
        '|' => ['N', 'S'],
        '-' => ['E', 'W'],
        'L' => ['N', 'E'],
        'J' => ['N', 'W'],
        '7' => ['S', 'W'],
        'F' => ['S', 'E'],
        '.' => [],
        'S' => ['N', 'S', 'E', 'W'],
    ];
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 10 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day10.txt');
        $grid = $this->createGrid($lines);
        $animal = $this->searchAnimal($grid);
        $roads = $this->searchRoads($grid, $animal);
        
        $maxDistance = 0;
        foreach ($roads as $road) {
            $maxDistance = (count($road) / 2 > $maxDistance) ? count($road) / 2 : $maxDistance;
        }
        $io->success('Sum of all of the calibration values: ' . $maxDistance);
        
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
     * Searches for an animal in a grid and returns its position.
     *
     * @param array<int, array<int, string>> $grid The grid to search in.
     * @return array<string, int|string> An associative array containing the animal type and its position.
     */
    private function searchAnimal(array $grid): array
    {
        foreach ($grid as $y => $line) {
            foreach ($line as $x => $cell) {
                if ($cell == 'S') {
                    return [
                        'type' => 'S',
                        'y' => $y,
                        'x' => $x,
                    ];
                }
            }
        }
        
        throw new \UnexpectedValueException('Animal not found');
    }
    
    /**
     * Returns the possible directions from a cell.
     * @param array<string, int|string> $step The coordinates of the cell.
     * @return array<string> Returns the possible directions.
     */
    private function getPossibleDirections(array $step): array
    {
        $directions = [];
        
        foreach (self::PIPES[$step['type']] as $direction) {
            $directions[] = strval($direction);
        }
        
        return $directions;
    }
    
    /**
     * Returns the coordinates of the next cell.
     * @param array<int, int> $coordinates The coordinates of the cell.
     * @param string $direction The direction to go.
     * @return array<int, int> Returns the coordinates of the next cell.
     */
    private function getNextCoordinates(array $coordinates, string $direction): array
    {
        return match ($direction) {
            'N' => [$coordinates[0] - 1, $coordinates[1]],
            'S' => [$coordinates[0] + 1, $coordinates[1]],
            'E' => [$coordinates[0], $coordinates[1] + 1],
            'W' => [$coordinates[0], $coordinates[1] - 1],
            default => throw new \UnexpectedValueException('Invalid direction'),
        };
    }
    
    /**
     * Returns the next step.
     * @param array<int, array<int, string>> $grid The grid to search in.
     * @param array<int, int> $coordinates The coordinates of the cell.
     * @param string $direction The direction to go.
     * @return array<string, int|string> Returns the next cell.
     */
    private function getNextStep(array $grid, array $coordinates, string $direction): array
    {
        $nextCoordinates = $this->getNextCoordinates($coordinates, $direction);
        
        return [
            'type' => $grid[$nextCoordinates[0]][$nextCoordinates[1]],
            'y' => $nextCoordinates[0],
            'x' => $nextCoordinates[1],
        ];
    }
    
    /**
     * Returns the next direction to go.
     * @param string $direction The direction to go.
     * @return string Returns the next direction to go.
     */
    private function getNextDirection(string $direction): string
    {
        return match ($direction) {
            'N' => 'S',
            'S' => 'N',
            'E' => 'W',
            'W' => 'E',
            default => throw new \UnexpectedValueException('Invalid direction'),
        };
    }
    
    
    /**
     * Searches for roads in a grid based on an animal's position.
     *
     * @param array<int, array<int, string>> $grid The grid of the environment.
     * @param array<string, int|string> $animal The position of the animal.
     * @return array<array<int, array<string, int|string>>> An array containing the roads found in the grid.
     */
    private function searchRoads(array $grid, array $animal): array
    {
        /* @var array<string, int|string> $step */
        $step = $animal;
        $road = [];
        
        foreach ($this->getPossibleDirections($step) as $key => $stepStart) {
            $direction = $stepStart;
            while (true) {
                $road[$key][] = $step;
                
                if ($step['type'] !== 'S') {
                    $possibleDirections = $this->getPossibleDirections($step);
                    $direction = implode('', array_diff($possibleDirections, [$direction]));
                }
                
                if ($direction && strlen($direction) === 1) {
                    $step = $this->getNextStep($grid, [(int)$step['y'], (int)$step['x']], $direction);
                    $direction = $this->getNextDirection($direction);
                } else {
                    break;
                }
                
                if ($step['type'] === 'S') {
                    break;
                }
            }
        }
        
        return $road;
    }
}
