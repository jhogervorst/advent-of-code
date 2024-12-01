<?php

declare(strict_types=1);

ini_set("memory_limit", "1G");

function damaged_groups(string $arrangement): array {
	$arrangement = str_replace("?", ".", $arrangement);
	$groups = array_filter(explode(".", $arrangement));
	$groups = array_map("strlen", $groups);
	return array_values($groups);
}

function is_valid(string $arrangement, array $damaged_groups): bool {
	return damaged_groups($arrangement) == $damaged_groups;
}

function can_be_valid(string $pattern, array $damaged_groups): bool {
	$number_damaged = substr_count($pattern, "#");
	$sum_damaged_groups = array_sum($damaged_groups);
	if ($number_damaged > $sum_damaged_groups) {
		return false;
	}

	$pattern_with_all_unknowns_damaged = str_replace("?", "#", $pattern);
	$number_unknowns_and_damaged = substr_count($pattern_with_all_unknowns_damaged, "#");
	if ($number_unknowns_and_damaged < $sum_damaged_groups) {
		return false;
	}

	$max_damaged_group = !empty($damaged_groups) ? max($damaged_groups) : 0;
	$pattern_damaged_groups = damaged_groups($pattern);
	if (!empty($pattern_damaged_groups) && max($pattern_damaged_groups) > $max_damaged_group) {
		return false;
	}

	if (max(damaged_groups($pattern_with_all_unknowns_damaged)) < $max_damaged_group) {
		return false;
	}

	$known_damaged_groups = damaged_groups(known_arrangement($pattern));
	if ($known_damaged_groups != array_slice($damaged_groups, 0, count($known_damaged_groups))) {
		return false;
	}

	return true;
}

function known_arrangement(string $pattern): string {
	return rtrim(explode("?", $pattern, 2)[0], "#");
}

function valid_arrangements(string $pattern, array $damaged_groups): int {
	if (str_contains($pattern, "?")) {
		if (can_be_valid($pattern, $damaged_groups)) {
			$known_arrangement = known_arrangement($pattern);
			$remaining_pattern = substr($pattern, strlen($known_arrangement));
			$unknown_pos = strpos($remaining_pattern, "?");

			$known_damaged_groups = damaged_groups($known_arrangement);
			$remaining_damaged_groups = array_slice($damaged_groups, count($known_damaged_groups));

			return cached_valid_arrangements(substr_replace($remaining_pattern, ".", $unknown_pos, 1), $remaining_damaged_groups)
				+ cached_valid_arrangements(substr_replace($remaining_pattern, "#", $unknown_pos, 1), $remaining_damaged_groups);
		}
	} elseif (is_valid($pattern, $damaged_groups)) {
		return 1;
	}

	return 0;
}

function cached_valid_arrangements(string $pattern, array $damaged_groups): int {
	static $cache = [];
	$key = implode(",", [$pattern, ...$damaged_groups]);
	if (!isset($cache[$key])) {
		$cache[$key] = valid_arrangements($pattern, $damaged_groups);
	}
	return $cache[$key];
}

function count_valid_arrangements(string $line): int {
	[$pattern, $damaged_groups] = explode(" ", $line);
	$damaged_groups = array_map("intval", explode(",", $damaged_groups));
	return cached_valid_arrangements($pattern, $damaged_groups);
}

function increase(string $line): string {
	[$pattern, $damaged_groups] = explode(" ", $line);
	$pattern = implode("?", array_fill(0, 5, $pattern));
	$damaged_groups = implode(",", array_fill(0, 5, $damaged_groups));
	$line = implode(" ", [$pattern, $damaged_groups]);
	return $line;
}

$sum1 = 0;
$sum2 = 0;

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	$line = trim($line);
	$sum1 += count_valid_arrangements($line);
	$sum2 += count_valid_arrangements(increase($line));
}

echo "Sum (part 1): {$sum1}\n";
echo "Sum (part 2): {$sum2}\n";
