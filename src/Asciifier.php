<?php

namespace olcaytaner\Deasciifier;

use olcaytaner\Corpus\Sentence;

abstract class Asciifier
{
    /**
     * The asciify method which takes a {@link Sentence} as an input and also returns a {@link Sentence} as the output.
     *
     * @param Sentence $sentence {@link Sentence} type input.
     * @return Sentence Sentence result.
     */
    public abstract function asciify(Sentence $sentence): Sentence;
}