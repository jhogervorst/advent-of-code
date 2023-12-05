<?php

declare(strict_types=1);

$cards = [];

$fp = fopen("input.txt", "r");
while ($line = trim(fgets($fp) ?: "")) {
	[$number, $numbers] = explode(":", $line);
	[$winning, $have] = explode("|", $numbers);

	$number = (int) trim(explode(" ", $number, 2)[1]);
	$winning = array_filter(explode(" ", $winning));
	$have = array_filter(explode(" ", $have));

	$winning_haves = array_intersect($have, $winning);

	$cards[$number] = [
		"number" => $number,
		"count" => 1,
		"winning" => $winning,
		"have" => $have,
		"winning_haves" => $winning_haves,
	];
}

foreach (array_keys($cards) as $n) {
	$card = $cards[$n];
	for ($i = 1; $i <= count($card["winning_haves"]); ++$i) {
		$won_card = &$cards[$n + $i];
		$won_card["count"] += $card["count"];
	}
}

$count = array_sum(array_column($cards, "count"));
echo "Count: {$count}\n";
