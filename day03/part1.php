<?php

declare(strict_types=1);

function is_number (string $char): bool { return $char >= "0" && $char <= "9"; }
function is_symbol (string $char): bool { return !is_number($char) && $char != "."; }

$sum = 0;

$max_colors = [
	"red" => 12,
	"green" => 13,
	"blue" => 14,
];

$lines = file("input.txt", FILE_IGNORE_NEW_LINES);
$lines = [
	str_repeat(".", 1 + strlen($lines[0]) + 1),
	...array_map(fn (string $line) => ".{$line}.", $lines),
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
					if (is_symbol($lines[$yy][$xx])) {
						$sum += $number;
						break 2;
					}
				}
			}

			$number_start = $number_end = null;
		}
	}
}

echo "Sum: {$sum}\n";
