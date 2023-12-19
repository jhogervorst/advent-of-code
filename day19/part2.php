<?php

declare(strict_types=1);

define("START_WORKFLOW", "in");
define("REJECTED", "R");
define("ACCEPTED", "A");

define("CONDITION_GREATER_THAN", ">");
define("CONDITION_LESS_THAN", "<");

define("RATING_MIN", 1);
define("RATING_MAX", 4000);

function parse_rule(string $rule): array {
	if (!preg_match("/^((?<condition_property>[xmas])(?<condition_operator>[<>])(?<condition_value>\d+):)?(?<destination>[a-z]+)$/i", $rule, $matches)) {
		throw new Exception("Invalid rule: {$rule}");
	}
	$matches = array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
	return $matches;
}

function parse_workflow(string $line): array {
	[$name, $rules] = explode("{", rtrim($line, "}"));
	$rules = explode(",", $rules);
	$rules = array_map("parse_rule", $rules);
	return [$name, $rules];
}

function count_part(array $part): int {
	$count = 1;
	foreach ($part as $prop) {
		[$min, $max] = $prop;
		$count *= $max - $min + 1;
	}
	return $count;
}

function count_parts(array $parts): int {
	return array_sum(array_map("count_part", $parts));
}

function evaluate_workflow(array $part, array $rules): array {
	foreach ($rules as $rule) {
		$prop = $rule["condition_property"];
		$val = $rule["condition_value"];
		[$min, $max] = $part[$prop] ?? [0, 0];

		if ($rule["condition_operator"] === CONDITION_GREATER_THAN) {
			if ($min > $val) {
				return [[...$part, "workflow" => $rule["destination"]]];
			} elseif ($max > $val) {
				return [
					[...$part, $prop => [$min, $val]],
					[...$part, $prop => [$val + 1, $max], "workflow" => $rule["destination"]],
				];
			}
		}

		elseif ($rule["condition_operator"] === CONDITION_LESS_THAN) {
			if ($max < $val) {
				return [[...$part, "workflow" => $rule["destination"]]];
			} elseif ($min < $val) {
				return [
					[...$part, $prop => [$min, $val - 1], "workflow" => $rule["destination"]],
					[...$part, $prop => [$val, $max]],
				];
			}
		}

		else {
			return [[...$part, "workflow" => $rule["destination"]]];
		}
	}

	throw new Exception("No rule matched");
}

$workflows = [];
$accepted = [];

$queue = [
	[
		"x" => [RATING_MIN, RATING_MAX],
		"m" => [RATING_MIN, RATING_MAX],
		"a" => [RATING_MIN, RATING_MAX],
		"s" => [RATING_MIN, RATING_MAX],
		"workflow" => START_WORKFLOW,
	],
];

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	$line = trim($line);
	if (!empty($line) && $line[0] !== "{") {
		[$name, $rules] = parse_workflow($line);
		$workflows[$name] = $rules;
	}
}

while ($part = array_pop($queue)) {
	if ($part["workflow"] === ACCEPTED) {
		unset($part["workflow"]);
		$accepted[] = $part;
	} elseif ($part["workflow"] !== REJECTED) {
		$rules = $workflows[$part["workflow"]];
		array_push($queue, ...evaluate_workflow($part, $rules));
	}
}

$count = count_parts($accepted);
echo "Number of accepted parts: {$count}\n";
