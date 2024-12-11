<?php

declare(strict_types=1);

$stones = array_map("intval", explode(" ", trim(file_get_contents($argv[1]))));

for ($blink = 0; $blink < 25; $blink++) {
	for ($i = 0; $i < count($stones); $i++) {
		$stone = $stones[$i];
		$length = strlen((string) $stone);

		if ($stone == 0) {
			$stones[$i] = 1;
		} elseif ($length % 2 == 0) {
			$stones = [
				...array_slice($stones, 0, $i),
				intval(substr((string) $stone, 0, $length / 2)),
				intval(substr((string) $stone, $length / 2)),
				...array_slice($stones, $i + 1),
			];
			$i++;
		} else {
			$stones[$i] = $stone * 2024;
		}
	}

	echo "{$blink}\n";
}

$count_stones = count($stones);
echo "Number of stones after 25 blinks: {$count_stones}\n";
