<?php

namespace App;

class Word
{
    const PLAIN_FILE = 'plain.txt';
    const ENCRYPTED_FILE = 'encrypted.txt';
    protected $plain_words = [];
    protected $encrypted_words = [];
    protected $strong_words_length = 18;
    protected $strong_words_min_length = 10;
    protected $strong_words_stage= 1;

    /**
     * Constructor that gets words from both files and has a cipher class
     *
     * @param Cipher $cipher
     */
    public function __construct(Cipher $cipher)
    {
        $this->plain_words = $this->getWordsFromFile(self::PLAIN_FILE, 'plain');
        $this->encrypted_words = $this->getWordsFromFile(self::ENCRYPTED_FILE, 'encrypted');
        $this->cipher = $cipher;
    }

    /**
     * Gets words from File
     *
     * @param String $file pulled from constants
     * @param String $type ('plain', 'encrypted')
     * @return Array of words
     */
    public static function getWordsFromFile(String $file, String $type)
    {
        $base = file_get_contents(__DIR__ . "\\..\\files\\$file");

        $words = array_unique(preg_split('/[\s]+/', $base));
        $pretty_words = [];

        foreach ($words as $word) {
            if (strlen($word) >=  6) {
                //Remove all special characters except - and '
                $pretty_words[] = strtolower(preg_replace("/[^a-zA-Z-\-\']/", "", $word));
            }
        }

        return $pretty_words;
    }

    public function comparePlainAndEncryptedWords()
    {
        //Gets words based off requirements
        $plain_words = $this->fetchStrongWords($this->plain_words, 'plain');
        $encrypted_words = $this->fetchStrongWords($this->encrypted_words, 'encrypted');

        $matches = [];

        //Run while cipher key is not complete
        while (count($this->cipher->getCipherKey()) != 26) {
            foreach ($encrypted_words as $encrypted_word => $encrypted_word_meta) {
                //Gets meta data as it goes along to not take up memory
                $encrypted_word_meta = $this->getWordMetaData($encrypted_word, 'encrypted');

                foreach ($plain_words as $plain_word => $plain_word_meta) {
                    //Gets meta data as it goes along to not take up memory
                    $plain_word_meta = $this->getWordMetaData($plain_word, 'plain');

                    //Checks to se if words have 100% match on meta data;  If so we assume same word.
                    if (count(array_diff_assoc($encrypted_word_meta, $plain_word_meta)) == 0) {
                        //Adds key to cipher and removes both words
                        $this->cipher->addToCipherKey($plain_word, $encrypted_word);
                        $this->plain_words = $this->removeWords($this->plain_words, $plain_word);
                        $this->encrypted_words = $this->removeWords($this->encrypted_words, $encrypted_word);

                        //Gets final letter and then passes encypted file
                        if (count($this->cipher->getCipherKey())  == 25) {
                            $this->cipher->getFinalLetter();
                            return self::ENCRYPTED_FILE;
                        }
                        
                        //If either word bank runs out start over again with new requirements
                        if (count($plain_words) == 0 || count($encrypted_words) == 0) {
                            $this->strong_words_length -= 1;
                            return $this->comparePlainAndEncryptedWords();
                        }
                    }
                }

                //Removes word from current list
                $this->encrypted_words = $this->removeWords($this->encrypted_words, $encrypted_word);
            }

            //Less requirements and run again
            $this->strong_words_length -= 1;
            return $this->comparePlainAndEncryptedWords();
        }

        return;
    }

    
    /**
     * Brings back strong words based off requirements that lessen after more letters are found
     *
     * @param array $words
     * @param String $type
     * @return Array of words considered strong
     */
    public function fetchStrongWords(array $words, String $type)
    {
        $strong_words_array = [];
        foreach ($words as $word) {
            if ($this->requirementsForStrongWords($word)) {
                $strong_words_array[$word] = [];
            }
        }

        return $strong_words_array;
    }

    /**
     * Requirements conditional that change as words are iterated through
     *
     * @param String $word
     * @return conditionals for strong words
     */
    public function requirementsForStrongWords(String $word)
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
            default:
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

        foreach ($this->cipher->getCipherKey() as $real_letter => $cipher_letter) {
            $letter = $type == 'plain' ? $real_letter : $cipher_letter;
            $letter_info[$real_letter . '_occurence_rate']= substr_count($word, $letter);
            $letter_info[$real_letter . '_first_position']= stripos($word, $letter);
            $letter_info[$real_letter . '_last_position']= strripos($word, $letter);
        }

    
        //Array of occurence_rate, first_position, last_position;
        return $letter_info;
    }

    /**
     * Removes words from list
     *
     * @param Array $words
     * @param String $word
     * @return new array of words
     */
    public function removeWords(array $words, String $word)
    {
        //Removes words for current list
        foreach ($words as $key => $current_word) {
            if ($current_word == $word) {
                unset($words[$key]);
            }
        }

        return $words;
    }
}
