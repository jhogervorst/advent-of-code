<?php

declare(strict_types=1);

$map = array_map("str_split", explode("\n", trim(file_get_contents($argv[1]))));

$frequencies = [];
$antinodes = [];

foreach ($map as $y => $row) {
	foreach ($row as $x => $cell) {
		if ($cell != '.') {
			$frequencies[$cell][] = ["x" => $x, "y" => $y];
		}
	}
}

foreach ($frequencies as $positions) {
	foreach ($positions as $i => $a) {
		foreach (array_slice($positions, $i + 1) as $b) {
			$dx = $b["x"] - $a["x"];
			$dy = $b["y"] - $a["y"];

			for ($k = 0; true; $k++) {
				$c = ["x" => $a["x"] - $k * $dx, "y" => $a["y"] - $k * $dy];
				$d = ["x" => $b["x"] + $k * $dx, "y" => $b["y"] + $k * $dy];

				$c_valid = isset($map[$c["y"]][$c["x"]]);
				$d_valid = isset($map[$d["y"]][$d["x"]]);

				if ($c_valid) $antinodes[] = $c;
				if ($d_valid) $antinodes[] = $d;

				if (!$c_valid && !$d_valid) break;
			}
		}
	}
}

$count_unique = count(array_unique(array_map("json_encode", $antinodes)));

echo "Number of unique locations with an antinode: {$count_unique}\n";
