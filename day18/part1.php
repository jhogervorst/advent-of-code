<?php

declare(strict_types=1);

define("UP", "U");
define("DOWN", "D");
define("LEFT", "L");
define("RIGHT", "R");
define("DIRECTIONS", [UP, DOWN, LEFT, RIGHT]);

define("GROUND", ".");
define("TRENCH", "#");

define("MAP_SIZE", 1000);

/** @source https://stackoverflow.com/a/47718714/251760 */
function transpose($array_one) {
	$array_two = [];
	foreach ($array_one as $key => $item) {
		foreach ($item as $subkey => $subitem) {
			$array_two[$subkey][$key] = $subitem;
		}
	}
	return $array_two;
}

function trim_map(array &$map): void {
	$is_row_not_empty = fn(array $row): bool => !empty(array_diff($row, [GROUND]));
	$filter_not_empty_rows = fn(array $map): array => array_values(array_filter($map, $is_row_not_empty));
	$map = $filter_not_empty_rows($map);
	$map = transpose($filter_not_empty_rows(transpose($map)));
}

function print_map(array $map): void {
	echo implode("\n", array_map(fn(array $row): string => implode("", $row), $map)) . "\n";
}

function step(array $position, string $direction): array {
	[$x, $y] = $position;
	return match ($direction) {
		UP => [$x, $y - 1],
		DOWN => [$x, $y + 1],
		LEFT => [$x - 1, $y],
		RIGHT => [$x + 1, $y],
	};
}

function mark(array &$map, array $position): void {
	[$x, $y] = $position;
	$map[$y][$x] = TRENCH;
}

function dig(array &$map, array &$position, string $direction, int $length): void {
	for ($i = 0; $i < $length; ++$i) {
		$position = step($position, $direction);
		mark($map, $position);
	}
}

function fill_from_position(array &$map, array $position): void {
	[$x, $y] = $position;

	if (!isset($map[$y][$x]) || $map[$y][$x] !== GROUND) {
		return;
	}

	$map[$y][$x] = TRENCH;

	foreach (DIRECTIONS as $direction) {
		fill_from_position($map, step($position, $direction));
	}
}

function fill(array &$map): void {
	foreach ($map as $y => $row) {
		$in_trench = false;
		foreach ($row as $x => $cell) {
			$above = $y > 0 ? $map[$y - 1][$x] : GROUND;
			if ($cell === TRENCH) {
				if (!$in_trench) {
					$in_trench = true;
				}
			} else if ($in_trench) {
				if ($above === TRENCH) {
					fill_from_position($map, [$x, $y]);
					return;
				} else {
					$in_trench = false;
				}
			}
		}
	}
}

function count_trenches(array $map): int {
	$is_trench = fn(string $cell): bool => $cell === TRENCH;
	$cells = array_merge(...$map);
	$trenches = array_filter($cells, $is_trench);
	return count($trenches);
}

$map = array_fill(0, MAP_SIZE, array_fill(0, MAP_SIZE, GROUND));
$position = [MAP_SIZE / 2, MAP_SIZE / 2];

dig($map, $position, "R", 1, TRENCH);

$fp = fopen("demo.txt", "r");
while ($line = fgets($fp)) {
	[$direction, $length, $color] = explode(" ", trim($line));
	$color = trim($color, "()");
	$length = intval($length);
	dig($map, $position, $direction, $length);
}

trim_map($map);
fill($map);
print_map($map);

$count = count_trenches($map);
echo "Trenches: {$count}\n";
