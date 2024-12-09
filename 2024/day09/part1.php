<?php

declare(strict_types=1);

$disk_map = trim(file_get_contents($argv[1]));
$blocks = [];

for ($i = 0; $i * 2 < strlen($disk_map); $i++) {
	$file_length = $disk_map[$i * 2];
	$free_length = $disk_map[$i * 2 + 1] ?? 0;

	for ($j = 0; $j < $file_length; $j++) {
		$blocks[] = $i;
	}

	for ($j = 0; $j < $free_length; $j++) {
		$blocks[] = ".";
	}
}

$left = 0;
$right = count($blocks) - 1;

while ($left < $right) {
	$left_block = $blocks[$left];
	$right_block = $blocks[$right];

	if ($left_block != ".") {
		$left++;
		continue;
	}

	if ($right_block == ".") {
		$right--;
		continue;
	}

	$blocks[$left] = $right_block;
	$blocks[$right] = $left_block;

	$left++;
	$right--;
}

$checksum = 0;

foreach ($blocks as $i => $block) {
	if ($block != ".") {
		$checksum += $i * $block;
	}
}

echo "Checksum after defragmentation: {$checksum}\n";
