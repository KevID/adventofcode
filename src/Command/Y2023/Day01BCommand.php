<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(name: 'app:2023:01:B')]
class Day01BCommand extends Command
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
        $io->title('Advent of Code - Day 01 - Challenge B');

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
        // Array with textual and numeric values from 0 o 9
        $numbers = [
            ['string' => 'zero', 'value' => '0'],
            ['string' => 'one', 'value' => '1'],
            ['string' => 'two', 'value' => '2'],
            ['string' => 'three', 'value' => '3'],
            ['string' => 'four', 'value' => '4'],
            ['string' => 'five', 'value' => '5'],
            ['string' => 'six', 'value' => '6'],
            ['string' => 'seven', 'value' => '7'],
            ['string' => 'eight', 'value' => '8'],
            ['string' => 'nine', 'value' => '9'],
            ['string' => '0', 'value' => '0'],
            ['string' => '1', 'value' => '1'],
            ['string' => '2', 'value' => '2'],
            ['string' => '3', 'value' => '3'],
            ['string' => '4', 'value' => '4'],
            ['string' => '5', 'value' => '5'],
            ['string' => '6', 'value' => '6'],
            ['string' => '7', 'value' => '7'],
            ['string' => '8', 'value' => '8'],
            ['string' => '9', 'value' => '9']
        ];
        
        $firstNumber = $lastNumber = [
            'value' => null,
            'position' => null,
        ];
        
        // Find the first and last number in the string
        foreach ($numbers as $number) {
            $firstPosition = strpos($value, $number['string']);
            if (is_int($firstPosition) && ($firstPosition < $firstNumber['position'] || null === $firstNumber['position'])) {
                $firstNumber['value'] = $number['value'];
                $firstNumber['position'] = $firstPosition;
            }
            
            $lastPosition = strrpos($value, $number['string']);
            if (is_int($lastPosition) && ($lastPosition > $lastNumber['position'] || null === $lastNumber['position'])) {
                $lastNumber['value'] = $number['value'];
                $lastNumber['position'] = $lastPosition;
            }
        }
        
        return (int) ($firstNumber['value'] . $lastNumber['value']);
    }
}
