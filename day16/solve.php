<?php

declare(strict_types=1);

define("NORTH", 1);
define("EAST", 2);
define("SOUTH", 4);
define("WEST", 8);

function parse_tiles(string $file): array {
	return array_map("str_split", file($file, FILE_IGNORE_NEW_LINES));
}

function print_tiles(array $tiles): void {
	echo implode("\n", array_map("implode", $tiles)) . "\n";
}

function beam_directions(string $tile, int $direction): array {
	return match ($tile) {
		"." => [$direction],
		"/" => match ($direction) {
			NORTH => [EAST],
			EAST => [NORTH],
			SOUTH => [WEST],
			WEST => [SOUTH],
		},
		"\\" => match ($direction) {
			NORTH => [WEST],
			EAST => [SOUTH],
			SOUTH => [EAST],
			WEST => [NORTH],
		},
		"|" => in_array($direction, [EAST, WEST]) ? [NORTH, SOUTH] : [$direction],
		"-" => in_array($direction, [NORTH, SOUTH]) ? [EAST, WEST] : [$direction],
	};
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

function beam_exists(array $beams, array $position, int $direction): bool {
	return !!(get_position($beams, $position) & $direction);
}

function get_position(array $array, array $position): mixed {
	[$x, $y] = $position;
	return $array[$y][$x];
}

function position_exists(array $array, array $position): bool {
	[$x, $y] = $position;
	return isset($array[$y][$x]);
}

function beam(array &$tiles, array &$beams, array $position, int $direction) {
	[$x, $y] = $position;
	$beams[$y][$x] |= $direction;

	$tile = get_position($tiles, $position);
	$directions = beam_directions($tile, $direction);

	foreach ($directions as $direction) {
		$new_position = step_position($position, $direction);
		if (position_exists($tiles, $new_position) && !beam_exists($beams, $new_position, $direction)) {
			beam($tiles, $beams, step_position($position, $direction), $direction);
		}
	}
}

function count_beams(array $beams): int {
	return count(array_filter(array_merge(...$beams)));
}

function beams_for_start(array $tiles, array $position, int $direction) {
	$beams = array_fill(0, count($tiles), array_fill(0, count($tiles[0]), 0));
	beam($tiles, $beams, $position, $direction);
	return count_beams($beams);
}

function start_options(array $tiles): array {
	$y_options = array_keys($tiles);
	$x_options = array_keys($tiles[0]);
	$max_y = max($y_options);
	$max_x = max($x_options);

	return [
		...array_map(fn(int $x): array => ["position" => [$x, 0], "direction" => SOUTH], $x_options), // top
		...array_map(fn(int $x): array => ["position" => [$x, $max_y], "direction" => NORTH], $x_options), // bottom
		...array_map(fn(int $y): array => ["position" => [0, $y], "direction" => EAST], $y_options), // left
		...array_map(fn(int $y): array => ["position" => [$max_x, $y], "direction" => WEST], $y_options), // RIGHT
	];
}

function most_beams(array $tiles) {
	$max_beams = 0;
	$start_options = start_options($tiles);
	foreach ($start_options as $start) {
		$beams = beams_for_start($tiles, $start["position"], $start["direction"]);
		$max_beams = max($max_beams, $beams);
	}
	return $max_beams;
}

$tiles = parse_tiles("input.txt");

$energized = beams_for_start($tiles, [0, 0], EAST);
echo "Energized for start from top left (part 1): {$energized}\n";

$most_energized = most_beams($tiles);
echo "Energized with optimal start (part 2): {$most_energized}\n";
