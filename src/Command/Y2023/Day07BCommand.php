<?php

namespace App\Command\Y2023;

use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:2023:07:B')]
class Day07BCommand extends Command
{
    /**
     * @var array<string>
     */
    private array $cards = ['J', '2', '3', '4', '5', '6', '7', '8', '9', 'T', 'Q', 'K', 'A'];
    
    /**
     * @var array<string>
     */
    private array $cardsWithoutJ = ['2', '3', '4', '5', '6', '7', '8', '9', 'T', 'Q', 'K', 'A'];
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Advent of Code - Day 07 - Challenge B');
        
        $lines = $this->readLinesFromFile('2023/day07.txt');
        $hands = $this->extractHands($lines);
        $handsOrdered = $this->playGame($hands);
        //dd($this->debug);
        $points = $this->countPoints($handsOrdered);
        
        $io->success('Sum of all of the calibration values: ' . $points);
        
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
        $results = iterator_to_array($reader);
        
        return array_map(function ($value) {
            if (!is_array($value) || count($value) == 0) {
                throw new \UnexpectedValueException('Invalid row format');
            }
            
            return (string)$value[0];
        }, $results);
    }
    
    /**
     * Returns an array of hands.
     * @param array<int, string> $lines Array of data lines.
     * @return array<int, array{0: string, 1: int}> Array of seeds numbers.
     */
    private function extractHands(array $lines): array
    {
        $hands = [];
        
        foreach ($lines as $line) {
            $hand = preg_split('/([AKQJT98765432]+)\s([0-9]+)/', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
            if (!is_array($hand)) {
                // Handle the error, either continue or throw an exception
                continue;
            }
            $hands[] = [(string)$hand[1], (int)$hand[2]];
        }
        
        return $hands;
    }
    
    /**
     * Plays the game, order hand in poker rules.
     * @param array<int, array{0: string, 1: int}> $hands Array of hands.
     * @return array<int, array{0: string, 1: int}> Returns an array of seeds.
     */
    private function playGame(array $hands): array
    {
        usort($hands, function ($hand1, $hand2) {
            return $this->compareHands($hand1[0], $hand2[0]);
        });
        
        return $hands;
    }
    
    /**
     * Compares two hands.
     * @param string $hand1 The first hand.
     * @param string $hand2 The second hand.
     * @return int Returns 1 if the first hand is better, -1 if the second hand is better, 0 if they are equal.
     */
    private function compareHands(string $hand1, string $hand2): int
    {
        $handType1 = $this->getHandType($hand1);
        $handType2 = $this->getHandType($hand2);
        
        if ($handType1 > $handType2) {
            return 1;
        } else if ($handType1 < $handType2) {
            return -1;
        } else {
            return $this->compareHighCard($hand1, $hand2);
        }
    }
    
    /**
     * Returns the type of the hand.
     * @param string $hand The hand to check.
     * @return int Returns the type of the hand.
     */
    private function getHandType(string $hand): int
    {
        $handType = 0;
        
        if ($this->isFiveOfAKind($hand)) {
            $handType = 7;
        } else if ($this->isFourOfAKind($hand)) {
            $handType = 6;
        } else if ($this->isFullHouse($hand)) {
            $handType = 5;
        } else if ($this->isThreeOfAKind($hand)) {
            $handType = 4;
        } else if ($this->isTwoPairs($hand)) {
            $handType = 3;
        } else if ($this->isOnePair($hand)) {
            $handType = 2;
        } else if ($this->isHighCard($hand)) {
            $handType = 1;
        }
        
        return $handType;
    }
    
    /**
     * Checks if the hand is a five of a kind.
     * @param string $hand The hand to check.
     * @return bool Returns true if the hand is a five of a kind, false otherwise.
     */
    private function isFiveOfAKind(string $hand): bool
    {
        foreach ($this->cardsWithoutJ as $card) {
            preg_match_all('/[' . $card . 'J]/', $hand, $matches);
            if (count($matches[0]) === 5) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Checks if the hand is a four of a kind.
     * @param string $hand The hand to check.
     * @return bool Returns true if the hand is a four of a kind, false otherwise.
     */
    private function isFourOfAKind(string $hand): bool
    {
        foreach ($this->cardsWithoutJ as $card) {
            preg_match_all('/[' . $card . 'J]/', $hand, $matches);
            if (count($matches[0]) === 4) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Checks if the hand is a full house.
     * @param string $hand The hand to check.
     * @return bool Returns true if the hand is a full house, false otherwise.
     */
    private function isFullHouse(string $hand): bool
    {
        $threeOfAKind = false;
        $twoOfAKind = false;
        
        foreach ($this->cardsWithoutJ as $card) {
            $hand = $hand ?? '';
            preg_match_all('/[' . $card . 'J]/', $hand, $matches);
            if (count($matches[0]) === 3) {
                $hand = (count(array_keys(str_split($hand), 'J')) > 0) ? preg_replace('/[J]/', '', $hand) : $hand;
                $threeOfAKind = true;
            } else if (count($matches[0]) === 2) {
                $hand = (count(array_keys(str_split($hand), 'J')) > 0) ? preg_replace('/[J]/', '', $hand) : $hand;
                $twoOfAKind = true;
            }
        }
        
        return $threeOfAKind && $twoOfAKind;
    }
    
    /**
     * Checks if the hand is a three of a kind.
     * @param string $hand The hand to check.
     * @return bool Returns true if the hand is a three of a kind, false otherwise.
     */
    private function isThreeOfAKind(string $hand): bool
    {
        foreach ($this->cardsWithoutJ as $card) {
            preg_match_all('/[' . $card . 'J]/', $hand, $matches);
            if (count($matches[0]) === 3) {
                $hand = (count(array_keys(str_split($hand), 'J')) > 0) ? preg_replace('/[J]/', '', $hand) : $hand;
                return true;
            }
        }
        return false;
    }
    
    /**
     * Checks if the hand is a two pairs.
     * @param string $hand The hand to check.
     * @return bool Returns true if the hand is a two pairs, false otherwise.
     */
    private function isTwoPairs(string $hand): bool
    {
        $pairs = 0;
        $numbers = [];
        
        foreach ($this->cardsWithoutJ as $card) {
            $hand = $hand ?? '';
            preg_match_all('/[' . $card . 'J]/', $hand, $matches);
            if (count($matches[0]) === 2) {
                $hand = (count(array_keys(str_split($hand), 'J')) > 0) ? preg_replace('/[J]/', '', $hand) : $hand;
                $numbers = array_merge($numbers, $matches[0]);
                $pairs++;
            }
        }
        
        return $pairs === 2;
    }
    
    /**
     * Checks if the hand is a one pair.
     * @param string $hand The hand to check.
     * @return bool Returns true if the hand is a one pair, false otherwise.
     */
    private function isOnePair(string $hand): bool
    {
        foreach ($this->cards as $card) {
            preg_match_all('/[J]/', $hand, $matcheJ);
            preg_match_all('/[' . $card . ']/', $hand, $matches);
            if (count($matches[0]) === 2 || count($matcheJ[0]) === 1) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Checks if the hand is a high card.
     * @param string $hand The hand to check.
     * @return bool Returns true if the hand is a high card, false otherwise.
     */
    private function isHighCard(string $hand): bool
    {
        return count(array_unique(str_split($hand))) === 5;
    }
    
    /**
     * Compares two hands.
     * @param string $hand1 The first hand.
     * @param string $hand2 The second hand.
     * @return int Returns 1 if the first hand is better, -1 if the second hand is better, 0 if they are equal.
     */
    private function compareHighCard(string $hand1, string $hand2): int
    {
        $hand1 = str_split($hand1);
        $hand2 = str_split($hand2);
        
        for ($i = 0; $i < count($hand1); $i++) {
            $hand1[$i] = array_search($hand1[$i], $this->cards);
            $hand2[$i] = array_search($hand2[$i], $this->cards);
            
            if ($hand1[$i] > $hand2[$i]) {
                return 1;
            } else if ($hand1[$i] < $hand2[$i]) {
                return -1;
            }
        }
        
        return 0;
    }
    
    /**
     * Counts the points of the hands.
     * @param array<int, array{0: string, 1: int}> $hands The hands to count.
     * @return int Returns the number of points.
     */
    private function countPoints(array $hands): int
    {
        $points = 0;
        
        foreach ($hands as $key => $hand) {
            $points += $hand[1] * ($key + 1);
        }
        
        return $points;
    }
}
