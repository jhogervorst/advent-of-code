<?php

declare(strict_types=1);

define("CARDS", array_reverse(["A", "K", "Q", "T", "9", "8", "7", "6", "5", "4", "3", "2", "J"]));
define("JOKER", "J");

function hand_type(string $hand): string {
	if (($joker_pos = strpos($hand, JOKER)) !== false) {
		$hands = array_map(fn (string $card): string => substr_replace($hand, $card, $joker_pos, 1), array_diff(CARDS, [JOKER]));
		$hands = array_map(fn (string $hand): string => hand_type($hand), $hands);
		usort($hands, fn (string $type1, string $type2): int => -strcmp($type1, $type2));
		return $hands[0];
	}

	$cards = str_split($hand);
	$card_occurrences = array_count_values($cards);
	$occurrences_counts = array_values($card_occurrences);
	rsort($occurrences_counts);

	switch ($occurrences_counts) {
		case [5]: return "7_five_of_a_kind";
		case [4, 1]: return "6_four_of_a_kind";
		case [3, 2]: return "5_full_house";
		case [3, 1, 1]: return "4_three_of_a_kind";
		case [2, 2, 1]: return "3_two_pairs";
		case [2, 1, 1, 1]: return "2_one_pair";
		case [1, 1, 1, 1, 1]: return "1_high card";
		default: throw new Exception("Unknown type for hand '{$hand}'");
	}
}

function card_cmp(string $card1, string $card2): int {
	$index1 = array_search($card1, CARDS);
	$index2 = array_search($card2, CARDS);
	return $index1 <=> $index2;
}

function hand_cmp(string $hand1, string $hand2) {
	$type1 = hand_type($hand1);
	$type2 = hand_type($hand2);
	$result = strcmp($type1, $type2);
	for ($c = 0; $result == 0 && $c < 5; ++$c) {
		$result = card_cmp($hand1[$c], $hand2[$c]);
	}
	return $result;
}

$games = file("input.txt", FILE_IGNORE_NEW_LINES);
$games = array_map(fn (string $line): array => array_combine(["hand", "bid"], explode(" ", $line)), $games);
usort($games, fn (array $game1, array $game2): int => hand_cmp($game1["hand"], $game2["hand"]));

$sum = 0;
foreach ($games as $i => $game) {
	$rank = $i + 1;
	$winnings = $rank * $game["bid"];
	$sum += $winnings;
}
echo "Sum: {$sum}\n";
