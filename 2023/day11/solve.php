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

function sorted(array $array): array {
	sort($array);
	return $array;
}

function find_empty_rows(array $image): array {
	$rows = [];
	for ($y = 0; $y < count($image); ++$y) {
		$row = $image[$y];
		$contains_galaxies = !empty(array_filter($row, fn(string $cell) => $cell === '#'));
		if (!$contains_galaxies) {
			$rows[] = $y;
		}
	}
	return $rows;
}

function find_galaxies(array $image): array {
	$galaxies = [];
	foreach ($image as $y => $row) {
		foreach ($row as $x => $cell) {
			if ($cell === '#') {
				$galaxies[] = [$x, $y];
			}
		}
	}
	return $galaxies;
}

function make_pairs(array $galaxies): array {
	$pairs = [];
	foreach ($galaxies as $i => $a) {
		foreach ($galaxies as $j => $b) {
			if ($j >= $i) {
				break;
			}
			$pairs[] = [$a, $b];
		}
	}
	return $pairs;
}

function distance(int $from, int $to, array $empty, int $empty_value): int {
	[$from, $to] = sorted([$from, $to]);
	$sum = 0;
	for ($i = $from; $i < $to; ++$i) {
		$sum += in_array($i, $empty) ? $empty_value : 1;
	}
	return $sum;
}

$image = array_map("str_split", file("input.txt", FILE_IGNORE_NEW_LINES));

$galaxies = find_galaxies($image);
$empty_rows = find_empty_rows($image);
$empty_columns = find_empty_rows(transpose($image));
$pairs = make_pairs($galaxies);

$sum1 = 0;
$sum2 = 0;

foreach ($pairs as $pair) {
	[$a, $b] = $pair;
	$sum1 += distance($a[0], $b[0], $empty_columns, 2) + distance($a[1], $b[1], $empty_rows, 2);
	$sum2 += distance($a[0], $b[0], $empty_columns, 1000000) + distance($a[1], $b[1], $empty_rows, 1000000);
}

echo "Sum (part 1): {$sum1}\n";
echo "Sum (part 2): {$sum2}\n";
