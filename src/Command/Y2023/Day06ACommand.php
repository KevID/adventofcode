<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:06:A')]
class Day06ACommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 06 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day06.txt');
        $times = $this->extractData($lines[0]);
        $distances = $this->extractData($lines[1]);
        $io->progressStart(count($times));
        
        $records = [];
        foreach ($times as $key => $time) {
            $records[] = $this->countRecords($time, $distances[$key]);
            $io->progressAdvance();
        }
        
        $io->newLine(2);
        $io->success('Sum of all of the calibration values: ' . array_product($records));
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
     * @param string $line Array of data lines.
     * @return array<int> Returns an array of seeds.
     */
    private function extractData(string $line): array
    {
        preg_match_all('/\d[^ ]+/', $line, $data);
        
        return $data[0] ?? [];
    }
    
    /**
     * Counts the possible records.
     * @param int $time The time to count.
     * @param int $distance The distance to count.
     * @return int Returns the number of records.
     */
    private function countRecords(int $time, int $distance): int
    {
        $records = 0;
        
        for ($i = 1; $i <= $time; $i++) {
            if ((($time - $i) * $i) > $distance) {
                $records++;
            }
        }
        
        return $records;
    }
    
}
