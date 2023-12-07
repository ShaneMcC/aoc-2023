#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	enum HandType: string {
		case FiveOfAKind = 'FiveOfAKind';
		case FourOfAKind = 'FourOfAKind';
		case FullHouse = 'FullHouse';
		case ThreeOfAKind = 'ThreeOfAKind';
		case TwoPair = 'TwoPair';
		case OnePair = 'OnePair';
		case HighCard = 'HighCard';

		function getNumericValue(): int {
			return match($this) {
				HandType::FiveOfAKind => 10,
				HandType::FourOfAKind => 9,
				HandType::FullHouse => 8,
				HandType::ThreeOfAKind => 7,
				HandType::TwoPair => 6,
				HandType::OnePair => 5,
				HandType::HighCard => 4,
				default => throw new Exception('No numeric value for: ' . $this->value),
			};
		}
	}

	enum CardType: string {
		case Ace = 'A';
		case King = 'K';
		case Queen = 'Q';
		case Jack = 'J';
		case Ten = 'T';
		case Nine = '9';
		case Eight = '8';
		case Seven = '7';
		case Six = '6';
		case Five = '5';
		case Four = '4';
		case Three = '3';
		case Two = '2';
		case One = '1';

		function getNumericValue(): int {
			return match($this) {
				CardType::Ace => 14,
				CardType::King => 13,
				CardType::Queen => 12,
				CardType::Jack => 11,
				CardType::Ten => 10,
				CardType::Nine => 9,
				CardType::Eight => 8,
				CardType::Seven => 7,
				CardType::Six => 6,
				CardType::Five => 5,
				CardType::Four => 4,
				CardType::Three => 3,
				CardType::Two => 2,
				CardType::One => 1,
				default => throw new Exception('No numeric value for: ' . $this->value),
			};
		}
	}

	function getHandType($hand) {
		$acv = array_count_values(array_map(function($c) { return $c->value; }, $hand));
		$pairs = 0;

		foreach ($acv as $card => $count) {
			if ($count == 5) { return HandType::FiveOfAKind; }
			if ($count == 4) { return HandType::FourOfAKind; }
			if ($count == 3) {
				if (count($acv) == 2) {
					return HandType::FullHouse;
				} else {
					return HandType::ThreeOfAKind;
				}
			}
			if ($count == 2) { $pairs++; }
		}

		if ($pairs == 2) { return HandType::TwoPair; }
		else if ($pairs == 1) { return HandType::OnePair; }

		return HandType::HighCard;
	}

	$entries = [];
	foreach ($input as $line) {
		preg_match('#(.*) (.*)#SADi', $line, $m);
		[$all, $handStr, $bid] = $m;

		$hand = [];
		foreach (str_split($handStr) as $card) {
			$hand[] = CardType::from($card);
		}

		$entries[] = ['hand' => $hand, 'type' => getHandType($hand), 'bid' => $bid, 'handStr' => $handStr];
	}

	usort($entries, function($a, $b) {
		if ($a['type'] === $b['type']) {
			for ($i = 0; count($a['hand']); $i++) {
				if ($a['hand'][$i] != $b['hand'][$i]) {
					return ($a['hand'][$i]->getNumericValue() <=> $b['hand'][$i]->getNumericValue());
				}
			}
		} else {
			return ($a['type']->getNumericValue() <=> $b['type']->getNumericValue());
		}

		echo 'Unable to sort: ', "\n";
		var_dump($a);
		var_dump($b);
		die();
	});

	$part1 = 0;
	foreach ($entries as $rank => $entry) {
		$part1 += ($rank + 1) * $entry['bid'];
	}

	echo 'Part 1: ', $part1, "\n";
