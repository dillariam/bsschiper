<?php

namespace spec\App;

use App\Word;
use App\Cipher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WordSpec extends ObjectBehavior
{
    function it_should_return_not_words_from_file_because_doesnt_meet_threshold(Cipher $cipher)
    {
        $this->beConstructedWith($cipher);
        $this->getWordsFromFile('CipherTest/cipher_test1.txt', 'plain')->shouldReturn([]);
    }

    function it_should_return_words_from_file(Cipher $cipher)
    {
        $this->beConstructedWith($cipher);
        $this->getWordsFromFile('CipherTest/cipher_test2.txt', 'plain')->shouldReturn(['hockey', 'football', 'soccer']);
    }

    //Starts off at 18 length with a - and an '
    function it_should_return_strong_words(Cipher $cipher)
    {
        $this->beConstructedWith($cipher);
        $words = ["this-is-how-woww'd", 'pastas', "testsssss-sdfdsadr"];
        $this->fetchStrongWords($words, 'plain')->shouldReturn(["this-is-how-woww'd" => []]);
    }

    function it_should_remove_word_from_array(Cipher $cipher)
    {
        $this->beConstructedWith($cipher);
        $words = ['this', 'that', 'mustache'];
        $word = 'mustache';
        $this->removeWords($words, $word)->shouldReturn(['this', 'that']);
    }
}
