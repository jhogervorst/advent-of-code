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

function parse_columns(string $file): array {
	$lines = file($file, FILE_IGNORE_NEW_LINES);
	$lines = array_map("str_split", $lines);
	$columns = transpose($lines);
	return $columns;
}

function print_columns(array $columns, bool $return = false): ?string {
	$rows = transpose($columns);
	$string = implode("\n", array_map("implode", $rows)) . "\n";
	if ($return) {
		return $string;
	} else {
		echo $string;
	}
}

function slide_column(array $column): array {
	$column = implode("", $column);
	$parts = explode("#", $column);
	$parts = array_map(function (string $part): string {
		$part = str_split($part);
		rsort($part, SORT_STRING);
		return implode("", $part);
	}, $parts);
	$column = implode("#", $parts);
	$column = str_split($column);
	return $column;
}

function slide_all_columns(array $columns): array {
	return array_map("slide_column", $columns);
}

function cycle_platform(array $columns): array {
	$columns = slide_all_columns($columns); // north
	$columns = transpose($columns);
	$columns = slide_all_columns($columns); // west
	$columns = transpose(array_reverse($columns));
	$columns = slide_all_columns($columns); // south
	$columns = array_reverse(transpose(array_reverse($columns)));
	$columns = slide_all_columns($columns); // east
	$columns = array_reverse(transpose($columns));
	return $columns;
}

function detect_loop(array $cycles): ?int {
	$last_cycle = $cycles[count($cycles) - 1];
	$occurrences = array_filter($cycles, fn (string $cycle): bool => $cycle == $last_cycle);
	if (count($occurrences) < 3) {
		return null;
	}
	$indexes = array_keys($occurrences);
	$last_index = $indexes[count($indexes) - 1];
	$second_last_index = $indexes[count($indexes) - 2];
	$third_last_index = $indexes[count($indexes) - 3];
	$last_loop_length = $last_index - $second_last_index;
	$second_last_loop_length = $second_last_index - $third_last_index;
	if ($last_loop_length != $second_last_loop_length) {
		return null;
	}
	$last_loop = array_slice($cycles, $second_last_index, $last_loop_length + 1);
	$second_last_loop = array_slice($cycles, $third_last_index, $last_loop_length + 1);
	if ($last_loop != $second_last_loop) {
		return null;
	}
	return $last_loop_length;
}

function hash_columns(array $columns): string {
	return md5(print_columns($columns, true));
}

function cycle_loop(array $columns, int $times): array {
	$cycles = [hash_columns($columns)];
	for ($i = 0; $i < $times; ++$i) {
		$columns = cycle_platform($columns);
		$cycles[] = hash_columns($columns);
		if ($loop = detect_loop($cycles)) {
			while ($i + $loop < $times) {
				$i += $loop;
			}
		}
	}
	return $columns;
}

function column_load(array $column): int {
	$sum = 0;
	foreach ($column as $i => $cell) {
		if ($cell == "O") {
			$weight = count($column) - $i;
			$sum += $weight;
		}
	}
	return $sum;
}

function total_load(array $columns): int {
	return array_sum(array_map("column_load", $columns));
}

$columns = parse_columns("input.txt");

$columns = slide_all_columns($columns);
echo "Total load (part 1): " . total_load($columns) . "\n";

$columns = cycle_loop($columns, 1000000000);
echo "Total load (part 2): " . total_load($columns) . "\n";
