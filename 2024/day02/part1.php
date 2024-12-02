<?php

declare(strict_types=1);

$count_safe = 0;
$fp = fopen($argv[1], "r");

while ($line = fgets($fp)) {
	$line = trim($line);
	$report = explode(" ", $line);
	$report = array_map("intval", $report);

	$last_direction = null;

	for ($i = 1; $i < count($report); $i++) {
		$a = $report[$i - 1];
		$b = $report[$i];
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
}

echo "Safe report count: {$count_safe}\n";
