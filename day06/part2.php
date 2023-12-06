<?php

declare(strict_types=1);

$fp = fopen("input.txt", "r");
$game_time = intval(preg_replace("/[^\d]/", "", fgets($fp)));
$game_distance = intval(preg_replace("/[^\d]/", "", fgets($fp)));

$options = 0;
for ($hold_time = 0; $hold_time <= $game_time; ++$hold_time) {
	$speed = $hold_time;
	$move_time = $game_time - $hold_time;
	$distance = $speed * $move_time;
	if ($distance > $game_distance) {
		++$options;
	}
}

echo "Options: {$options}\n";
