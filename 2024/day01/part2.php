<?php

declare(strict_types=1);

$left = [];
$right = [];
$similarities = [];

$fp = fopen($argv[1], "r");
while ($line = fgets($fp)) {
	$line = trim($line);
	preg_match("/^(\d+)\s+(\d+)$/", $line, $matches);
	$left[] = (int) $matches[1];
	$right[] = (int) $matches[2];
}

$counts = array_count_values($right);

foreach ($left as $number) {
	$similarities[] = $number * ($counts[$number] ?? 0);
}

$similarity_score = array_sum($similarities);
echo "Similarity score: {$similarity_score}\n";
