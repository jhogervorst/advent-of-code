<?php

declare(strict_types=1);

$left = [];
$right = [];
$distances = [];

$fp = fopen($argv[1], "r");
while ($line = fgets($fp)) {
	$line = trim($line);
	preg_match("/^(\d+)\s+(\d+)$/", $line, $matches);
	$left[] = (int) $matches[1];
	$right[] = (int) $matches[2];
}

sort($left);
sort($right);

for ($i = 0; $i < count($left); $i++) {
	$distances[] = abs($left[$i] - $right[$i]);
}

$total_distance = array_sum($distances);
echo "Total distance: {$total_distance}\n";
