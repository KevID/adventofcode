<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:15:A')]
class Day15ACommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 15 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day15.txt');
        $verifNumber = $this->getVerifNumber($lines[2]);
        
        $io->success('Result: ' . $verifNumber);
        
        return Command::SUCCESS;
    }
    
    /**
     * Reads lines from a file and returns them.
     * @return array<int, array<int, string>> Array of lines from the file.
     * @throws UnavailableStream
     */
    private function readLinesFromFile(string $filePath): array
    {
        $reader = Reader::createFromPath('%kernel.root_dir%/../import/' . $filePath, 'r');
        $reader->setDelimiter(',');
        $results = iterator_to_array($reader);
        
        return array_map(function ($value) {
            if (!is_array($value) || count($value) == 0) {
                throw new \UnexpectedValueException('Invalid row format');
            }
            
            return $value;
        }, $results);
    }
    
    /**
     * Returns the verification number for the given sequence.
     * @param array<int, string> $sequence The sequence to verify.
     * @return int The verification number.
     */
    private function getVerifNumber(array $sequence): int
    {
        $verifNumber = 0;
        
        foreach ($sequence as $step) {
            $verifNumber += $this->getNumber($step);
        }
        
        return $verifNumber;
    }
    
    /**
     * Returns the number for the given step.
     * @param string $step The step to get the number for.
     * @return int The number for the given step.
     */
    private function getNumber(mixed $step): int
    {
        $number = 0;
        
        foreach (str_split($step) as $character) {
            $number = $this->getCharacterNumber((string)$character, $number);
        }
        
        return $number;
    }
    
    /**
     * Returns the number for the given character.
     * @param string $character The character to get the number for.
     * @param int $currentValue The current value.
     * @return int The number for the given character.
     */
    private function getCharacterNumber(string $character, int $currentValue = 0): int
    {
        $currentValue += ord($character);
        $currentValue = $currentValue * 17;
        $currentValue = (int)fmod($currentValue, 256);
        
        return $currentValue;
    }
}
