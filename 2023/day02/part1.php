<?php

declare(strict_types=1);

$sum = 0;

$max_colors = [
	"red" => 12,
	"green" => 13,
	"blue" => 14,
];

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	$line = trim($line);
	[$game, $grabs] = explode(": ", $line, 2);
	$game = explode(" ", $game)[1];
	$grabs = explode("; ", $grabs);
	foreach ($grabs as $grab) {
		$grab = explode(", ", $grab);
		foreach ($grab as $color) {
			[$amount, $color] = explode(" ", $color);
			if ($amount > $max_colors[$color]) {
				echo "[Impossible] {$line}\n             {$amount} {$color} cubes in grab is more than maximum of {$max_colors[$color]}\n";
				continue 3;
			}
		}
	}
	echo "[Possible] {$line}\n";
	$sum += $game;
}

echo "Sum: {$sum}\n";
