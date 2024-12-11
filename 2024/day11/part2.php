<?php

declare(strict_types=1);

function blink(int $stone, int $times = 75): int {
	static $cache = [];

	if ($times == 0) {
		return 1;
	}

	if (!isset($cache[$times][$stone])) {
		$length = strlen((string) $stone);

		if ($stone == 0) {
			$count_stones = blink(1, $times - 1);
		} elseif ($length % 2 == 0) {
			$left = intval(substr((string) $stone, 0, $length / 2));
			$right = intval(substr((string) $stone, $length / 2));
			$count_stones = blink($left, $times - 1) + blink($right, $times - 1);
		} else {
			$count_stones = blink($stone * 2024, $times - 1);
		}

		$cache[$times][$stone] = $count_stones;
	}

	return $cache[$times][$stone];
}

$stones = array_map("intval", explode(" ", trim(file_get_contents($argv[1]))));
$count_stones = array_sum(array_map("blink", $stones));

echo "Number of stones after 75 blinks: {$count_stones}\n";
