<?php

namespace App;

class Letter
{

    public function getFinalLetter($alpha_cipher)
    {

        $letters = range('a', 'z');
           
        $final_plain = current(array_diff($letters, array_keys($alpha_cipher)));
        $final_ecrypted = current(array_diff($letters, $alpha_cipher));
        $alpha_cipher[$final_plain] = $final_ecrypted;

        return $alpha_cipher;
    }
}
