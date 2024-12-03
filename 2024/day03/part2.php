<?php

declare(strict_types=1);

$memory = file_get_contents($argv[1]);
preg_match_all("/mul\((\d{1,3}),(\d{1,3})\)|do\(\)|don't\(\)/", $memory, $matches);

$enabled = true;
$sum = 0;

foreach ($matches[0] as $i => $instruction) {
	if ($instruction === "do()") {
		$enabled = true;
	} elseif ($instruction === "don't()") {
		$enabled = false;
	} elseif ($enabled) {
		$sum += $matches[1][$i] * $matches[2][$i];
	}
}

echo "Sum of multiplications: {$sum}\n";
