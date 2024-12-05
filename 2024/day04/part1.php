<?php

declare(strict_types=1);

function step($position, $direction) {
	[$x, $y] = $position;
	if (str_contains($direction, "N")) {
		$y--;
	}
	if (str_contains($direction, "S")) {
		$y++;
	}
	if (str_contains($direction, "E")) {
		$x++;
	}
	if (str_contains($direction, "W")) {
		$x--;
	}
	return [$x, $y];
}

function word($letters, $position, $direction, $length) {
	[$x, $y] = $position;
	$word = "";
	for ($i = 0; $i < $length; $i++) {
		$word .= $letters[$y][$x] ?? "";
		[$x, $y] = step([$x, $y], $direction);
	}
	return $word;
}

$letters = [];

$fp = fopen($argv[1], "r");
while ($line = trim(fgets($fp) ?: "")) {
	$letters[] = str_split($line);
}

$word = "XMAS";
$matches = 0;

for ($y = 0; $y < count($letters); $y++) {
	for ($x = 0; $x < count($letters[$y]); $x++) {
		foreach (["N", "NE", "E", "SE", "S", "SW", "W", "NW"] as $direction) {
			if (word($letters, [$x, $y], $direction, strlen($word)) === $word) {
				$matches++;
			}
		}
	}
}

echo "XMAS found: {$matches}\n";
