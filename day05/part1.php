<?php

declare(strict_types=1);

$seeds = [];
$maps = [];

$fp = fopen("input.txt", "r");
$seeds = array_filter(array_map("intval", explode(" ", fgets($fp))));
while ($line = fgets($fp)) {
	if (!($line = trim($line))) {
		continue;
	}
	if (preg_match("/^([^\d]+) map:$/", $line, $matches)) {
		$map_target = &$maps[$matches[1]];
		continue;
	}
	$map_target[] = array_combine(["destination", "source", "length"], array_map("intval", explode(" ", $line)));
}
unset($map_target);

function find_destination(int $source, array $map): int {
	foreach ($map as $mapping) {
		if ($mapping["source"] <= $source && $source < $mapping["source"] + $mapping["length"]) {
			return $source - $mapping["source"] + $mapping["destination"];
		}
	}
	return $source;
}

foreach ($seeds as &$seed) {
	$seed = ["seed" => $seed];
	$seed["soil"] = find_destination($seed["seed"], $maps["seed-to-soil"]);
	$seed["fertilizer"] = find_destination($seed["soil"], $maps["soil-to-fertilizer"]);
	$seed["water"] = find_destination($seed["fertilizer"], $maps["fertilizer-to-water"]);
	$seed["light"] = find_destination($seed["water"], $maps["water-to-light"]);
	$seed["temperature"] = find_destination($seed["light"], $maps["light-to-temperature"]);
	$seed["humidity"] = find_destination($seed["temperature"], $maps["temperature-to-humidity"]);
	$seed["location"] = find_destination($seed["humidity"], $maps["humidity-to-location"]);
}
unset($seed);

$lowest_location = min(array_column($seeds, "location"));
echo "Lowest location: {$lowest_location}\n";
