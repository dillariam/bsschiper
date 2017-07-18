<?php

namespace App;

use App\Word;
use App\Cipher;

class Decrypt
{

    public function generate()
    {
        
        $cipher = new Cipher;
        $word = new Word($cipher);
        
        $file = $word->comparePlainAndEncryptedWords();

        $this->decryptFile($cipher->getCipherKey(), $file);

        echo "The file has been decrypted!\r\n";
    }

    public function decryptFile(array $cipher, String $file)
    {
        $base = strtolower(file_get_contents(__DIR__ . "\\..\\files\\$file"));

        $ciphered_text = implode(array_values($cipher));
        $decrypted_text = implode(array_keys($cipher));

        $new_message = strtr($base, $ciphered_text, $decrypted_text);

        file_put_contents(__DIR__ . "\\..\\files\\decrypted_file.txt", $new_message);

    }
}
