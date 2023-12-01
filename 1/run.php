#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$words = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
	$numbers = ['o1e', 't2o', 't3e', 'f4r', 'f5e', 's6x', 's7n', 'e8t', 'n9e'];

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
		$part2 += getNumbers(str_replace($words, $numbers, $line));
	}

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
