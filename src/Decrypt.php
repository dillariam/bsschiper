<?php

namespace App;

class Decrypt
{
    const PLAIN_FILE = 'plain.txt';
    const ENCRYPTED_FILE = 'encrypted.txt';
    protected $plain_words = [];
    protected $encrypted_words = [];
    protected $strong_words_length = 18;
    protected $strong_words_min_length = 14;
    protected $strong_words_stage= 1;
    protected $alpha_cipher = [];

    public function generate()
    {
        $this->findEBasedOffOccurence(self::ENCRYPTED_FILE);

        $this->plain_words = $this->getWordsFromFile(self::PLAIN_FILE, 'plain');
        $this->encrypted_words = $this->getWordsFromFile(self::ENCRYPTED_FILE, 'encrypted');

        $this->comparePlainAndEncryptedWords();

        $this->decryptFile(self::ENCRYPTED_FILE);

        var_dump($this->alpha_cipher . ' rarrr');
    }

    public function comparePlainAndEncryptedWords()
    {

        $plain_words = $this->fetchStrongWords($this->plain_words, 'plain');
        $encrypted_words = $this->fetchStrongWords($this->encrypted_words, 'encrypted');

        $matches = [];

        while ($this->strong_words_length >= $this->strong_words_min_length) {
            foreach ($encrypted_words as $encrypted_word => $encrypted_word_meta) {
                foreach ($plain_words as $plain_word => $plain_word_meta) {
                    //Stops after it finds all the letters
                    if (count($this->alpha_cipher)  >= 25) {
                        $this->decryptFile(self::ENCRYPTED_FILE);
                    }

                    //Checks to se if words have 100% match on meta data;  If so we assume same word.
                    if (count(array_diff_assoc($encrypted_word_meta, $plain_word_meta)) == 0) {
                        $this->addToAlphaCipher($plain_word, $encrypted_word);

                        /****************TEST**********************/
                        ksort($this->alpha_cipher);
                        var_dump($this->alpha_cipher);


                        if (count($plain_words) == 0 || count($encrypted_words) == 0) {
                            $this->strong_words_length -= 1;
                            $this->comparePlainAndEncryptedWords();
                        }
                        
                        $this->comparePlainAndEncryptedWords();
                    }
                }
            }

            $this->strong_words_length -= 1;
            $this->comparePlainAndEncryptedWords();
        }
     
    }


    public function getWordsFromFile(String $file, String $type)
    {
        $base = file_get_contents(__DIR__ . "\\..\\files\\$file");

        $words = array_unique(preg_split('/[\s]+/', $base));
        $pretty_words = [];

        foreach ($words as $word) {
           
           if(strlen($word) >=  6){
                //Remove all special characters except the one we are passing in
                $pretty_words[] = strtolower(preg_replace("/[^a-zA-Z-\-\']/", "", $word));
           }
        }

        return $pretty_words;
    }

    
    public function fetchStrongWords(Array $words, String $type)
    {
        $strong_words_array = [];
        foreach ($words as $word) {
            
            if ($this->toneDownRequirements($word)) {
                $strong_words_array[$word] = $this->getWordMetaData($word, $type);
            }
        }

        return $strong_words_array;
    }

    public function toneDownRequirements(String $word)
    {
        if ($this->strong_words_length <  $this->strong_words_min_length) {
            $this->strong_words_stage++;
            $this->strong_words_length = 20;
        }

        switch ($this->strong_words_stage) {
            case 1:
                return strlen($word) == $this->strong_words_length && strpos($word, "-") > 0 && strpos($word, "'") > 0;
                break;
            case 2:
                return strlen($word) == $this->strong_words_length && strpos($word, "-") > 0;
                break;
            case 3:
                return strlen($word) == $this->strong_words_length && strpos($word, "'") > 0;
                break;
            case 4:
                return strlen($word) == $this->strong_words_length;
                break;
            default:
                $this->strong_words_length = 0;
                return strlen($word) == $this->strong_words_length;
                break;
        }
    }

    public function getWordMetaData($word, $type)
    {
        
        //Add letter occurences for each letter
        $wordInfo = [
                'length' => strlen($word),
                'hypen_occurence_rate' => substr_count($word, '-'),
                'first_hypen_position' => stripos($word, '-'),
                'last_hypen_position' => strripos($word, '-'),
                'apostrophe_occurence_rate' => substr_count($word, "'"),
                'first_apostrophe_position' => stripos($word, "'"),
                'last_apostrophe_position' => strripos($word, "'")
        ];

        $wordInfo = array_merge($wordInfo, $this->getLetterDetails($word, $type));
           
        return $wordInfo;
    }

    public function getLetterDetails($word, $type)
    {
        $letter_info = [];

        foreach ($this->alpha_cipher as $real_letter => $cipher_letter) {
            $letter = $type == 'plain' ? $real_letter : $cipher_letter;
            $letter_info[$real_letter . '_occurence_rate']= substr_count($word, $letter);
            $letter_info[$real_letter . '_first_position']= stripos($word, $letter);
            $letter_info[$real_letter . '_last_position']= strripos($word, $letter);
        }

    
        //Array of occurence_rate, first_position, last_position;
        return $letter_info;
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

    public function addToAlphaCipher($plain_word, $encrypted_word)
    {
        
        $plain_letters = str_split(preg_replace("/[^a-zA-Z]/", "", $plain_word));
        $encrypted_letters = str_split(preg_replace("/[^a-zA-Z]/", "", $encrypted_word));

        foreach ($plain_letters as $key => $letter) {
            if (!array_key_exists(strtolower($letter), $this->alpha_cipher) && !in_array(strtolower($encrypted_letters[$key]), $this->alpha_cipher)) {
                $this->alpha_cipher[strtolower($letter)] = strtolower($encrypted_letters[$key]);
            }
        }

        //Removes words for current list
        foreach($this->plain_words as $key => $current_word){
            if($current_word == $plain_word){
                unset($this->plain_words[$key]);
            }
        }

        //Removes words for current list
        foreach($this->encrypted_words as $key => $current_word){
            if($current_word == $encrypted_word){
                unset($this->encrypted_words[$key]);
            }
        }
 
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
}
