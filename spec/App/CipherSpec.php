<?php

namespace spec\App;

use App\Cipher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CipherSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Cipher::class);
    }

    function it_should_set_and_return_cipher_key()
    {
        $this->setCipherKey('e', 'z');
        $this->getCipherKey()->shouldReturn(['e' => 'z']);
    }

    function it_should_add_keys_based_off_words_passed()
    {
        $this->addToCipherKey('fun', 'abc');
        $this->getCipherKey()->shouldReturn(['f' => 'a', 'u' => 'b', 'n' => 'c']);
    }

    function it_should_only_add_new_keys()
    {
        $this->addToCipherKey('fun', 'abc');
        $this->addToCipherKey('funny', 'abcce');
        $this->getCipherKey()->shouldReturn(['f' => 'a', 'u' => 'b', 'n' => 'c' , 'y' => 'e']);
    }

    function it_should_return_final_missing_letter()
    {
        $this->addToCipherKey('abcdefghijkmnopqrstuvwxyz', 'yxwvutsrqponmkljihgfedcba');
        $this->getCipherKey()->shouldNotHaveKeyWithValue('l', 'z');
        $this->getFinalLetter();
        $this->getCipherKey()->shouldHaveKeyWithValue('l', 'z');
    }
}
