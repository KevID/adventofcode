<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(name: 'app:2023:02:B')]
class Day02BCommand extends Command
{
    private int $sumPowers = 0;
    
    /**
     * @throws UnavailableStream
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 02 - Challenge B');

        $values = $this->readFile('2023/day02.txt');
        
        $io->progressStart(count($values));
        
        /** @var array<string> $value */
        foreach ($values as $value) {
            $io->progressAdvance();
            
            $maxCubesByColor = $this->maxCubesByColor($value);
            
            $this->sumPowers += $maxCubesByColor['blue'] * $maxCubesByColor['green'] * $maxCubesByColor['red'];
        }
        
        $io->newLine(2);
        
        $io->success('Sum of the power of the valid ID: '.$this->sumPowers);
        
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
}
