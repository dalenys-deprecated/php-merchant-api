<?php

/**
 * Usage:
 * php hash_generator.php PASSWORD URI_QUERY_STRING_TO_HASH
 */
require 'autoload.php';

if ($argc < 2) {
    echo "Usage: php " . __FILE__ . " password IDENTIFIER=foo&PARAM2=bar...\n";
    exit(1);
}

$password = $argv[1];
parse_str($argv[2], $query);

$hash = new Dalenys_Api_Hash_Parameters();

echo $hash->compute($password, $query), "\n";
exit(0);
