<?php

declare(strict_types=1);

function find_violation(array $pages, array $rules): ?array {
	foreach ($pages as $i => $page) {
		foreach ($rules as $rule) {
			if ($rule[1] == $page && ($j = array_search($rule[0], $pages)) > $i) {
				return ['i' => $i, 'j' => $j, 'rule' => $rule];
			}
		}
	}
	return null;
}

function is_valid_update(array $pages, array $rules): bool {
	return !find_violation($pages, $rules);
}

function fix_update(array $pages, array $rules): array {
	while ($violation = find_violation($pages, $rules)) {
		$pages[$violation['i']] = $violation['rule'][0];
		$pages[$violation['j']] = $violation['rule'][1];
	}
	return $pages;
}

$rules = [];
$sum_middle_pages = 0;

$fp = fopen($argv[1], "r");
while ($line = fgets($fp)) {
	$line = trim($line);

	if (str_contains($line, "|")) {
		$rules[] = explode("|", $line);
	} elseif (str_contains($line, ",")) {
		$pages = explode(",", $line);
		if (!is_valid_update($pages, $rules)) {
			$pages = fix_update($pages, $rules);
			$sum_middle_pages += $pages[(count($pages) - 1) / 2];
		}
	}
}

echo "Sum of middle pages of fixed invalid updates: {$sum_middle_pages}\n";
