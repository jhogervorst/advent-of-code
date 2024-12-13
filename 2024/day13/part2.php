<?php

declare(strict_types=1);

$regex = '/^Button A: X\+(?<a_x>\d+), Y\+(?<a_y>\d+)\nButton B: X\+(?<b_x>\d+), Y\+(?<b_y>\d+)\nPrize: X=(?<prize_x>\d+), Y=(?<prize_y>\d+)$/m';
preg_match_all($regex, file_get_contents($argv[1]), $matches, PREG_SET_ORDER);

$total_cost = 0;

foreach ($matches as $m) {
	$m["prize_x"] += 10000000000000;
	$m["prize_y"] += 10000000000000;

	$b = ($m["a_y"] * $m["prize_x"] - $m["a_x"] * $m["prize_y"]) / ($m["a_y"] * $m["b_x"] - $m["a_x"] * $m["b_y"]);
	$a = ($m["prize_x"] - $m["b_x"] * $b) / $m["a_x"];

	if (!is_int($a) || !is_int($b)) {
		continue;
	}

	$total_cost += $a * 3 + $b;
}

echo "Total cost: {$total_cost}\n";
