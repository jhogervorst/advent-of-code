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

function invert($direction) {
	$inverse = "";
	if (str_contains($direction, "N")) {
		$inverse .= "S";
	}
	if (str_contains($direction, "S")) {
		$inverse .= "N";
	}
	if (str_contains($direction, "E")) {
		$inverse .= "W";
	}
	if (str_contains($direction, "W")) {
		$inverse .= "E";
	}
	return $inverse;
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

$matches = 0;

for ($y = 0; $y < count($letters); $y++) {
	for ($x = 0; $x < count($letters[$y]); $x++) {
		$position_matches = 0;
		foreach (["NE", "SE", "SW", "NW"] as $direction) {
			$start = step([$x, $y], invert($direction));
			if (word($letters, $start, $direction, 3) === "MAS") {
				$position_matches++;
			}
		}
		if ($position_matches === 2) {
			$matches++;
		}
	}
}

echo "X-MAS found: {$matches}\n";
