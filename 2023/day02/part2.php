<?php

declare(strict_types=1);

$sum = 0;

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	$line = trim($line);
	[$game, $grabs] = explode(": ", $line, 2);
	$game = explode(" ", $game)[1];
	$grabs = explode("; ", $grabs);
	$min_colors = ["red" => 0, "green" => 0, "blue" => 0];
	foreach ($grabs as $grab) {
		$grab = explode(", ", $grab);
		foreach ($grab as $color) {
			[$amount, $color] = explode(" ", $color);
			$min_colors[$color] = max($min_colors[$color], $amount);
		}
	}
	$power = array_reduce($min_colors, fn($carry, $item) => $carry * $item, 1);
	echo "Game {$game}: power {$power}\n";
	$sum += $power;
}

echo "Sum: {$sum}\n";
