<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:04:A')]
class Day04ACommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 04 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day04.txt');
        $io->progressStart(count($lines));
        
        $totalPoints = 0;
        foreach ($lines as $key => $line) {
            $io->progressAdvance();
            $totalPoints += (int)$this->processLine($line);
        }
        
        $io->newLine(2);
        $io->success('Sum of all of the calibration values: ' . $totalPoints);
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
     * Processes a single line from the file.
     * @param string $line The current line content.
     * @return int The number of points.
     */
    private function processLine(string $line): int
    {
        $numbers = $this->extractNumbers($line);
        $winningNumbers = $this->findWinningNumbers($numbers['win'], $numbers['available']);
        
        return $this->countPoints($winningNumbers);
    }
    
    /**
     * Extracts numbers from a line on 2 sub-arrays: win and available.
     * @param string $line The current line content.
     * @return array<string, array<int, int>> The extracted numbers.
     */
    private function extractNumbers(string $line): array
    {
        $numbers = [];
        
        $line = str_replace("  ", " ", $line);
        $line = substr($line, strpos($line, ":") + 1);
        $parts = explode("|", $line);
        $numbers['win'] = array_map('intval', explode(" ", trim($parts[0])));
        $numbers['available'] = array_map('intval', explode(" ", trim($parts[1])));
        
        return $numbers;
    }
    
    /**
     * Finds the winning numbers.
     * @param array<int> $availableWinNumbers The available winning numbers.
     * @param array<int> $availableNumbers The available numbers.
     * @return array<int> The winning numbers.
     */
    private function findWinningNumbers(array $availableWinNumbers, array $availableNumbers): array
    {
        $winningNumbers = [];
        
        foreach ($availableWinNumbers as $availableWinNumber) {
            if (in_array($availableWinNumber, $availableNumbers)) {
                $winningNumbers[] = $availableWinNumber;
                continue;
            }
        }
        
        return $winningNumbers;
    }
    
    /**
     * Counts the points of the winning numbers.
     * @param array<int> $winningNumbers The winning numbers.
     * @return int The points.
     */
    private function countPoints(array $winningNumbers): int
    {
        $nbWinningNumbers = count($winningNumbers);
        
        return ($nbWinningNumbers == 1) ? 1 : (int)pow(2, ($nbWinningNumbers - 1));
    }
}
