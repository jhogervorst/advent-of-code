<?php

declare(strict_types=1);

$width = str_contains($argv[1], "demo") ? 11 : 101;
$height = str_contains($argv[1], "demo") ? 7 : 103;
$steps = 100;

$quadrants = ["TL" => 0, "TR" => 0, "BL" => 0, "BR" => 0];

$fp = fopen($argv[1], "r");
while ($line = trim(fgets($fp) ?: "")) {
	preg_match("/^p=(?<px>\d+),(?<py>\d+) v=(?<vx>-?\d+),(?<vy>-?\d+)$/", $line, $matches);
	$start = ["x" => (int) $matches["px"], "y" => (int) $matches["py"]];
	$velocity = ["x" => (int) $matches["vx"], "y" => (int) $matches["vy"]];
	$end = [
		"x" => ($start["x"] + $velocity["x"] * $steps % $width + $width) % $width,
		"y" => ($start["y"] + $velocity["y"] * $steps % $height + $height) % $height,
	];
	$qh = $end["x"] + 1 < $width / 2 ? "L" : ($end["x"] > $width / 2 ? "R" : null);
	$qv = $end["y"] + 1 < $height / 2 ? "T" : ($end["y"] > $height / 2 ? "B" : null);
	if ($qh && $qv) $quadrants["{$qv}{$qh}"]++;
}

$safety_factor = array_reduce($quadrants, fn ($carry, $item) => $carry * $item, 1);
echo "Safety factor: {$safety_factor}\n";
