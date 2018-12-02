<?php

$out = null;
$wordsPath = "words.txt";


function buildDictionary($length)
{

    global $wordsPath;

    $wordsContents = file_get_contents($wordsPath);

    preg_match_all('/\w{' . $length . '}/u', $wordsContents, $words);

    $words = array_flip($words[0]);

    set_time_limit(count($words));

    foreach ($words as $word => $id) {

        $subWords = array();
        $variants = array();

        for ($j = 0; $j < $length; $j++) {
            $variants[] =
                mb_substr($word, 0, $j) .
                '[^' . mb_substr($word, $j, 1) . ']' .
                mb_substr($word, $j + 1);
        }

        if (preg_match_all('/' . join("|", $variants) . '/u', $wordsContents, $subWords)) {
            $words[$word] = $subWords[0];
        } else {
            $words[$word] = array();
        }
    }

    return $words;
}

function generateWordChain($startWord, $endWord, $length)
{

    $words = buildDictionary($length);

    $i = 0;
    $found = false;
    $search = [[$startWord => null]];

    $used = [];

    do {
        foreach ($search[$i] as $perm => $root) {

            if (!is_array($search[$i + 1])) $search[$i + 1] = [];

            if (count($words[$perm])) {

                if (in_array($endWord, $words[$perm])) {
                    $found = true;
                }

                $parentPerms = array_flip($words[$perm]);
                foreach ($parentPerms as $childPerm => $childRoot) {
                    if (!in_array($childPerm, $used)) {
                        $parentPerms[$childPerm] = $perm;
                        $used[] = $childPerm;
                    } else {
                        unset($parentPerms[$childPerm]);
                    }
                }


                $search[$i + 1] = array_merge($parentPerms, $search[$i + 1]);
            }
        }
    } while (!$found && count($search[$i++ + 1]));


    $steps = [];
    if ($found) {

        $intermediateWord = $endWord;
        $count = count($search);

        for ($i = $count; $i > -1; $i--) {
            if ($intermediateWord) {
                $steps[] = $intermediateWord;
            }
            $intermediateWord = $search[$i - 1][$intermediateWord];
        }

        $steps = array_reverse($steps);
        $output = "Цепочка: \n";

        foreach ($steps as $step) {
            $output .= $step ;
            $output .= $step == $endWord ? "\n" : " - ";
        }

        $output .= "Длина цепочки: " . $count;

    } else {
        $output = "Цепь не найдена";
    }
    echo $output;
}


@generateWordChain('лужа', 'море', 4);