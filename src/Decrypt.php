<?php

namespace App;

use App\Word;

class Decrypt
{
    
    

    public function generate()
    {
        //$this->findEBasedOffOccurence(self::ENCRYPTED_FILE);

        $word = new Word;
        $word->comparePlainAndEncryptedWords();

    }

    public function decryptFile($file)
    {
        $base = strtolower(file_get_contents(__DIR__ . "\\..\\files\\$file"));

        $ciphered_text = implode(array_values($this->alpha_cipher));
        $decrypted_text = implode(array_keys($this->alpha_cipher));

        $new_message = strtr($base, $ciphered_text, $decrypted_text);

        file_put_contents(__DIR__ . "\\..\\files\\decrypted_file.txt", $new_message);

        exit();
    }

   

    

    
    

    public function findEBasedOffOccurence($path)
    {
        $base = file_get_contents(__DIR__ . "\\..\\files\\$path");

        $letterCount = [];

        foreach (range('a', 'z') as $letter) {
            $letterCount[$letter] = substr_count($base, $letter);
        }

        arsort($letterCount);
        
        $this->alpha_cipher['e'] = key($letterCount);
    }

    


    
}
