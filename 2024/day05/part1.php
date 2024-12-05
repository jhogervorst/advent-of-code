<?php

declare(strict_types=1);

function is_valid_update(array $pages, array $rules): bool {
	foreach ($pages as $i => $page) {
		foreach ($rules as $rule) {
			if ($rule[1] == $page && array_search($rule[0], $pages) > $i) {
				return false;
			}
		}
	}
	return true;
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
		if (is_valid_update($pages, $rules)) {
			$sum_middle_pages += $pages[(count($pages) - 1) / 2];
		}
	}
}

echo "Sum of middle pages of valid updates: {$sum_middle_pages}\n";
