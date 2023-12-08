<?php

$instructions = $map = [];

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	if (empty($instructions)) {
		$instructions = str_split(trim($line));
	} else if (preg_match("/^([A-Z]+) = \(([A-Z]+), ([A-Z]+)\)/", $line, $matches)) {
		$map[$matches[1]] = ["L" => $matches[2], "R" => $matches[3]];
	}
}

$position = "AAA";
for ($steps = 0; $position != "ZZZ"; ++$steps) {
	$instruction = $instructions[$steps % count($instructions)];
	$position = $map[$position][$instruction];
}
echo "Steps: {$steps}\n";
