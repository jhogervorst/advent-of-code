<?php

declare(strict_types=1);

$seed_ranges = [];
$maps = [];

$fp = fopen("input.txt", "r");
$seed_ranges = array_chunk(array_filter(array_map("intval", explode(" ", fgets($fp)))), 2);
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

function find_source(array $map, int $destination): int {
	foreach ($map as $mapping) {
		if ($mapping["destination"] <= $destination && $destination < $mapping["destination"] + $mapping["length"]) {
			return $destination - $mapping["destination"] + $mapping["source"];
		}
	}
	return $destination;
}

for ($i = 0; true; ++$i) {
	if ($i % 100000 === 0) {
		echo ".";
	}

	$seed = ["location" => $i];
	$seed["humidity"] = find_source($maps["humidity-to-location"], $seed["location"]);
	$seed["temperature"] = find_source($maps["temperature-to-humidity"], $seed["humidity"]);
	$seed["light"] = find_source($maps["light-to-temperature"], $seed["temperature"]);
	$seed["water"] = find_source($maps["water-to-light"], $seed["light"]);
	$seed["fertilizer"] = find_source($maps["fertilizer-to-water"], $seed["water"]);
	$seed["soil"] = find_source($maps["soil-to-fertilizer"], $seed["fertilizer"]);
	$seed["seed"] = find_source($maps["seed-to-soil"], $seed["soil"]);

	foreach ($seed_ranges as $range) {
		[$source, $length] = $range;
		if ($source <= $seed["seed"] && $seed["seed"] < $source + $length) {
			echo "\nLowest location: {$seed["location"]}\n";
			break 2;
		}
	}
}
