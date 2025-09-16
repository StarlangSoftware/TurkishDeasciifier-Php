<?php

namespace olcaytaner\Deasciifier;

use olcaytaner\Corpus\Sentence;
use olcaytaner\Deasciifier\Deasciifier;
use olcaytaner\Dictionary\Dictionary\Word;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;

class SimpleDeasciifier extends Deasciifier
{
    protected FsmMorphologicalAnalyzer $fsm;

    /**
     * A constructor of {@link SimpleDeasciifier} class which takes a {@link FsmMorphologicalAnalyzer} as an input and
     * initializes fsm variable with given {@link FsmMorphologicalAnalyzer} input.
     *
     * @param FsmMorphologicalAnalyzer $fsm {@link FsmMorphologicalAnalyzer} type input.
     */
    public function __construct(FsmMorphologicalAnalyzer $fsm){
        $this->fsm = $fsm;
    }

    /**
     * The generateCandidateList method takes an {@link Array} $candidates, a {@link String}, and an integer $index as inputs.
     * First, it creates a {@link String} which consists of corresponding Latin versions of special Turkish characters. If given $index
     * is less than the length of given word and if the item of word's at given $index is one of the chars of {@link String}, it loops
     * given $candidates {@link Array}'s size times and substitutes Latin characters with their corresponding Turkish versions
     * and put them to newly created char {@link Array} $modified. At the end, it adds each $modified item to the $candidates
     * {@link Array} as a {@link String} and recursively calls generateCandidateList with next $index.
     *
     * @param array $candidates {@link ArrayList} type input.
     * @param string $word       {@link String} input.
     * @param int $index      {@link Integer} input.
     */
    private function generateCandidateList(array& $candidates, string $word, int $index){
        $s = "ıiougcsİIOUGCS";
        if ($index < mb_strlen($word)) {
            if (str_contains($s, mb_substr($word, $index, 1))) {
                $size = count($candidates);
                for ($i = 0; $i < $size; $i++) {
                    $modified = $candidates[$i];
                    switch (mb_substr($word, $index, 1)) {
                        case 'ı':
                            $modified = mb_substr($modified, 0, $index) . 'i' . mb_substr($modified, $index + 1);
                            break;
                        case 'i':
                            $modified = mb_substr($modified, 0, $index) . 'ı' . mb_substr($modified, $index + 1);
                            break;
                        case 'o':
                            $modified = mb_substr($modified, 0, $index) . 'ö' . mb_substr($modified, $index + 1);
                            break;
                        case 'u':
                            $modified = mb_substr($modified, 0, $index) . 'ü' . mb_substr($modified, $index + 1);
                            break;
                        case 'g':
                            $modified = mb_substr($modified, 0, $index) . 'ğ' . mb_substr($modified, $index + 1);
                            break;
                        case 'c':
                            $modified = mb_substr($modified, 0, $index) . 'ç' . mb_substr($modified, $index + 1);
                            break;
                        case 's':
                            $modified = mb_substr($modified, 0, $index) . 'ş' . mb_substr($modified, $index + 1);
                            break;
                        case 'I':
                            $modified = mb_substr($modified, 0, $index) . 'İ' . mb_substr($modified, $index + 1);
                            break;
                        case 'İ':
                            $modified = mb_substr($modified, 0, $index) . 'I' . mb_substr($modified, $index + 1);
                            break;
                        case 'O':
                            $modified = mb_substr($modified, 0, $index) . 'Ö' . mb_substr($modified, $index + 1);
                            break;
                        case 'U':
                            $modified = mb_substr($modified, 0, $index) . 'Ü' . mb_substr($modified, $index + 1);
                            break;
                        case 'G':
                            $modified = mb_substr($modified, 0, $index) . 'Ğ' . mb_substr($modified, $index + 1);
                            break;
                        case 'C':
                            $modified = mb_substr($modified, 0, $index) . 'Ç' . mb_substr($modified, $index + 1);
                            break;
                        case 'S':
                            $modified = mb_substr($modified, 0, $index) . 'Ş' . mb_substr($modified, $index + 1);
                            break;
                    }
                    $candidates[] = $modified;
                }
            }
            if (count($candidates) < 10000){
                $this->generateCandidateList($candidates, $word, $index + 1);
            }
        }
    }

    /**
     * The candidateList method takes a {@link Word} as an input and creates new candidates {@link Array}. First it
     * adds given word to this {@link Array} and calls generateCandidateList method with candidates, given word and
     * index 0. Then, loops i times where i ranges from 0 to size of candidates {@link Array} and calls morphologicalAnalysis
     * method with ith item of candidates {@link Array}. If it does not return any analysis for given item, it removes
     * the item from candidates {@link Array}.
     *
     * @param Word $word {@link Word} type input.
     * @return array ArrayList candidates.
     */
    protected function candidateList(Word $word): array{
        $candidates = [];
        $candidates[] = $word->getName();
        $this->generateCandidateList($candidates, $word->getName(), 0);
        for ($i = 0; $i < count($candidates); $i++) {
            $fsmParseList = $this->fsm->morphologicalAnalysis($candidates[$i]);
            if ($fsmParseList->size() == 0) {
                array_splice($candidates, $i, 1);
                $i--;
            }
        }
        return $candidates;
    }

    /**
     * The deasciify method takes a {@link Sentence} as an input and loops $i times where $i ranges from 0 to number of
     * words in the given {@link Sentence}. First it gets ith word from given {@link Sentence} and calls candidateList with
     * ith word and assigns the returned {@link Array} to the newly created $candidates {@link Array}. And if the size of
     * $candidates {@link Array} is greater than 0, it generates a random number and gets the item of $candidates {@link Array}
     * at the $index of random number and assigns it as a newWord. If the size of $candidates {@link Array} is 0, it then
     * directly assigns ith word as the newWord. At the end, it adds newWord to the result {@link Sentence}.
     *
     * @param Sentence $sentence {@link Sentence} type input.
     * @return Sentence result {@link Sentence}.
     */
    public function deasciify(Sentence $sentence): Sentence
    {
        $result = new $sentence();
        for ($i = 0; $i < $sentence->wordCount(); $i++) {
            $word = $sentence->getWord($i);
            $fsmParseList = $this->fsm->morphologicalAnalysis($word->getName());
            if ($fsmParseList->size() == 0){
                $candidates = $this->candidateList($word);
                if (count($candidates) > 0) {
                    $randomCandidate = random_int(0, count($candidates) - 1);
                    $newWord = new $word($candidates[$randomCandidate]);
                } else {
                    $newWord = $word;
                }
            } else {
                $newWord = $word;
            }
            $result->addWord($newWord);
        }
        return $result;
    }
}