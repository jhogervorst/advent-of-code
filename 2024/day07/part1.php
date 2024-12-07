<?php

declare(strict_types=1);

function solve(array $numbers, ?int $carry = null): array {
	if (count($numbers) == 0) {
		return [$carry];
	}

	$item = array_shift($numbers);

	return [
		...solve($numbers, ($carry ?? 0) + $item),
		...solve($numbers, ($carry ?? 1) * $item),
	];
}

$sum = 0;

$fp = fopen($argv[1], "r");
while ($line = trim(fgets($fp) ?: "")) {
	[$outcome, $numbers] = explode(": ", $line);
	$numbers = explode(" ", $numbers);
	$values = solve($numbers);
	if (in_array($outcome, $values)) {
		$sum += $outcome;
	}
}

echo "Sum of outcomes: {$sum}\n";
