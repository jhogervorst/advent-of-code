<?php

declare(strict_types=1);

function step(array $position, string $direction): array {
	switch ($direction) {
		case "N": $position["y"]--; break;
		case "E": $position["x"]++; break;
		case "S": $position["y"]++; break;
		case "W": $position["x"]--; break;
	}
	return $position;
}

function turn(string $direction): string {
	return match ($direction) {
		"N" => "E",
		"E" => "S",
		"S" => "W",
		"W" => "N",
	};
}

function is_blocked(array $map, array $position, string $direction): bool {
	$position = step($position, $direction);
	return is_inside($map, $position) && $map[$position["y"]][$position["x"]] == "#";
}

function is_inside(array $map, array $position): bool {
	return in_array($position["y"], array_keys($map)) && in_array($position["x"], array_keys($map[0]));
}

function walk_outside(array $map, array $position, string $direction): array {
	while (is_inside($map, $position)) {
		if (is_blocked($map, $position, $direction)) {
			$direction = turn($direction);
		} else {
			$map[$position["y"]][$position["x"]] = "X";
			$position = step($position, $direction);
		}
	}
	return $map;
}

$map = [];
$start_position = [];
$start_direction = null;

$fp = fopen($argv[1], "r");
while ($line = trim(fgets($fp) ?: "")) {
	$map[] = str_split($line);
}

foreach ($map as $y => $row) {
	foreach ($row as $x => $cell) {
		if ($cell == "^") {
			$start_position = ["x" => $x, "y" => $y];
			$start_direction = "N";
			$map[$y][$x] = "X";
			break 2;
		}
	}
}

$map = walk_outside($map, $start_position, $start_direction);
$count_visited = count(array_filter(array_merge(...$map), fn($cell) => $cell == "X"));

echo "Visited positions: {$count_visited}\n";
