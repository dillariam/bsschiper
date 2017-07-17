<?php

namespace spec;

use App\Decrypt;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DecryptSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Decrypt::class);
    }
}
