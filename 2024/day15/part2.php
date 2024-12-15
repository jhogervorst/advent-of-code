<?php

declare(strict_types=1);

function step(array $position, string $direction): array {
	switch ($direction) {
		case "^": $position["y"]--; break;
		case ">": $position["x"]++; break;
		case "v": $position["y"]++; break;
		case "<": $position["x"]--; break;
	}
	return $position;
}

function move(array &$map, array $position, string $direction): ?array {
	$cell = $map[$position["y"]][$position["x"]];
	$next_position = step($position, $direction);
	$next_cell = $map[$next_position["y"]][$next_position["x"]];

	if ($next_cell == "#") {
		return null;
	}

	if ($next_cell == "[" || $next_cell == "]") {
		$original_map = $map;
		$moved = move($map, $next_position, $direction);

		if ($direction == "^" || $direction == "v") {
			$other_position = step($next_position, $next_cell == "[" ? ">" : "<");
			$moved = $moved && move($map, $other_position, $direction);
		}

		if ($moved) {
			$next_cell = $map[$next_position["y"]][$next_position["x"]];
		} else {
			$map = $original_map;
			return null;
		}
	}

	$map[$position["y"]][$position["x"]] = $next_cell;
	$map[$next_position["y"]][$next_position["x"]] = $cell;

	return $next_position;
}

[$map, $movements] = explode("\n\n", trim(file_get_contents($argv[1])));
$map = str_replace(["#", "O", ".", "@"], ["##", "[]", "..", "@."], $map);
$map = array_map("str_split", explode("\n", $map));
$movements = str_split(str_replace("\n", "", $movements));

$position = [];
$position["y"] = array_find_key($map, fn ($row) => in_array("@", $row));
$position["x"] = array_find_key($map[$position["y"]], fn ($cell) => $cell == "@");

foreach ($movements as $movement) {
	$position = move($map, $position, $movement) ?? $position;
}

$sum_coordinates = 0;
foreach ($map as $y => $row) {
	foreach ($row as $x => $cell) {
		if ($cell == "[") {
			$sum_coordinates += $y * 100 + $x;
		}
	}
}

echo "Sum of boxes' GPS coordinates: {$sum_coordinates}\n";
