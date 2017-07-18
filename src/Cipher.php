<?php

namespace App;

class Cipher
{

    public $cipher_key = [];

    /**
     * Returns alphabet cipher
     *
     * @return array of the alphabet cipher between plain and encrypted file
     */
    public function getCipherKey()
    {
        return $this->cipher_key;
    }

    public function setCipherKey($key, $value)
    {
        $this->cipher_key[$key] = $value;
    }


    /**
     * Undocumented function
     *
     * @param [type] $plain_word
     * @param [type] $encrypted_word
     * @return void
     */
    public function addToCipherKey($plain_word, $encrypted_word)
    {
        
        $plain_letters = str_split(preg_replace("/[^a-zA-Z]/", "", $plain_word));
        $encrypted_letters = str_split(preg_replace("/[^a-zA-Z]/", "", $encrypted_word));

        foreach ($plain_letters as $key => $letter) {
            if (!array_key_exists(strtolower($letter), $this->getCipherKey()) && !in_array(strtolower($encrypted_letters[$key]), $this->getCipherKey())) {
                $this->cipher_key[strtolower($letter)] = strtolower($encrypted_letters[$key]);
            }
        }
    }

    /**
     * Adds the final letter to the cipher key
     *
     * @return void
     */
    public function getFinalLetter()
    {

        $letters = range('a', 'z');
           
        $final_plain = current(array_diff($letters, array_keys($this->cipher_key)));
        $final_ecrypted = current(array_diff($letters, $this->cipher_key));
        $this->cipher_key[$final_plain] = $final_ecrypted;
    }
}
