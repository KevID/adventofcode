<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:03:A')]
class Day03ACommand extends Command
{
    /** @var int[] */
    private array $numbersAdjacentToSymbol = [];
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 03 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day03.txt');
        $io->progressStart(count($lines));
        
        foreach ($lines as $key => $line) {
            $io->progressAdvance();
            $this->processLine($lines, $key, $line);
        }
        
        $io->newLine(2);
        $io->success('Sum of all of the calibration values: ' . array_sum($this->numbersAdjacentToSymbol));
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
        $reader = Reader::createFromPath('%kernel.root_dir%/../import/'.$filePath, 'r');
        $results = iterator_to_array($reader);
        
        return array_map(function ($value) {
            if (!is_array($value) || count($value) == 0) {
                throw new \UnexpectedValueException('Invalid row format');
            }
            
            return (string) $value[0];
        }, $results);
    }
    
    /**
    * Processes a single line from the file.
    * @param string[] $lines Array of lines from the file.
    * @param int $key The current line number.
    * @param string $line The current line content.
    */
    private function processLine(array $lines, int $key, string $line): void
    {
        preg_match_all('/(\d)+/', $line, $numbers, PREG_OFFSET_CAPTURE);
        
        foreach ($numbers[0] as $number) {
            if ($this->isAdjacentToSymbol($lines, $key, $number[1], strlen($number[0]))) {
                $this->numbersAdjacentToSymbol[] = (int) $number[0];
            }
        }
    }
    
    /**
     * Checks if a number is adjacent to a symbol.
     * @param string[] $lines Array of lines from the file.
     * @param int $key The current line number.
     * @param int $offset The offset of the number in the line.
     * @param int $length The length of the number.
     * @return bool
     */
    private function isAdjacentToSymbol(array $lines, int $key, int $offset, int $length): bool
    {
        for ($i = $key - 1; $i <= $key + 1; $i++) {
            if (array_key_exists($i, $lines) && $this->isAdjacentToSymbolInLine($lines[$i], $offset, $length)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Checks if a number in a line of text is adjacent to a symbol.
     * @param string $line The line of text to be checked.
     * @param int $offset The starting position of the number in the line.
     * @param int $length The length of the number in the line.
     * @return bool Returns true if the number is adjacent to a symbol, false otherwise.
     */
    private function isAdjacentToSymbolInLine(string $line, int $offset, int $length): bool
    {
        $lengthAdjustment = ($offset === 0 || ($offset + $length) === strlen($line)) ? 1 : 2;
        $offsetAdjustment = ($offset === 0) ? 0 : -1;
        
        $characters = substr($line, $offset + $offsetAdjustment, $length + $lengthAdjustment);
        
        return (bool) preg_match('/[^0-9A-Z.]+/', $characters);
    }
}
