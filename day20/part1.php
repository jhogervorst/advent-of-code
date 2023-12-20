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

define("BUTTON_PUSHES", 1000);

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
		echo "{$pulse['from']} -{$pulse['type']}-> {$pulse['to']}\n";
		array_push($pulses, ...evaluate($pulse, $modules));
	}

	echo str_repeat("-", 30) . "\n";
	return $pulses;
}

function count_pulses(array $pulses): void {
	$low_pulses = count(array_filter($pulses, fn(array $pulse): bool => $pulse["type"] === LOW_PULSE));
	$high_pulses = count(array_filter($pulses, fn(array $pulse): bool => $pulse["type"] === HIGH_PULSE));
	$product = $low_pulses * $high_pulses;
	echo "Low pulses: {$low_pulses}\n";
	echo "High pulses: {$high_pulses}\n";
	echo "Product: {$product}\n";
}

$modules = load_modules("input.txt");
initialize_modules($modules);

$pulses = [];
for ($i = 0; $i < BUTTON_PUSHES; ++$i) {
	array_push($pulses, ...press_button($modules));
}
count_pulses($pulses);
