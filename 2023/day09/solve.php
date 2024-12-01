<?php

declare(strict_types=1);

function first(array $array): mixed
{
	return $array[array_key_first($array)];
}

function last(array $array): mixed
{
	return $array[array_key_last($array)];
}

function all_zeroes(array $array): bool
{
	return empty(array_diff($array, [0]));
}

function sequence_diff(array $sequence): array
{
	$diff = [];
	for ($i = 1; $i < count($sequence); ++$i) {
		$diff[] = $sequence[$i] - $sequence[$i - 1];
	}
	return $diff;
}

function extrapolate(array $sequences): int
{
	return array_sum(array_map("last", $sequences));
}

function extrapolate_back(array $sequences): int
{
	return array_reduce(
		array_reverse(array_map("first", $sequences)),
		fn(int $carry, int $item) => $item - $carry,
		0,
	);
}

$total = $total_back = 0;

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	$sequences = [array_map("intval", explode(" ", trim($line)))];
	while (!all_zeroes(last($sequences))) {
		$sequences[] = sequence_diff(last($sequences));
	}
	$total += extrapolate($sequences);
	$total_back += extrapolate_back($sequences);
}

echo "Total (part 1): {$total}\n";
echo "Total (part 2): {$total_back}\n";
