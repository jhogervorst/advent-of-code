<?php

declare(strict_types=1);

function get_digit(string $line, bool $from_end = false): int {
	$digits = [
		"one" => 1,
		"two" => 2,
		"three" => 3,
		"four" => 4,
		"five" => 5,
		"six" => 6,
		"seven" => 7,
		"eight" => 8,
		"nine" => 9,
	];

	if ($from_end) {
		$line = strrev($line);
		$digits = array_combine(
			array_map("strrev", array_keys($digits)),
			array_values($digits)
		);
	}

	$regex = "/(\d|" . implode("|", array_keys($digits)) . ")/";
	preg_match($regex, $line, $matches);

	if (array_key_exists($matches[0], $digits)) {
		return $digits[$matches[0]];
	} else {
		return (int) $matches[0];
	}
}

$sum = 0;

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	$line = trim($line);
	$value = (int) (get_digit($line) . get_digit($line, true));
	$sum += $value;
	echo "{$value} - {$line}\n";
}

echo "Sum: {$sum}\n";
