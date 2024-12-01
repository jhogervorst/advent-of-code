<?php

declare(strict_types=1);

function is_number (string $char): bool { return $char >= "0" && $char <= "9"; }
function is_gear (string $char): bool { return $char == "*"; }

$sum = 0;
$gears = [];

$lines = file("input.txt", FILE_IGNORE_NEW_LINES);
$lines = [
	str_repeat(".", 1 + strlen($lines[0]) + 1),
	...array_map(fn(string $line) => ".{$line}.", $lines),
	str_repeat(".", 1 + strlen($lines[0]) + 1),
];

for ($y = 1; $y < count($lines) - 1; ++$y) {
	$number_start = $number_end = null;

	for ($x = 1; $x < strlen($lines[$y]) - 1; ++$x) {
		$cur_char = $lines[$y][$x];
		$next_char = $lines[$y][$x + 1];

		if (is_null($number_start) && is_number($cur_char)) {
			$number_start = $x;
		} elseif (!is_null($number_start) && !is_number($next_char)) {
			$number_end = $x;
			$number = (int) substr($lines[$y], $number_start, $number_end - $number_start + 1);

			for ($yy = $y - 1; $yy <= $y + 1; ++$yy) {
				for ($xx = $number_start - 1; $xx <= $number_end + 1; ++$xx) {
					if (is_gear($lines[$yy][$xx])) {
						$gears[$yy][$xx][] = $number;
					}
				}
			}

			$number_start = $number_end = null;
		}
	}
}

foreach (array_merge(...$gears) as $numbers) {
	if (count($numbers) == 2) {
		$ratio = $numbers[0] * $numbers[1];
		$sum += $ratio;
	}
}

echo "Sum: {$sum}\n";
