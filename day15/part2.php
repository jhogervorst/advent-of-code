<?php

declare(strict_types=1);

function hash_string(string $string): int {
	$value = 0;
	foreach (str_split($string) as $char) {
		$value = (($value + ord($char)) * 17) % 256;
	}
	return $value;
}

function find_lens(array $box, string $label): int {
	$filtered = array_filter($box, fn (array $lens): bool => $lens["label"] === $label);
	return empty($filtered) ? -1 : array_keys($filtered)[0];
}

$sequence = explode(",", trim(file_get_contents("input.txt")));
$boxes = array_fill(0, 256, []);
$power = 0;

foreach ($sequence as $step) {
	preg_match("/^([a-z]+)([-=])(\d*)$/", $step, $matches);
	[$_, $label, $operation, $focal_length] = $matches;

	$box = hash_string($label);
	$existing_lens = find_lens($boxes[$box], $label);

	if ($operation === "=" && $existing_lens === -1) {
		$boxes[$box][] = ["label" => $label, "focal_length" => $focal_length];
	} elseif ($operation === "=" && $existing_lens !== -1) {
		$boxes[$box][$existing_lens]["focal_length"] = $focal_length;
	} elseif ($operation === "-" && $existing_lens !== -1) {
		unset($boxes[$box][$existing_lens]);
		$boxes[$box] = array_values($boxes[$box]);
	}
}

foreach ($boxes as $b => $lenses) {
	foreach ($lenses as $l => $lens) {
		$power += ($b + 1) * ($l + 1) * $lens["focal_length"];
	}
}

echo "Total power: {$power}\n";
