<?php

declare(strict_types=1);

$fp = fopen("input.txt", "r");
$times = array_values(array_filter(array_map("intval", explode(" ", fgets($fp)))));
$distances = array_values(array_filter(array_map("intval", explode(" ", fgets($fp)))));
$games = array_map(fn ($i) => ["time" => $times[$i], "distance" => $distances[$i]], array_keys($times));

$total_options = 1;
foreach ($games as $game) {
	$game_options = 0;
	for ($hold_time = 0; $hold_time <= $game["time"]; ++$hold_time) {
		$speed = $hold_time;
		$move_time = $game["time"] - $hold_time;
		$distance = $speed * $move_time;
		if ($distance > $game["distance"]) {
			++$game_options;
		}
	}
	$total_options *= $game_options;
}

echo "Total options: {$total_options}\n";
