<?php

declare(strict_types=1);

define("UP", "U");
define("DOWN", "D");
define("LEFT", "L");
define("RIGHT", "R");
define("DIRECTIONS", [UP, DOWN, LEFT, RIGHT]);

define("GROUND", ".");
define("TRENCH", "#");

define("MAP_SIZE", 1000);

function print_map(array $map, array $sizes): void {
	foreach ($map as $y => $row) {
		if ($y === 0) {
			echo " ";
			foreach ($row as $x => $cell) {
				echo $sizes["columns"][$x] ?? 0;
			}
			echo "\n";
		}
		echo $sizes["rows"][$y] ?? 0;
		foreach ($row as $x => $cell) {
			echo $cell;
		}
		echo "\n";
	}
	echo "\n";
}

function step(array $position, string $direction): array {
	[$x, $y] = $position;
	return match ($direction) {
		UP => [$x, $y - 1],
		DOWN => [$x, $y + 1],
		LEFT => [$x - 1, $y],
		RIGHT => [$x + 1, $y],
	};
}

function mark(array &$map, array $position): void {
	[$x, $y] = $position;
	$map[$y][$x] = TRENCH;
}

function split_row(array &$map, array &$sizes, int $y, int $length): void {
	$sizes["rows"] = [
		...array_slice($sizes["rows"], 0, $y),
		$length,
		$sizes["rows"][$y] - $length,
		...array_slice($sizes["rows"], $y + 1),
	];

	$map = [
		...array_slice($map, 0, $y + 1),
		...array_slice($map, $y),
	];
}

function split_column(array &$map, array &$sizes, int $x, int $length): void {
	$sizes["columns"] = [
		...array_slice($sizes["columns"], 0, $x),
		$length,
		$sizes["columns"][$x] - $length,
		...array_slice($sizes["columns"], $x + 1),
	];

	foreach ($map as &$row) {
		$row = [
			...array_slice($row, 0, $x + 1),
			...array_slice($row, $x),
		];
	}
}

function dig(array &$map, array &$sizes, array &$position, string $direction, string $prev_direction, int $length): void {
	$position = step($position, $direction);
	[$x, $y] = $position;

	if (in_array($direction, [LEFT, RIGHT])) {
		// If the current row is wider than 1, we must split it because the new trench (left/right) should have width 1
		if ($sizes["rows"][$y] > 1) {
			if ($prev_direction === UP) {
				// If the direction of the previous trench was up, we split the current row and remain at the top side
				split_row($map, $sizes, $y, 1);
			} else {
				// If the direction of the previous trench was down, we split the current row and go to the bottom side
				split_row($map, $sizes, $y, $sizes["rows"][$y] - 1);
				$position = step($position, DOWN);
				[$x, $y] = $position;
			}
		}

		// If this is a new column, we can simply set the width to the length of the new trench
		if ($sizes["columns"][$x] === 0) {
			$sizes["columns"][$x] = $length;
		}

		// If the current column is wider than the new trench should be, we must split it to the appropriate length
		if ($sizes["columns"][$x] > $length) {
			if ($direction === LEFT) {
				// If the direction of the new trench is left, we split the current column and go to the right side
				split_column($map, $sizes, $x, $sizes["columns"][$x] - $length);
				$position = step($position, RIGHT);
				[$x, $y] = $position;
			} else {
				// If the direction of the new trench is right, we split the current column and remain at the left side
				split_column($map, $sizes, $x, $length);
			}
		}

		mark($map, $position);

		// If the current column is smaller than the new trench should be, we dig the remaining length recursively
		if ($sizes["columns"][$x] < $length) {
			dig($map, $sizes, $position, $direction, $prev_direction, $length - $sizes["columns"][$x]);
		}
	} else {
		// If the current column is wider than 1, we must split it because the new trench (up/down) should have width 1
		if ($sizes["columns"][$x] > 1) {
			if ($prev_direction === LEFT) {
				// If the direction of the previous trench was left, we split the current column and remain at the left side
				split_column($map, $sizes, $x, 1);
			} else {
				// If the direction of the previous trench was right, we split the current column and go to the right side
				split_column($map, $sizes, $x, $sizes["columns"][$x] - 1);
				$position = step($position, RIGHT);
				[$x, $y] = $position;
			}
		}

		// If this is a new row, we can simply set the width to the length of the new trench
		if ($sizes["rows"][$y] === 0) {
			$sizes["rows"][$y] = $length;
		}

		// If the current row is wider than the new trench should be, we must split it to the appropriate length
		if ($sizes["rows"][$y] > $length) {
			if ($direction === UP) {
				// If the direction of the new trench is up, we split the current row and go to the bottom side
				split_row($map, $sizes, $y, $sizes["rows"][$y] - $length);
				$position = step($position, DOWN);
				[$x, $y] = $position;
			} else {
				// If the direction of the new trench is down, we split the current row and remain at the top side
				split_row($map, $sizes, $y, $length);
			}
		}

		mark($map, $position);

		// If the current row is smaller than the new trench should be, we dig the remaining length recursively
		if ($sizes["rows"][$y] < $length) {
			dig($map, $sizes, $position, $direction, $prev_direction, $length - $sizes["rows"][$y]);
		}
	}
}

function fill_from_position(array &$map, array $position): void {
	[$x, $y] = $position;

	if (!isset($map[$y][$x]) || $map[$y][$x] !== GROUND) {
		return;
	}

	$map[$y][$x] = TRENCH;

	foreach (DIRECTIONS as $direction) {
		fill_from_position($map, step($position, $direction));
	}
}

function fill(array &$map): void {
	foreach ($map as $y => $row) {
		$in_trench = false;
		foreach ($row as $x => $cell) {
			$above = $y > 0 ? $map[$y - 1][$x] : GROUND;
			if ($cell === TRENCH) {
				if (!$in_trench) {
					$in_trench = true;
				}
			} else if ($in_trench) {
				if ($above === TRENCH) {
					fill_from_position($map, [$x, $y]);
					return;
				} else {
					$in_trench = false;
				}
			}
		}
	}
}

function count_trenches(array $map, array $sizes): int {
	$sum = 0;
	foreach ($map as $y => $row) {
		$height = $sizes["rows"][$y];
		foreach ($row as $x => $cell) {
			if ($cell === TRENCH) {
				$width = $sizes["columns"][$x];
				$sum += $width * $height;
			}
		}
	}
	return $sum;
}

$map = array_fill(0, MAP_SIZE, array_fill(0, MAP_SIZE, GROUND));
$sizes = ["rows" => array_fill(0, MAP_SIZE, 0), "columns" => array_fill(0, MAP_SIZE, 0)];
$position = [MAP_SIZE / 2, MAP_SIZE / 2];

[$x, $y] = $position;
$sizes["rows"][$y] = 1;
$sizes["columns"][$x] = 1;
mark($map, $position);

$fp = fopen("input.txt", "r");
$prev_direction = "";
while ($line = fgets($fp)) {
	$code = explode(" ", trim($line))[2];
	$code = trim($code, "(#)");
	$length = hexdec(substr($code, 0, 5));
	$direction = match($code[5]) {
		"0" => RIGHT,
		"1" => DOWN,
		"2" => LEFT,
		"3" => UP,
	};

	dig($map, $sizes, $position, $direction, $prev_direction, $length);
	$prev_direction = $direction;
}

fill($map);

$count = count_trenches($map, $sizes);
echo "Trenches: {$count}\n";
