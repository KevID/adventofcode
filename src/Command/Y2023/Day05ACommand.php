<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:05:A')]
class Day05ACommand extends Command
{
    /** @var array<int, string> List of maps */
    private array $mapsList = [
        'seed-to-soil',
        'soil-to-fertilizer',
        'fertilizer-to-water',
        'water-to-light',
        'light-to-temperature',
        'temperature-to-humidity',
        'humidity-to-location',
    ];
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 05 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day05.txt');
        
        $seeds = $this->extractSeeds($lines);
        $maps = $this->extractMaps($lines);
        
        $io->progressStart(count($seeds));
        $locations = $this->searchLocations($seeds, $maps, $io);
        
        $io->newLine(2);
        $io->success('Sum of all of the calibration values: ' . min($locations));
        $io->progressFinish();
        
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
     * Returns an array of seeds numbers.
     * @param array<int, string> $lines Array of data lines.
     * @return array<int, int> Returns an array of seeds.
     */
    private function extractSeeds(array $lines): array
    {
        preg_match_all('/\d[^ ]+/', $lines[0], $seeds);
        
        return array_map('intval', array_filter($seeds[0], 'is_numeric'));
    }
    
    /**
     * Returns an array of maps.
     * @param array<int, string> $lines Array of data lines.
     * @return array<string, array<int, array<int, int>>> Returns an array of maps.
     */
    private function extractMaps(array $lines): array
    {
        $maps = [];
        $map = null;
        unset($lines[0]);
        
        foreach ($lines as $line) {
            if (in_array(str_replace(' map:', '', $line), $this->mapsList)) {
                $map = str_replace(' map:', '', $line);
            } else {
                $maps[$map][] = array_map('intval', explode(' ', $line));
            }
        }
        
        return $maps;
    }
    
    /**
     * Returns an array of locations.
     * @param array<int, int> $seeds Array of seeds.
     * @param array<string, array<int, array<int, int>>> $maps Array of maps.
     * @return array<int, int|null> Returns an array of locations.
     */
    private function searchLocations(array $seeds, array $maps, SymfonyStyle $io): array
    {
        $locations = [];
        
        foreach ($seeds as $seed) {
            $locations[$seed] = $this->searchLocation($seed, $maps);
            $io->progressAdvance();
        }
        
        return $locations;
    }
    
    /**
     * Returns a location.
     * @param int $start The seed number.
     * @param array<string, array<int, array<int, int>>> $maps Array of maps.
     * @return int|null Returns a location.
     */
    private function searchLocation(int $start, array $maps): ?int
    {
        $destination = null;
        
        foreach ($this->mapsList as $map) {
            $destination = $this->searchDestination($start, $maps[$map]);
            $start = $destination;
        }
        
        return $destination;
    }
    
    /**
     * Returns a destination.
     * @param int $start The seed number.
     * @param array<int, array<int, int>> $map Array of map.
     * @return int Returns a destination.
     */
    private function searchDestination(int $start, array $map): int
    {
        foreach ($map as $mapRange) {
            if ($start >= $mapRange[1] && $start <= $mapRange[1] + $mapRange[2]) {
                return $mapRange[0] + ($start - $mapRange[1]);
            }
        }
        
        return $start;
    }
}
