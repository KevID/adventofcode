<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:13:B')]
class Day13BCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 13 - Challenge B');
        
        $lines = $this->readLinesFromFile('2023/day13.txt');
        $notes = $this->getNotes($lines);
        //$symmetricalLines = $this->getSymmetricalLines($notes[0]);
        //$bestSymmetry = $this->getBestSymmetry($notes[3]);
        
        $io->progressStart(count($notes));
        $getSum = $this->getSum($notes, $io);
        $io->progressFinish();
        
        $io->success('Result: ' . $getSum);
        
        return Command::SUCCESS;
    }
    
    /**
     * Reads lines from a file and returns them.
     * @return array<int, string> Array of lines from the file.
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
     * Returns grouped array of notes.
     * @param array<int, string> $lines Array of lines from the file.
     * @return array<int, array<int, array<int, string>>> Array of notes.
     */
    private function getNotes(array $lines): array
    {
        $data = [];
        $note = 0;
        $increment = 0;
        
        foreach ($lines as $key => $line) {
            if ($key !== $increment) {
                $note++;
                $increment = $key;
            }
            $data[$note][] = str_split($line);
            $increment++;
        }
        
        return $data;
    }
    
    /**
     * Returns the magnitude of the symmetry.
     * @param array<int, array<int, string>> $note Array of notes.
     * @param int $key The key of the line.
     * @return array<string, int|string> Returns the magnitude of the symmetry.
     */
    private function getMagnitudeY(array $note, int $key): array
    {
        $magnitude = 0;
        $smudge = 0;
        $maxMagnitude = max(array_key_last($note) - $key, $key - array_key_first($note));
        
        for ($i = 0; $i <= $maxMagnitude; $i++) {
            if (!array_key_exists($key - $i, $note) || !array_key_exists($key + $i + 1, $note)) {
                break;
            }
            
            // If the lines are not identical, it is possible a smudge.
            if ($note[$key - $i] !== $note[$key + $i + 1]) {
                
                // If it is not a smudge, or so many smudges, it is not symmetrical, we stop the loop.
                if (!$this->isJustOneSmudge($note[$key - $i], $note[$key + $i + 1]) || $smudge > 1) {
                    $magnitude = 0;
                    break;
                }
                
                $smudge++;
            }
            
            $magnitude++;
        }
        
        return [
            'magnitude' => $magnitude,
            'position' => $key + 1,
            'axis' => 'y',
            'smudge' => $smudge,
        ];
    }
    
    /**
     * Returns the magnitude of the symmetry.
     * @param array<int, array<int, string>> $note Array of notes.
     * @param int $key The key of the line.
     * @return array<string, int|string> Returns the magnitude of the symmetry.
     */
    private function getMagnitudeX(array $note, int $key): array
    {
        $magnitude = 0;
        $smudge = 0;
        $maxMagnitude = max(array_key_last($note[0]) - $key, $key - array_key_first($note[0]));
        
        for ($i = 0; $i <= $maxMagnitude; $i++) {
            if (!array_key_exists($key - $i, $note[0]) || !array_key_exists($key + $i + 1, $note[0])) {
                break;
            }
            
            // If the lines are not identical, it is possible a smudge.
            if (array_column($note, $key - $i) !== array_column($note, $key + $i + 1)) {
                
                // If it is not a smudge, or so many smudges, it is not symmetrical, we stop the loop.
                if (!$this->isJustOneSmudge(array_column($note, $key - $i), array_column($note, $key + $i + 1)) || $smudge > 1) {
                    $magnitude = 0;
                    break;
                }
                
                $smudge++;
            }
            
            $magnitude++;
        }
        
        return [
            'magnitude' => $magnitude,
            'position' => $key + 1,
            'axis' => 'x',
            'smudge' => $smudge,
        ];
    }
    
    
    /**
     * Returns true if there is only one smudge.
     * @param array<string> $line1 The first line.
     * @param array<string> $line2 The second line.
     * @return bool Returns true if there is only one smudge.
     */
    private function isJustOneSmudge(array $line1, array $line2): bool
    {
        $smudge = 0;
        
        foreach ($line1 as $key => $value) {
            if ($value !== $line2[$key]) {
                $smudge++;
            }
        }
        
        return $smudge === 1;
    }
    
    /**
     * Returns the best symmetry.
     * @param array<array<string>> $note Array of notes.
     * @return array<string, int|string> Returns the best symmetry.
     */
    private function getBestSymmetry(array $note): array
    {
        $bestSymmetry = ['magnitude' => 0, 'position' => 0];
        
        foreach ($note as $key => $line) {
            $symmetry = $this->getMagnitudeY($note, $key);
            if ($symmetry['magnitude'] > 0 && $symmetry['smudge'] == 1) {
                $bestSymmetry = ($bestSymmetry['magnitude'] >= $symmetry['magnitude']) ? $bestSymmetry : $symmetry;
            }
        }
        
        foreach ($note[0] as $key => $pattern) {
            $symmetry = $this->getMagnitudeX($note, $key);
            if ($symmetry['magnitude'] > 0 && $symmetry['smudge'] == 1) {
                $bestSymmetry = ($bestSymmetry['magnitude'] >= $symmetry['magnitude']) ? $bestSymmetry : $symmetry;
            }
        }
        
        return $bestSymmetry;
    }
    
    /**
     * Returns the sum of the notes.
     * @param array<int, array<int, array<int, string>>> $notes Array of notes.
     * @param SymfonyStyle $io SymfonyStyle instance.
     * @return int Returns the sum of the notes.
     */
    private function getSum(array $notes, SymfonyStyle $io): int
    {
        $sum = 0;
        
        foreach ($notes as $key => $note) {
            $bestSymmetry = $this->getBestSymmetry($note);
            
            if ($bestSymmetry['magnitude'] > 0 && $bestSymmetry['axis'] == 'y') {
                $sum += 100 * (int)$bestSymmetry['position'];
            } else if ($bestSymmetry['magnitude'] > 0 && $bestSymmetry['axis'] == 'x') {
                $sum += $bestSymmetry['position'];
            }
            
            $io->progressAdvance();
        }
        
        return $sum;
    }
    
    // 34398 too low
}
