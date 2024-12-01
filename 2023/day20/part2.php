<?php

declare(strict_types=1);

define("HIGH_PULSE", "high");
define("LOW_PULSE", "low");

define("ON_STATE", "on");
define("OFF_STATE", "off");

define("FLIP_FLOP_MODULE", "%");
define("CONJUNCTION_MODULE", "&");
define("BROADCASTER_MODULE", "broadcaster");
define("BUTTON_MODULE", "button");
define("LAST_MODULE", "rx");

function load_modules(string $file): array {
	$modules = array_map(function(string $line): array {
		preg_match("/(?<type>[^a-z])?(?<name>[a-z]+) -> (?<destinations>[a-z, ]+)/", $line, $matches);
		return [
			"type" => $matches["type"] ?: null,
			"name" => $matches["name"],
			"destinations" => explode(", ", $matches["destinations"]),
		];
	}, file($file, FILE_IGNORE_NEW_LINES));
	return array_combine(array_column($modules, "name"), $modules);
}

function initialize_modules(array &$modules): void {
	$modules[BUTTON_MODULE] = [
		"type" => null,
		"name" => BUTTON_MODULE,
		"destinations" => [BROADCASTER_MODULE],
	];

	foreach ($modules as &$module) {
		if ($module["type"] === FLIP_FLOP_MODULE) {
			$module["state"] = OFF_STATE;
		}

		foreach ($module["destinations"] as $destination) {
			if (!isset($modules[$destination])) {
				$modules[$destination] = [
					"type" => null,
					"name" => $destination,
					"destinations" => [],
				];
			} elseif ($modules[$destination]["type"] === CONJUNCTION_MODULE) {
				$modules[$destination]["memory"][$module["name"]] = LOW_PULSE;
			}
		}
	}
}

function evaluate(array $pulse, array &$modules): array {
	$from = $modules[$pulse["from"]];
	$to = &$modules[$pulse["to"]];
	$send_pulse_type = null;

	if ($to["type"] === FLIP_FLOP_MODULE) {
		if ($pulse["type"] === LOW_PULSE) {
			if ($to["state"] === OFF_STATE) {
				$to["state"] = ON_STATE;
				$send_pulse_type = HIGH_PULSE;
			} elseif ($to["state"] === ON_STATE) {
				$to["state"] = OFF_STATE;
				$send_pulse_type = LOW_PULSE;
			}
		}
	} elseif ($to["type"] === CONJUNCTION_MODULE) {
		$to["memory"][$from["name"]] = $pulse["type"];
		if (array_values(array_unique($to["memory"])) == [HIGH_PULSE]) {
			$send_pulse_type = LOW_PULSE;
		} else {
			$send_pulse_type = HIGH_PULSE;
		}
	} elseif ($to["name"] === BROADCASTER_MODULE) {
		$send_pulse_type = $pulse["type"];
	}

	if ($send_pulse_type) {
		return array_map(fn(string $destination): array => [
			"from" => $to["name"],
			"type" => $send_pulse_type,
			"to" => $destination,
		], $to["destinations"]);
	} else {
		return [];
	}
}

function press_button(array &$modules): array {
	$pulses = [[
		"from" => BUTTON_MODULE,
		"type" => LOW_PULSE,
		"to" => BROADCASTER_MODULE,
	]];

	for ($i = 0; $i < count($pulses); ++$i) {
		$pulse = $pulses[$i];
		array_push($pulses, ...evaluate($pulse, $modules));
	}

	return $pulses;
}

// Inspired by https://www.reddit.com/r/adventofcode/comments/18mtwrs/ and
// https://www.reddit.com/r/adventofcode/comments/18msq8g/, I didn't figure it
// out myself 😅
function find_loops_for_last_module(array &$modules): array {
	foreach ($modules as $module) {
		if (in_array(LAST_MODULE, $module["destinations"])) {
			$second_last_module = &$modules[$module["name"]];
		}
	}

	$loops = array_map(fn() => null, $second_last_module["memory"]);
	$all_known = fn(array $loops): bool => array_filter($loops) == $loops;

	for ($i = 1; !$all_known($loops); ++$i) {
		$pulses = press_button($modules);
		foreach ($pulses as $pulse) {
			if (
				$pulse["to"] === $second_last_module["name"]
				&& $pulse["type"] === HIGH_PULSE
				&& !$loops[$pulse["from"]]
			) {
				$loops[$pulse["from"]] = $i;
			}
		}
	}

	return $loops;
}

$modules = load_modules("input.txt");
initialize_modules($modules);
$loops = find_loops_for_last_module($modules);

$product = 1;
foreach ($loops as $name => $count) {
	echo "{$name}: {$count}\n";
	$product *= $count;
}
echo "Product: {$product}\n";
