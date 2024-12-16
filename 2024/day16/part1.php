<?php

declare(strict_types=1);

function turn90(string $direction): string {
	return match ($direction) {
		"N" => "E",
		"E" => "S",
		"S" => "W",
		"W" => "N",
	};
}

function turn180(string $direction): string {
	return turn90(turn90($direction));
}

function turn270(string $direction): string {
	return turn90(turn90(turn90($direction)));
}

function step(array $map, array $position, string $direction): ?array {
	switch ($direction) {
		case "N": $position["y"]--; break;
		case "E": $position["x"]++; break;
		case "S": $position["y"]++; break;
		case "W": $position["x"]--; break;
	}
	return is_valid_position($map, $position) ? $position : null;
}

function is_valid_position(array $map, array $position): bool {
	$cell = $map[$position["y"]][$position["x"]] ?? null;
	return $cell && $cell != "#";
}

function find_position(array $map, string $target): array {
	$y = array_find_key($map, fn ($row) => in_array($target, $row));
	$x = array_find_key($map[$y], fn ($cell) => $cell == $target);
	return ["x" => $x, "y" => $y];
}

function enqueue(SplPriorityQueue $queue, array $visited, array $end_position, array $state, array $change): void {
	$next_state = [...$state, ...$change];
	if (!isset($visited[visited_hash($next_state)])) {
		$expected_score = expected_score($next_state, $end_position);
		$queue->insert($next_state, -$expected_score);
	}
}

function visited_hash(array $state): string {
	return "{$state['position']['x']}.{$state['position']['y']}.{$state['direction']}";
}

function expected_score(array $state, array $end_position): int {
	$dx = abs($end_position["x"] - $state["position"]["x"]);
	$dy = abs($end_position["y"] - $state["position"]["y"]);
	return $state["score"] + $dx + $dy + ($dx ? 1000 : 0) + ($dy ? 1000 : 0);
}

$map = array_map("str_split", explode("\n", trim(file_get_contents($argv[1]))));

$start_position = find_position($map, "S");
$end_position = find_position($map, "E");

$visited = [];
$queue = new SplPriorityQueue();

$queue->insert([
	"score" => 0,
	"position" => $start_position,
	"direction" => "E",
], 0);

while ($state = $queue->extract()) {
	$visited[visited_hash($state)] = true;

	if ($state["position"] == $end_position) {
		echo "Reached end position with score {$state["score"]}\n";
		break;
	}

	if ($next_position = step($map, $state["position"], $state["direction"])) {
		enqueue($queue, $visited, $end_position, $state, [
			"score" => $state["score"] + 1,
			"position" => $next_position,
		]);
	}

	enqueue($queue, $visited, $end_position, $state, [
		"score" => $state["score"] + 1000,
		"direction" => turn90($state["direction"]),
	]);

	enqueue($queue, $visited, $end_position, $state, [
		"score" => $state["score"] + 1000,
		"direction" => turn270($state["direction"]),
	]);

	enqueue($queue, $visited, $end_position, $state, [
		"score" => $state["score"] + 2000,
		"direction" => turn180($state["direction"]),
	]);
}
