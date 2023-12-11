<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:08:A')]
class Day08ACommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 08 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day08.txt');
        $directions = str_split($lines[0]);
        $nodes = $this->extractNodes($lines);
        $countSteps = $this->countSteps($nodes, $directions);
        
        $io->success('Number of steps required to reach AAA to ZZZ: ' . $countSteps);
        
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
        $reader->setDelimiter("\n");
        $results = iterator_to_array($reader);
        
        return array_map(function ($value) {
            if (!is_array($value) || count($value) == 0) {
                throw new \UnexpectedValueException('Invalid row format');
            }
            
            return (string)$value[0];
        }, $results);
    }
    
    /**
     * Extracts the nodes from the lines.
     * @param array<string> $lines The lines to extract from.
     * @return array<string, array<string, string>> Returns an array of nodes.
     */
    private function extractNodes(array $lines): array
    {
        $nodes = [];
        
        foreach ($lines as $key => $line) {
            if ($key === 0) {
                continue;
            }
            preg_match_all('/([A-Z]{3}) = \(([A-Z]{3}), ([A-Z]{3})\)/', $line, $data);
            
            $nodes[(string)$data[1][0]] = [
                'L' => (string)$data[2][0],
                'R' => (string)$data[3][0],
            ];
        }
        
        return $nodes;
    }
    
    /**
     * Counts the steps to reach AAA to ZZZ.
     * @param array<string, array<string, string>> $nodes The nodes to count.
     * @param array<string> $directions The directions to count.
     * @param string $currentNode The current node to count.
     * @return int Returns the number of steps.
     */
    private function countSteps(array $nodes, array $directions, string $currentNode = 'AAA'): int
    {
        $steps = 0;
        
        foreach ($directions as $direction) {
            $currentNode = $nodes[$currentNode][$direction];
            $steps++;
            
            if ($currentNode === 'ZZZ') {
                break;
            }
        }
        
        if ($currentNode !== 'ZZZ') {
            $steps += $this->countSteps($nodes, $directions, $currentNode);
        }
        
        return $steps;
    }
}
