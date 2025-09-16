<?php

namespace olcaytaner\Deasciifier;

use olcaytaner\Corpus\Sentence;
use olcaytaner\Dictionary\Dictionary\Word;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;
use olcaytaner\NGram\NGram;
use olcaytaner\Util\FileUtils;

class NGramDeasciifier extends SimpleDeasciifier
{
    private NGram $nGram;
    private bool $rootNGram;
    private float $threshold;
    private array $asciifiedSame;

    /**
     * A constructor of {@link NGramDeasciifier} class which takes an {@link FsmMorphologicalAnalyzer} and an {@link NGram}
     * as inputs. It first calls it super class {@link SimpleDeasciifier} with given {@link FsmMorphologicalAnalyzer} input
     * then initializes nGram variable with given {@link NGram} input.
     *
     * @param FsmMorphologicalAnalyzer $fsm {@link FsmMorphologicalAnalyzer} type input.
     * @param NGram $nGram {@link NGram} type input.
     * @param bool $rootNGram True if the NGram have been constructed for the root words, false otherwise.
     */
    public function __construct(FsmMorphologicalAnalyzer $fsm, NGram $nGram, bool $rootNGram)
    {
        parent::__construct($fsm);
        $this->nGram = $nGram;
        $this->rootNGram = $rootNGram;
        $this->asciifiedSame = [];
        $this->loadAsciifiedSameList();
    }

    /**
     * Checks the morphological analysis of the given word in the given index. If there is no misspelling, it returns
     * the longest root word of the possible analyses.
     * @param Sentence $sentence Sentence to be analyzed.
     * @param int $index Index of the word
     * @return Word|null If the word is misspelled, null; otherwise the longest root word of the possible analyses.
     */
    private function checkAnalysisAndSetRoot(Sentence $sentence, int $index): ?Word
    {
        if ($index < $sentence->wordCount()) {
            $fsmParses = $this->fsm->morphologicalAnalysis($sentence->getWord($index)->getName());
            if ($fsmParses->size() != 0) {
                if ($this->rootNGram) {
                    return $fsmParses->getParseWithLongestRootWord()->getWord();
                } else {
                    return $sentence->getWord($index);
                }
            }
        }
        return null;
    }

    /**
     * Sets minimum N-Gram probability threshold for replacement $candidates.
     * @param float $threshold New N-Gram probability threshold
     */
    public function setThreshold(float $threshold): void
    {
        $this->threshold = $threshold;
    }

    /**
     * Returns the bi-gram probability P(word2 | word1) for the given bigram consisting of two words.
     * @param string $word1 First word in bi-gram
     * @param string $word2 Second word in bi-gram
     * @return float Bi-gram probability P(word2 | word1)
     */
    private function getProbability(string $word1, string $word2): float
    {
        return $this->nGram->getProbability($word1, $word2);
    }

    /**
     * The deasciify method takes a {@link Sentence} as an input. First it creates a String {@link Array} as $candidates,
     * and a {@link Sentence} $result-> Then, loops i times where i ranges from 0 to words size of given sentence. It gets the
     * current word and generates a candidateList with this current word then, it loops through the candidateList. First it
     * calls morphologicalAnalysis method with current candidate and gets the first item as root $word-> If it is the first root,
     * it gets its N-gram probability, if there are also other roots, it gets probability of these roots and finds out the
     * best candidate, best root and the best probability. At the nd, it adds the bestCandidate to the bestCandidate {@link Array}.
     *
     * @param Sentence $sentence {@link Sentence} type input.
     * @return Sentence Sentence $result as output.
     */
    public function deasciify(Sentence $sentence): Sentence
    {
        $previousRoot = null;
        $result = new Sentence();
        $root = $this->checkAnalysisAndSetRoot($sentence, 0);
        $nextRoot = $this->checkAnalysisAndSetRoot($sentence, 1);
        for ($repeat = 0; $repeat < 2; $repeat++) {
            for ($i = 0; $i < $sentence->wordCount(); $i++) {
                $candidates = [];
                $isAsciifiedSame = false;
                $word = $sentence->getWord($i);
                if (array_key_exists($word->getName(), $this->asciifiedSame)) {
                    $candidates[] = $word->getName();
                    $candidates[] = $this->asciifiedSame[$word->getName()];
                    $isAsciifiedSame = true;
                }
                if ($root == null || $isAsciifiedSame) {
                    if (!$isAsciifiedSame) {
                        $candidates = $this->candidateList($word);
                    }
                    $bestCandidate = $word->getName();
                    $bestRoot = $word;
                    $bestProbability = $this->threshold;
                    foreach ($candidates as $candidate) {
                        $fsmParses = $this->fsm->morphologicalAnalysis($candidate);
                        if ($this->rootNGram && !$isAsciifiedSame) {
                            $root = $fsmParses->getParseWithLongestRootWord()->getWord();
                        } else {
                            $root = new $word($candidate);
                        }
                        if ($previousRoot != null) {
                            $previousProbability = $this->getProbability($previousRoot->getName(), $root->getName());
                        } else {
                            $previousProbability = 0.0;
                        }
                        if ($nextRoot != null) {
                            $nextProbability = $this->getProbability($root->getName(), $nextRoot->getName());
                        } else {
                            $nextProbability = 0.0;
                        }
                        if (max($previousProbability, $nextProbability) > $bestProbability || count($candidates) == 1) {
                            $bestCandidate = $candidate;
                            $bestRoot = $root;
                            $bestProbability = max($previousProbability, $nextProbability);
                        }
                    }
                    $root = $bestRoot;
                    $result->addWord(new $word($bestCandidate));
                } else {
                    $result->addWord($word);
                }
                $previousRoot = $root;
                $root = $nextRoot;
                $nextRoot = $this->checkAnalysisAndSetRoot($sentence, $i + 2);
            }
            $sentence = $result;
            if ($repeat < 1) {
                $result = new $sentence();
                $previousRoot = null;
                $root = $this->checkAnalysisAndSetRoot($sentence, 0);
                $nextRoot = $this->checkAnalysisAndSetRoot($sentence, 1);
            }
        }
        return $result;
    }

    /**
     * Loads asciified same word list. Asciified same words are the words whose asciified versions are also
     * valid Turkish words. For example, ascified version of 'ekşi' is 'eksi', ascified version of 'fön' is 'fon'.
     */
    private function loadAsciifiedSameList(): void
    {
        $this->asciifiedSame = FileUtils::readHashMap("../asciified-same.txt");
    }
}