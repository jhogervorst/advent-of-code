<?php

declare(strict_types=1);

function create_plot(string $plant, int $x, int $y): array {
	return [
		"position" => [$x, $y],
		"plant" => $plant,
		"region" => null,
	];
}

function empty_region(): array {
	return [
		"plots" => [],
		"area" => 0,
		"perimeter" => 0,
	];
}

function &create_region(array &$plot): array {
	$region = empty_region();
	add_plot_to_region($plot, $region, 0);
	return $region;
}

function add_plot_to_region(array &$plot, array &$region, int $adjacent_sides = 1): void {
	$plot["region"] = &$region;

	$region["plots"][] = &$plot;
	$region["area"] += 1;
	$region["perimeter"] += 4 - 2 * $adjacent_sides;
}

function merge_regions(array &$a, array &$b, int $adjacent_sides = 1): void {
	foreach ($b["plots"] as &$plot) {
		$plot["region"] = &$a;
	}

	$a = [
		"plots" => [...$a["plots"], ...$b["plots"]],
		"area" => $a["area"] + $b["area"],
		"perimeter" => $a["perimeter"] + $b["perimeter"] - 2 * $adjacent_sides,
	];

	$b = empty_region();
}

$map = array_map("str_split", explode("\n", trim(file_get_contents($argv[1]))));
$regions = [];
$total_price = 0;

foreach ($map as $y => &$row) {
	foreach ($row as $x => &$plot) {
		$plot = create_plot($plot, $x, $y);

		$above = $map[$y - 1][$x] ?? null;
		$left = $map[$y][$x - 1] ?? null;

		$match_above = $above && $above["plant"] == $plot["plant"];
		$match_left = $left && $left["plant"] == $plot["plant"];

		if (!$match_above && !$match_left) {
			$regions[] = &create_region($plot);
		} elseif ($match_above && $match_left) {
			if ($above["region"] == $left["region"]) {
				add_plot_to_region($plot, $above["region"], 2);
			} else {
				add_plot_to_region($plot, $above["region"]);
				merge_regions($above["region"], $left["region"]);
			}
		} else if ($match_above) {
			add_plot_to_region($plot, $above["region"]);
		} else if ($match_left) {
			add_plot_to_region($plot, $left["region"]);
		}
	}
}

foreach ($regions as $region) {
	$total_price += $region["area"] * $region["perimeter"];
}

echo "Total price: {$total_price}\n";
