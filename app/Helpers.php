<?php
class Helpers {
    public static function generateDomains($loation, $keywords = [], $tldList = [], $maintainOrder = false) {
        $possible_combinations = array();

        $final_keywords = array();
        $final_keywords[] = $loation;

        if ($maintainOrder) {
            $final_keywords[] = implode('', $keywords);
        }
        else {
            $final_keywords = array_merge($final_keywords, $keywords);
        }

        foreach ($final_keywords as $i => $keyword1) {
            $remainingKeywords = $final_keywords;
            unset($remainingKeywords[$i]);
            $possible_combinations[] = $keyword1 . implode('', $remainingKeywords);
        }

        $possible_domains = [];

        foreach ($tldList as $tld) {
            foreach ($possible_combinations as $possible_combination) {
                $domain = $possible_combination . $tld;
                
                if (gethostbyname($domain) == $domain) {
                    $possible_domains[$tld][] = $domain;
                }
            }
        }

        return $possible_domains;
    }
}