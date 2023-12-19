<?php

declare(strict_types=1);

define("START_WORKFLOW", "in");
define("REJECTED", "R");
define("ACCEPTED", "A");

define("CONDITION_GREATER_THAN", ">");
define("CONDITION_LESS_THAN", "<");

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

function parse_part(string $line): array {
	$ratings = explode(",", trim($line, "{}"));
	$ratings = array_map(fn(string $rating): array => explode("=", $rating), $ratings);
	$keys = array_column($ratings, 0);
	$values = array_column($ratings, 1);
	$values = array_map("intval", $values);
	$ratings = array_combine($keys, $values);
	return $ratings;
}

function count_parts(array $parts): int {
	return array_sum(array_map("array_sum", $parts));
}

function evaluate_rule(array $part, array $rule): ?string {
	if (
		($rule["condition_operator"] === CONDITION_GREATER_THAN && !($part[$rule["condition_property"]] > $rule["condition_value"]))
		|| ($rule["condition_operator"] === CONDITION_LESS_THAN && !($part[$rule["condition_property"]] < $rule["condition_value"]))
	) {
		return null;
	}
	return $rule["destination"];
}

function evaluate_workflow(array $part, array $workflow): string {
	foreach ($workflow as $rule) {
		if ($result = evaluate_rule($part, $rule)) {
			return $result;
		}
	}
	throw new Exception("No rule matched");
}

function evaluate(array $part, array $workflows): bool {
	$workflow = START_WORKFLOW;
	while (!in_array($workflow, [ACCEPTED, REJECTED])) {
		$workflow = evaluate_workflow($part, $workflows[$workflow]);
	}
	return $workflow === ACCEPTED;
}

$workflows = [];
$parts = [];

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	$line = trim($line);
	if (!empty($line)) {
		if ($line[0] === "{") {
			$parts[] = parse_part($line);
		} else {
			[$name, $rules] = parse_workflow($line);
			$workflows[$name] = $rules;
		}
	}
}

$accepted = array_filter($parts, fn(array $part): bool => evaluate($part, $workflows));

$count = count_parts($accepted);
echo "Number of accepted parts: {$count}\n";
