<?php

declare(strict_types=1);

$disk_map = trim(file_get_contents($argv[1]));
$chunks = [];

for ($i = 0; $i * 2 < strlen($disk_map); $i++) {
	if ($file_length = $disk_map[$i * 2]) {
		$chunks[] = [$i, $file_length];
	}

	if ($free_length = $disk_map[$i * 2 + 1] ?? 0) {
		$chunks[] = [".", $free_length];
	}
}

for ($right = count($chunks) - 1; $right > 0; $right--) {
	$right_chunk = $chunks[$right];
	[$right_block, $right_length] = $right_chunk;

	if ($right_block == ".") {
		continue;
	}

	for ($left = 0; $left < $right; $left++) {
		$left_chunk = $chunks[$left];
		[$left_block, $left_length] = $left_chunk;

		if ($left_block != ".") {
			continue;
		}

		if ($left_length < $right_length) {
			continue;
		}

		$chunks[$left] = $right_chunk;
		$chunks[$right] = $left_chunk;

		if ($extra_length = $left_length - $right_length) {
			$chunks[$right] = [$left_block, $right_length];
			$chunks = [
				...array_slice($chunks, 0, $left + 1),
				[$left_block, $extra_length],
				...array_slice($chunks, $left + 1),
			];

			$right++;
		}

		break;
	}
}

$checksum = 0;
$i = 0;

foreach ($chunks as [$chunk_block, $chunk_length]) {
	for ($j = 0; $j < $chunk_length; $j++, $i++) {
		if ($chunk_block != ".") {
			$checksum += $i * $chunk_block;
		}
	}
}

echo "Checksum after defragmentation: {$checksum}\n";
