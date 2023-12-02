<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(name: 'app:2023:01:A')]
class Day01ACommand extends Command
{
    /**
     * @var int[] $calibrationValues
     */
    private array $calibrationValues = [];
    
    /**
     * @throws UnavailableStream
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 01 - Challenge A');

        $values = $this->readFile('2023/day01.txt');
        
        $io->progressStart(count($values));
        
        foreach ($values as $value) {
            $io->progressAdvance();
            $this->calibrationValues[] = is_string($value[0]) ? $this->returnCalibrationValue($value[0]) : 0;
        }
        
        $io->newLine(2);
        
        $io->success('Sum of all of the calibration values: '.array_sum($this->calibrationValues));
        
        $io->progressFinish();
        
        return Command::SUCCESS;
    }
    
    /**
     * @throws UnavailableStream
     */
    private function readFile(string $filePath): Reader
    {
        return Reader::createFromPath('%kernel.root_dir%/../import/'.$filePath, 'r');
    }
    
    private function returnCalibrationValue(string $value): int
    {
        // Remove all non-numeric characters
        $numbers = preg_replace("/[^0-9]/", "", $value) ?? '';
        
        // Return first character of the string
        $number1 = substr($numbers, 0, 1);
        
        // Return last character of the string
        $number2 = substr($numbers, -1);
        
        return (int) ($number1 . $number2);
    }
}
