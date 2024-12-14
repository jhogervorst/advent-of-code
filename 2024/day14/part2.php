<?php

declare(strict_types=1);

$width = 101;
$height = 103;

$robots = [];

$fp = fopen($argv[1], "r");
while ($line = trim(fgets($fp) ?: "")) {
	preg_match("/^p=(?<px>\d+),(?<py>\d+) v=(?<vx>-?\d+),(?<vy>-?\d+)$/", $line, $matches);
	$robots[] = [
		"position" => ["x" => (int) $matches["px"], "y" => (int) $matches["py"]],
		"velocity" => ["x" => (int) $matches["vx"], "y" => (int) $matches["vy"]],
	];
}

for ($steps = 1; true; $steps++) {
	$image = array_fill(0, $height, array_fill(0, $width, " "));

	foreach ($robots as &$robot) {
		$robot["position"]["x"] = ($robot["position"]["x"] + $robot["velocity"]["x"] % $width + $width) % $width;
		$robot["position"]["y"] = ($robot["position"]["y"] + $robot["velocity"]["y"] % $height + $height) % $height;
		$image[$robot["position"]["y"]][$robot["position"]["x"]] = "#";
	}

	$image = implode("\n", array_map(fn ($row) => implode("", $row), $image));

	if (str_contains($image, "##########")) {
		echo "Arrangement after {$steps} steps:\n\n{$image}\n";
		break;
	}
}
