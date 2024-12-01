<?php

declare(strict_types=1);

define("NORTH", 1);
define("EAST", 2);
define("SOUTH", 4);
define("WEST", 8);
define("DIRECTIONS", [NORTH, EAST, SOUTH, WEST]);

function parse_tiles(string $file): array {
	return array_map("str_split", file($file, FILE_IGNORE_NEW_LINES));
}

function print_tiles(array $tiles): void {
	echo implode("\n", array_map("implode", $tiles)) . "\n";
}

function start_positions(array $tiles): array {
	$result = [];
	foreach ($tiles as $y => $row) {
		foreach ($row as $x => $tile) {
			if ($tile === "S") {
				$result[] = [$x, $y];
			}
		}
	}
	return $result;
}

function step_position(array $position, int $direction): array {
	[$x, $y] = $position;
	return match ($direction) {
		NORTH => [$x, $y - 1],
		EAST => [$x + 1, $y],
		SOUTH => [$x, $y + 1],
		WEST => [$x - 1, $y],
	};
}

function get_position(array $array, array $position): mixed {
	[$x, $y] = $position;
	return $array[$y][$x];
}

function position_exists(array $array, array $position): bool {
	[$x, $y] = $position;
	return isset($array[$y][$x]);
}

function steps(array $tiles, array $positions): array {
	$result = [];
	foreach ($positions as $position) {
		foreach (DIRECTIONS as $direction) {
			$new_position = step_position($position, $direction);
			if (!position_exists($tiles, $new_position)) {
				continue;
			}
			$tile = get_position($tiles, $new_position);
			if ($tile === "#") {
				continue;
			}
			$result[] = $new_position;
		}
	}
	$result = array_unique($result, SORT_REGULAR);
	return $result;
}

$tiles = parse_tiles("input.txt");
$positions = start_positions($tiles);

for ($i = 0; $i < 64; ++$i) {
	$positions = steps($tiles, $positions);
}

$count = count($positions);
echo "Number of positions after 64 steps: {$count}\n";
