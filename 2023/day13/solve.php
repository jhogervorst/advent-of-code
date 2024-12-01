<?php

declare(strict_types=1);

/** @source https://stackoverflow.com/a/47718714/251760 */
function transpose($array_one) {
	$array_two = [];
	foreach ($array_one as $key => $item) {
		foreach ($item as $subkey => $subitem) {
			$array_two[$subkey][$key] = $subitem;
		}
	}
	return $array_two;
}

function is_reflected(array $pattern, int $before_row): bool {
	$before = array_slice($pattern, 0, $before_row);
	$after = array_slice($pattern, $before_row);
	$size = min(count($before), count($after));
	$before = array_slice($before, -$size);
	$after = array_slice($after, 0, $size);
	return $before === array_reverse($after);
}

function find_different_reflection(array $pattern, int $different_than): int {
	for ($y = 1; $y < count($pattern); ++$y) {
		if ($y != $different_than && is_reflected($pattern, $y)) {
			return $y;
		}
	}
	return 0;
}

function find_reflection(array $pattern): int {
	return find_different_reflection($pattern, 0);
}

function unsmudged_pattern(array $pattern): array {
	$horizontal_reflection = find_reflection($pattern);
	$vertical_reflection = find_reflection(transpose($pattern));

	for ($y = 0; $y < count($pattern); ++$y) {
		for ($x = 0; $x < count($pattern[$y]); ++$x) {
			$unsmudged = $pattern;
			$unsmudged[$y][$x] = $unsmudged[$y][$x] == "#" ? "." : "#";

			$unsmudged_horizontal_reflection = find_different_reflection($unsmudged, $horizontal_reflection);
			$unsmudged_vertical_reflection = find_different_reflection(transpose($unsmudged), $vertical_reflection);

			if ($unsmudged_horizontal_reflection || $unsmudged_vertical_reflection) {
				return $unsmudged;
			}
		}
	}
}

$patterns = explode("\n\n", trim(file_get_contents("input.txt")));

$sum1 = 0;
$sum2 = 0;

foreach ($patterns as $pattern) {
	$pattern = array_map("str_split", explode("\n", $pattern));

	$horizontal_reflection = find_reflection($pattern);
	$vertical_reflection = find_reflection(transpose($pattern));
	$sum1 += $vertical_reflection + $horizontal_reflection * 100;

	$unsmudged = unsmudged_pattern($pattern);
	$unsmudged_horizontal_reflection = find_different_reflection($unsmudged, $horizontal_reflection);
	$unsmudged_vertical_reflection = find_different_reflection(transpose($unsmudged), $vertical_reflection);
	$sum2 += $unsmudged_vertical_reflection + $unsmudged_horizontal_reflection * 100;
}

echo "Sum (part 1): {$sum1}\n";
echo "Sum (part 2): {$sum2}\n";
