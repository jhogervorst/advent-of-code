<?php

declare(strict_types=1);

$sum = 0;

$fp = fopen("input.txt", "r");
while ($line = trim(fgets($fp) ?: "")) {
	$numbers = explode(":", $line)[1];
	[$winning, $have] = explode("|", $numbers);
	$winning = array_filter(explode(" ", $winning));
	$have = array_filter(explode(" ", $have));
	$winning_haves = array_intersect($have, $winning);
	if (!empty($winning_haves)) {
		$points = pow(2, count($winning_haves) - 1);
		$sum += $points;
	}
}

echo "Sum: {$sum}\n";
