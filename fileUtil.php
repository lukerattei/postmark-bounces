<?php

function writeJsonToFile($filename, $bounces) {
    if (!$file = fopen($filename, 'w')) {
        return false;
    }
    $bounces = array('bounces' => $bounces);
    $json = '';
    if (defined('JSON_PRETTY_PRINT')) {
        $json = json_encode($bounces, JSON_PRETTY_PRINT);
    }
    else {
        $json = jsonPrettyPrint(json_encode($bounces));
    }
    fwrite($file, $json);
    fclose($file);
    return true;
}

function writeCsvToFile($filename, $bounces) {
    if (!$file = fopen($filename, 'w')) {
        return false;
    }
    // Get all keys from all bounces
    $merged = array();
    foreach ($bounces as $bounce) {
        $merged = array_merge($merged, $bounce);
    }
    $keys = array_keys($merged);
    fputcsv($file, $keys);
    foreach ($bounces as $bounce) {
        $orderedBounces = array();
        foreach ($keys as $key) {
            $orderedBounces[$key] = isset($bounce[$key]) ? $bounce[$key] : '';
        }
        fputcsv($file, $orderedBounces);
    }
    fclose($file);
    return true;
}

/**
 * Found at http://recursive-design.com/blog/2008/03/11/format-json-with-php/
 *
 * Indents a flat JSON string to make it more human-readable.
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function jsonPrettyPrint($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '    ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }

    return $result;
}
