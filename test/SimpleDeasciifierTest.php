<?php

use olcaytaner\Corpus\Sentence;
use olcaytaner\Deasciifier\SimpleDeasciifier;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;

class SimpleDeasciifierTest extends \PHPUnit\Framework\TestCase
{
    public function testSentenceDeasciify(){
        ini_set('memory_limit', '250M');
        $fsm = new FsmMorphologicalAnalyzer();
        $simpleDeasciifier = new SimpleDeasciifier($fsm);
        $this->assertEquals((new Sentence("üçkağıtçılık akışkanlaştırıcılık"))->toString(), ($simpleDeasciifier->deasciify(new Sentence("uckagitcilik akiskanlastiricilik")))->toString());
        $this->assertEquals((new Sentence("çıtçıtçılık düşkırıklığı yüzgörümlüğü"))->toString(), ($simpleDeasciifier->deasciify(new Sentence("citcitcilik duskirikligi yuzgorumlugu")))->toString());
    }
}