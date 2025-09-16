<?php

namespace olcaytaner\Deasciifier;

use olcaytaner\Corpus\Sentence;
use olcaytaner\Dictionary\Dictionary\Word;

class SimpleAsciifier extends Asciifier
{

    /**
     * Another asciify method which takes a {@link Sentence} as an input. It loops i times, where i ranges form 0 to number of
     * words in the given sentence. First it gets each word and calls asciify with current word and creates {@link Word}
     * with returned String. At the end, adds each newly created ascified words to the result {@link Sentence}.
     *
     * @param Sentence $sentence {@link Sentence} type input.
     * @return Sentence Sentence output which is asciified.
     */
    public function asciify(Sentence $sentence): Sentence
    {
        $result = new Sentence();
        for ($i = 0; $i < $sentence->wordCount(); $i++) {
            $word = $sentence->getWord($i);
            $newWord = new Word($this->asciifyWord($word));
            $result->addWord($newWord);
        }
        return $result;
    }

    /**
     * The asciify method takes a {@link Word} as an input and converts it to a char {@link Array}. Then,
     * loops i times, where i ranges from 0 to length of the char {@link Array} and substitutes Turkish
     * characters with their corresponding Latin versions and returns it as a new {@link String}.
     *
     * @param Word $word {@link Word} type input to asciify.
     * @return string String output which is asciified.
     */
    public function asciifyWord(Word $word): string{
        $modified = $word->getName();
        $result = "";
        for ($i = 0; $i < mb_strlen($modified); $i++) {
            switch (mb_substr($modified, $i, 1)) {
                case 'ç':
                    $result .= 'c';
                    break;
                case 'ö':
                    $result .= 'o';
                    break;
                case 'ğ':
                    $result .= 'g';
                    break;
                case 'ü':
                    $result .= 'u';
                    break;
                case 'ş':
                    $result .= 's';
                    break;
                case 'ı':
                    $result .= 'i';
                    break;
                case 'Ç':
                    $result .= 'C';
                    break;
                case 'Ö':
                    $result .= 'O';
                    break;
                case 'Ğ':
                    $result .= 'G';
                    break;
                case 'Ü':
                    $result .= 'U';
                    break;
                case 'Ş':
                    $result .= 'S';
                    break;
                case 'İ':
                    $result .= 'I';
                    break;
                default:
                    $result .= mb_substr($modified, $i, 1);
                    break;
            }
        }
        return $result;
    }
}