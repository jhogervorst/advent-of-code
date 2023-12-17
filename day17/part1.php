<?php

declare(strict_types=1);

define("NORTH", 1);
define("EAST", 2);
define("SOUTH", 4);
define("WEST", 8);
define("DIRECTIONS", [NORTH, EAST, SOUTH, WEST]);
define("OPPOSITE", [NORTH => SOUTH, EAST => WEST, SOUTH => NORTH, WEST => EAST]);

define("MAX_SAME_STEPS", 3);

// Array

function last(array $array, int $count = 1): mixed
{
	$last = array_slice($array, -$count);
	return $count === 1 ? (!empty($last) ? $last[0] : null) : $last;
}

// Map

function parse_map(string $file): array {
	$parse_line = fn (string $line): array => array_map("intval", str_split($line));
	return array_map($parse_line, file($file, FILE_IGNORE_NEW_LINES));
}

function print_map(array $map): void {
	echo implode("\n", array_map("implode", $map)) . "\n\n";
}

// Map positions

function position_exists(array $map, array $position): bool {
	[$x, $y] = $position;
	return isset($map[$y][$x]);
}

function at_position(array $map, array $position): int {
	[$x, $y] = $position;
	return $map[$y][$x];
}

function set_position(array &$map, array $position, mixed $value): void {
	[$x, $y] = $position;
	$map[$y][$x] = $value;
}

function mark_position(array $map, array $position): array {
	set_position($map, $position, "X");
	return $map;
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

// Steps

function invalid_steps(array $prev_steps): int {
	$invalid = 0;

	if ($last = last($prev_steps)) {
		$invalid |= OPPOSITE[$last];
	}

	if (
		($last = last($prev_steps, MAX_SAME_STEPS))
		&& count($last) === MAX_SAME_STEPS
		&& count(array_unique($last)) === 1
	) {
		$invalid |= $last[0];
	}

	return $invalid;
}

function valid_steps_for_position(array $map, array $position, array $prev_steps): array {
	$steps = [];
	$invalid = invalid_steps($prev_steps);

	foreach (DIRECTIONS as $direction) {
		if ($direction & $invalid) {
			continue;
		}

		$step_position = step_position($position, $direction);
		if (!position_exists($map, $step_position)) {
			continue;
		}

		$steps[] = [
			"step" => $direction,
			"position" => $step_position,
		];
	}

	return $steps;
}

// Queue

function enqueue(array &$queue, array $state): void {
	$rank = $state["rank"];
	$queue[$rank][] = $state;

	if (!isset($queue["low"]) || $rank < $queue["low"]) {
		$queue["low"] = $rank;
	}
}

function dequeue(array &$queue): ?array {
	$low = $queue["low"];
	$state = array_pop($queue[$low]);

	if (!$state) {
		unset($queue[$low]);
		$ranks = array_diff(array_keys($queue), ["low"]);
		if (!empty($ranks)) {
			$queue["low"] = min($ranks);
			$state = dequeue($queue);
		}
	}

	return $state;
}

// BFS

function bfs(array $map): array {
	$start = [0, 0];
	$end = [count($map[0]) - 1, count($map) - 1];

	$queue = [];
	$visited = [];
	$best = null;

	enqueue($queue, [
		"position" => $start,
		"positions_map" => mark_position([], $start),
		"steps" => [],
		"score" => 0,
		"rank" => 0,
	]);

	while ($state = dequeue($queue)) {
		if ($state["position"] === $end) {
			if (!$best || $state["score"] < $best["score"]) {
				$best = $state;
			}
			continue;
		}

		if ($best && $state["score"] >= $best["score"]) {
			continue;
		}

		$next_steps = valid_steps_for_position($map, $state["position"], $state["steps"]);

		foreach ($next_steps as $next) {
			// Skip if position was already visited in the current path
			if (position_exists($state["positions_map"], $next["position"])) {
				continue;
			}

			$next_score = $state["score"] + at_position($map, $next["position"]);
			$next_steps = [...array_slice($state["steps"], 1 - MAX_SAME_STEPS), $next["step"]];

			// Skip if position (with same last steps) was already visited in a shorter path
			[$next_x, $next_y] = $next["position"];
			$steps_key = intval(implode("", $next_steps));
			if (isset($visited[$next_y][$next_x][$steps_key]) && $visited[$next_y][$next_x][$steps_key] <= $next_score) {
				continue;
			}
			$visited[$next_y][$next_x][$steps_key] = $next_score;

			$min_remaining_steps = ($end[0] - $next["position"][0]) + ($end[1] - $next["position"][1]);

			enqueue($queue, [
				"position" => $next["position"],
				"positions_map" => mark_position($state["positions_map"], $next["position"]),
				"steps" => $next_steps,
				"score" => $next_score,
				"rank" => $next_score + $min_remaining_steps,
			]);
		}
	}

	return $best ?? [];
}

$map = parse_map("input.txt");
$best = bfs($map);
echo "Heat loss: {$best['score']}\n";
