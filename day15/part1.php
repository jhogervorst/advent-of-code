<?php

declare(strict_types=1);

function hash_string(string $string): int {
	$value = 0;
	foreach (str_split($string) as $char) {
		$value = (($value + ord($char)) * 17) % 256;
	}
	return $value;
}

$sequence = explode(",", trim(file_get_contents("input.txt")));
$sum = array_sum(array_map("hash_string", $sequence));

echo "Sum: {$sum}\n";
