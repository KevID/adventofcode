<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(name: 'app:2023:02:A')]
class Day02ACommand extends Command
{
    private int $sumOfValidId = 0;
    
    /**
     * @throws UnavailableStream
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 02 - Challenge A');

        $values = $this->readFile('2023/day02.txt');
        
        $io->progressStart(count($values));
        
        /** @var array<string> $value */
        foreach ($values as $key => $value) {
            $io->progressAdvance();
            
            if ($this->isPossibleGame($this->maxCubesByColor($value), 14, 13, 12)) {
                $this->sumOfValidId += $key + 1;
            }
        }
        
        $io->newLine(2);
        
        $io->success('Sum of the valid ID: '.$this->sumOfValidId);
        
        $io->progressFinish();
        
        return Command::SUCCESS;
    }
    
    /**
     * @throws UnavailableStream
     */
    private function readFile(string $filePath): Reader
    {
        $data = Reader::createFromPath('%kernel.root_dir%/../import/'.$filePath, 'r');
        $data->setDelimiter(';');
        
        return $data;
    }
    
    /**
     * Counts the number of cubes by color.
     *
     * @param array<string> $value The array containing the tours of one game.
     * @return array<string, int> Associative array with keys 'blue', 'green', 'red' and their counts.
     */
    private function maxCubesByColor(array $value): array
    {
        $blue = $green = $red = 0;
        
        foreach ($value as $tour) {
            preg_match_all("/(\d+)? (blue|green|red)/U", $tour, $cubes);
            
            foreach ($cubes[2] as $key => $cube) {
                switch ($cube) {
                    case 'blue':
                        $blue = ((int) $cubes[1][$key] > $blue) ? (int) $cubes[1][$key] : $blue;
                        break;
                    case 'green':
                        $green = ((int) $cubes[1][$key] > $green) ? (int) $cubes[1][$key] : $green;
                        break;
                    case 'red':
                        $red = ((int) $cubes[1][$key] > $red) ? (int) $cubes[1][$key] : $red;
                        break;
                }
            }
        }
        
        return [
            'blue' => $blue,
            'green' => $green,
            'red' => $red
        ];
    }
    
    /**
     * Determines if a game is possible based on the maximum number of each color cube.
     *
     * @param array<string, int> $resultGame Associative array with keys 'blue', 'green', 'red' and their counts.
     * @param int $maxBlue Maximum number of blue cubes.
     * @param int $maxGreen Maximum number of green cubes.
     * @param int $maxRed Maximum number of red cubes.
     * @return bool True if the game is possible, false otherwise.
     */
    private function isPossibleGame(array $resultGame, int $maxBlue, int $maxGreen, int $maxRed): bool
    {
        if (!$resultGame || $resultGame['blue'] > $maxBlue || $resultGame['green'] > $maxGreen || $resultGame['red'] > $maxRed) {
            return false;
        }
        
        return true;
    }
}
