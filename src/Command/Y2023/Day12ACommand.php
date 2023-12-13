<?php

namespace App\Command\Y2023;

use Generator;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:12:A')]
class Day12ACommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 12 - Challenge A');
        
        $lines = $this->readLinesFromFile('2023/day12.txt');
        $data = $this->getData($lines);
        $totalPossibleCombinations = $this->getTotalPossibleCombinations($data, $io);
        
        $io->success('Result: ' . $totalPossibleCombinations);
        
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
     * Returns the data from the lines.
     * @param array<int, string> $lines
     * @return array<int, array<string, array<int, int|string>>> Array of data.
     */
    private function getData(array $lines): array
    {
        $data = [];
        foreach ($lines as $line) {
            $dataLine = explode(' ', $line);
            $data[] = [
                'springs' => str_split($dataLine[0]),
                'groups' => array_map('intval', explode(',', $dataLine[1])),
            ];
        }
        
        return $data;
    }
    
    /**
     * Returns all the possible combinations for a line.
     * @param array<int, int|string> $springs The array to get the combinations from.
     * @return Generator Generator of combinations (most efficient memory-wise).
     */
    public function getCombinations(array $springs): Generator
    {
        yield from $this->getCombinationsRecursive($springs, 0);
    }
    
    /**
     * Returns all the possible combinations for a line.
     * @param array<int, int|string> $springs The array to get the combinations from.
     * @param int $key The key of the current element.
     * @return Generator Generator of combinations (most efficient memory-wise).
     */
    private function getCombinationsRecursive(array $springs, int $key): Generator
    {
        if ($key >= count($springs)) {
            yield $springs;
            return;
        }
        
        if ($springs[$key] === '?') {
            $arrayCopy = $springs;
            $arrayCopy[$key] = '#';
            yield from $this->getCombinationsRecursive($arrayCopy, $key + 1);
            
            $arrayCopy[$key] = '.';
            yield from $this->getCombinationsRecursive($arrayCopy, $key + 1);
        } else {
            yield from $this->getCombinationsRecursive($springs, $key + 1);
        }
    }
    
    /**
     * Returns the number of possible combinations for a line.
     * @param array<string, array<int, int|string>> $line The line to get the possible combinations from.
     * @return int The number of possible combinations.
     */
    private function getPossibleCombinations(array $line): int
    {
        $possibleCombinations = 0;
        
        foreach ($this->getCombinations($line['springs']) as $combination) {
            if (is_array($combination) && $this->isPossibleCombination($combination, $line['groups'])) {
                $possibleCombinations++;
            }
        }
        
        return $possibleCombinations;
    }
    
    /**
     * Returns true if the combination is possible, false otherwise.
     * @param array<int, int|string> $combination The combination to check.
     * @param array<int, int|string> $groups The groups to check.
     * @return bool True if the combination is possible, false otherwise.
     */
    public function isPossibleCombination(array $combination, array $groups): bool
    {
        $currentKey = 0;
        
        foreach ($groups as $groupSize) {
            $count = 0;
            
            // Passer les "." devant le prochain #
            while ($currentKey < count($combination) && $combination[$currentKey] === '.') {
                $currentKey++;
            }
            
            // Parcourir le tableau jusqu'à trouver tous les "#" consécutifs
            while ($currentKey < count($combination) && $combination[$currentKey] === '#') {
                $count++;
                $currentKey++;
            }
            
            // Vérifier si le nombre de "#" correspond à la taille du groupe actuel
            if ($count != $groupSize) {
                return false;
            }
            
            // S'assurer qu'il y a au moins un "." après un groupe de "#", sauf pour le dernier groupe
            if ($currentKey < count($combination) && $combination[$currentKey] !== '.') {
                return false;
            }
        }
        
        // Vérifier s'il reste des "#" non pris en compte après le dernier groupe
        while ($currentKey < count($combination)) {
            if ($combination[$currentKey] === '#') {
                return false;
            }
            $currentKey++;
        }
        
        return true;
    }
    
    /**
     * Returns the total number of possible combinations for all the lines.
     * @param array<int, array<string, array<int, int|string>>> $data The data.
     * @param SymfonyStyle $io The SymfonyStyle instance.
     * @return int The total number of possible combinations.
     */
    private function getTotalPossibleCombinations(array $data, SymfonyStyle $io): int
    {
        $totalPossibleCombinations = 0;
        
        $io->progressStart(count($data));
        
        foreach ($data as $line) {
            $io->progressAdvance();
            $totalPossibleCombinations += $this->getPossibleCombinations($line);
        }
        
        $io->progressFinish();
        
        return $totalPossibleCombinations;
    }
}
