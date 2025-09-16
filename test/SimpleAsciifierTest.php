<?php

use olcaytaner\Corpus\Sentence;
use olcaytaner\Deasciifier\SimpleAsciifier;
use olcaytaner\Dictionary\Dictionary\Word;

class SimpleAsciifierTest extends \PHPUnit\Framework\TestCase
{
    public function testWordAsciify(): void{
        $simpleAsciifier = new SimpleAsciifier();
        $this->assertEquals("cogusiCOGUSI", $simpleAsciifier->asciifyWord(new Word("çöğüşıÇÖĞÜŞİ")));
        $this->assertEquals("sogus", $simpleAsciifier->asciifyWord(new Word("söğüş")));
        $this->assertEquals("uckagitcilik", $simpleAsciifier->asciifyWord(new Word("üçkağıtçılık")));
        $this->assertEquals("akiskanlistiricilik", $simpleAsciifier->asciifyWord(new Word("akışkanlıştırıcılık")));
        $this->assertEquals("citcitcilik", $simpleAsciifier->asciifyWord(new Word("çıtçıtçılık")));
        $this->assertEquals("duskirikligi", $simpleAsciifier->asciifyWord(new Word("düşkırıklığı")));
        $this->assertEquals("yuzgorumlugu", $simpleAsciifier->asciifyWord(new Word("yüzgörümlüğü")));
    }

    public function testSentenceAsciify(){
        $simpleAsciifier = new SimpleAsciifier();
        $this->assertEquals((new Sentence("cogus iii COGUSI"))->toString(), ($simpleAsciifier->asciify(new Sentence("çöğüş ııı ÇÖĞÜŞİ")))->toString());
        $this->assertEquals((new Sentence("uckagitcilik akiskanlistiricilik"))->toString(), ($simpleAsciifier->asciify(new Sentence("üçkağıtçılık akışkanlıştırıcılık")))->toString());
        $this->assertEquals((new Sentence("citcitcilik duskirikligi yuzgorumlugu"))->toString(), ($simpleAsciifier->asciify(new Sentence("çıtçıtçılık düşkırıklığı yüzgörümlüğü")))->toString());
    }
}