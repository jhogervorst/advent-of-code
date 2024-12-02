<?php

declare(strict_types=1);

$count_safe = 0;
$fp = fopen($argv[1], "r");

while ($line = fgets($fp)) {
	$line = trim($line);
	$report = explode(" ", $line);
	$report = array_map("intval", $report);

	for ($r = -1; $r < count($report); $r++) {
		$levels = $report;
		$last_direction = null;

		if ($r !== -1) {
			unset($levels[$r]);
			$levels = array_values($levels);
		}

		for ($i = 1; $i < count($levels); $i++) {
			$a = $levels[$i - 1];
			$b = $levels[$i];
			$direction = $a <= $b ? "+" : "-";
			$diff = abs($b - $a);

			if ($last_direction && $last_direction !== $direction) {
				continue 2;
			}

			if ($diff < 1 || $diff > 3) {
				continue 2;
			}

			$last_direction = $direction;
		}

		$count_safe++;
		continue 2;
	}
}

echo "Safe report count: {$count_safe}\n";
