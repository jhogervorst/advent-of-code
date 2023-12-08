<?php

function at_start(string $position): bool {
	return preg_match("/A$/", $position);
}

function at_destination(string $position): bool {
	return preg_match("/Z$/", $position);
}

function all_at_destination(array $positions): bool {
	return array_filter($positions, "at_destination") == $positions;
}

$instructions = $map = [];

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	if (empty($instructions)) {
		$instructions = str_split(trim($line));
	} else if (preg_match("/^([A-Z0-9]+) = \(([A-Z0-9]+), ([A-Z0-9]+)\)/", $line, $matches)) {
		$map[$matches[1]] = ["L" => $matches[2], "R" => $matches[3]];
	}
}

$positions = array_filter(array_keys($map), "at_start");
$steps_to_destination = [];

for ($i = 0; count($steps_to_destination) < count($positions); ++$i) {
	$instruction = $instructions[$i % count($instructions)];
	$positions = array_map(fn (string $position): string => $map[$position][$instruction], $positions);
	foreach ($positions as $j => $position) {
		if (at_destination($position) && !array_key_exists($j, $steps_to_destination)) {
			$steps_to_destination[$j] = $i + 1;
		}
	}
}

$steps = array_reduce($steps_to_destination, "gmp_lcm", 1);
echo "Steps: {$steps}\n";
