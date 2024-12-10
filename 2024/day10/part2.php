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

function is_inside(array $map, array $position): bool {
	return in_array($position["y"], array_keys($map)) && in_array($position["x"], array_keys($map[0]));
}

function trailheads(array $map, array $position): array {
	$trailheads = [];
	$cell = $map[$position["y"]][$position["x"]];

	if ($cell == 9) {
		return [$position];
	}

	foreach (["N", "E", "S", "W"] as $direction) {
		$next_position = step($position, $direction);

		if (!is_inside($map, $next_position)) {
			continue;
		}

		$next_cell = $map[$next_position["y"]][$next_position["x"]];

		if ($next_cell != $cell + 1) {
			continue;
		}

		$trailheads = [...$trailheads, ...trailheads($map, $next_position)];
	}

	return $trailheads;
}

$map = array_map("str_split", explode("\n", trim(file_get_contents($argv[1]))));
$sum_ratings = 0;

foreach ($map as $y => $row) {
	foreach ($row as $x => $cell) {
		if ($cell != 0) {
			continue;
		}

		$trailheads = trailheads($map, ["x" => $x, "y" => $y]);
		$sum_ratings += count($trailheads);
	}
}

echo "Sum of trailhead ratings: {$sum_ratings}\n";
