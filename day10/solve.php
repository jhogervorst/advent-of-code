<?php

declare(strict_types=1);

define("NORTH", 1);
define("EAST", 2);
define("SOUTH", 4);
define("WEST", 8);
define("DIRECTIONS", [NORTH, EAST, SOUTH, WEST]);
define("OPPOSITE", [NORTH => SOUTH, EAST => WEST, SOUTH => NORTH, WEST => EAST]);
define("DEGREES_90", [NORTH => EAST, EAST => SOUTH, SOUTH => WEST, WEST => NORTH]);

define("PIPES", [
	"|" => NORTH | SOUTH,
	"-" => EAST | WEST,
	"L" => NORTH | EAST,
	"J" => NORTH | WEST,
	"7" => SOUTH | WEST,
	"F" => SOUTH | EAST,
	"." => 0,
	"S" => NORTH | EAST | SOUTH | WEST,
]);

function start_pos(array $tiles): array {
	for ($y = 0; $y < count($tiles); ++$y) {
		for ($x = 0 ; $x < count($tiles[$y]); ++$x) {
			if ($tiles[$y][$x] == "S") {
				return [$y, $x];
			}
		}
	}
	throw new Exception("No start position found");
}

function step_pos(array $pos, int $dir): array {
	switch ($dir) {
		case NORTH: return [$pos[0] - 1, $pos[1]];
		case EAST: return [$pos[0], $pos[1] + 1];
		case SOUTH: return [$pos[0] + 1, $pos[1]];
		case WEST: return [$pos[0], $pos[1] - 1];
	};
	throw new Exception("Invalid direction");
}

function valid_steps(array $tiles, array $pos): array {
	$valid_steps = [];
	$pipe = $tiles[$pos[0]][$pos[1]];
	$valid_dirs = PIPES[$pipe];
	foreach (DIRECTIONS as $dir) {
		if ($valid_dirs & $dir) {
			$next_pos = step_pos($pos, $dir);
			if (isset($tiles[$next_pos[0]][$next_pos[1]])) {
				$next_pipe = $tiles[$next_pos[0]][$next_pos[1]];
				if (PIPES[$next_pipe] & OPPOSITE[$dir]) {
					$valid_steps[] = ["dir" => $dir, "pos" => $next_pos];
				}
			}
		}
	}
	return $valid_steps;
}

function right_sides(array $out, array $pos, int $dir): array {
	$positions = [step_pos($pos, DEGREES_90[$dir]), step_pos(step_pos($pos, DEGREES_90[$dir]), $dir)];
	$right_sides = array_filter($positions, fn(array $pos) => isset($out[$pos[0]][$pos[1]]) && $out[$pos[0]][$pos[1]] == " ");
	return [...$right_sides, ...array_merge(...array_map(fn(array $pos) => right_sides($out, $pos, $dir), $right_sides))];
}

function fill_right_sides(array &$out, array $right_sides): void {
	foreach ($right_sides as $pos) {
		$out[$pos[0]][$pos[1]] = "R";
	}
}

$tiles = array_map("str_split", file("input.txt", FILE_IGNORE_NEW_LINES));
$out = array_fill(0, count($tiles), array_fill(0, count($tiles[0]), " "));

$start = start_pos($tiles);
$cur = ["dir" => 0, "pos" => $start];
$prev = ["dir" => 0, "pos" => []];
$steps = [];

do {
	$next = valid_steps($tiles, $cur["pos"]);
	$next = array_filter($next, fn(array $step) => $step["pos"] != $prev["pos"]);
	$next = array_values($next)[0];

	$out[$cur["pos"][0]][$cur["pos"][1]] = "•";

	$steps[] = $next;
	$prev = $cur;
	$cur = $next;
} while ($cur["pos"] != $start);

for ($i = 1; $i < count($steps); ++$i) {
	$cur = $steps[$i - 1];
	$next = $steps[$i];

	$right_sides = right_sides($out, $cur["pos"], $next["dir"]);
	foreach ($right_sides as $pos) {
		$out[$pos[0]][$pos[1]] = "R";
	}
}

$total = count($steps);
$half = $total / 2;
echo "Total steps to start: {$total}\n";
echo "Steps to farthest position (halfway --> part 1): {$half}\n";

$right_sides = count(array_filter(array_merge(...$out), fn(string $c) => $c == "R"));
echo "Right sides (enclosed? --> part 2): {$right_sides}\n";
