#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$words = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
	$numbers = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];

	function doWordReplace($words, $numbers, $line) {
		return preg_replace_callback('(' . implode('|', $words) . ')', function($matches) use ($words, $numbers) {
			return $numbers[array_search($matches[0], $words)];
		}, $line);
	}

	function getNumbers($line) {
		$first = $last = null;
		for ($i = 0; $i < strlen($line); $i++) {
			if (is_numeric($line[$i])) {
				$first = $first ?? $line[$i];
				$last = $line[$i];
			}
		}
		return $first.$last;
	}

	$part1 = $part2 = 0;
	foreach ($input as $line) {
		$part1 += getNumbers($line);
		$part2 += getNumbers(doWordReplace($words, $numbers, $line));
	}

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
