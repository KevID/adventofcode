<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:15:B')]
class Day15BCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 15 - Challenge B');
        
        $lines = $this->readLinesFromFile('2023/day15.txt');
        $lens = $this->getLensSlots($lines[2]);
        $focusingPower = $this->getFocusingPower($lens);
        
        $io->success('Result: ' . $focusingPower);
        
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
    
    /**
     * Returns the lens slots.
     * @param array<int, string> $lens The lens.
     * @return array<int, array<string, int>> The lens slots.
     */
    private function getLensSlots(array $lens): array
    {
        $boxes = [];
        
        foreach ($lens as $len) {
            if (str_contains($len, '-')) {
                $len = explode('-', $len);
                unset($boxes[$this->getNumber($len[0])][(string)$len[0]]);
            } else if (str_contains($len, '=')) {
                $len = explode('=', $len);
                if (count($len) === 2) {
                    $boxes[$this->getNumber($len[0])][(string)$len[0]] = $len[1];
                }
            }
        }
        
        return $boxes;
    }
    
    /**
     * Returns the focusing power.
     * @param array<int, array<string, int>> $lens The lens.
     * @return int The focusing power.
     */
    private function getFocusingPower(array $lens): int
    {
        $focusingPower = 0;
        
        foreach ($lens as $boxKey => $box) {
            $slot = 0;
            foreach ($box as $focalLength) {
                $slot++;
                $focusingPower += ($boxKey + 1) * $slot * $focalLength;
            }
        }
        
        return $focusingPower;
    }
}
