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
		debugOut($first, $last);
		return ($first ?? '0').($last ?? '0');
	}

	$part1 = $part2 = 0;
	foreach ($input as $line) {
		debugOut($line, ' => 1: ');
		$part1 += getNumbers($line);
		debugOut(', 2: ');
		$part2 += getNumbers(str_replace($words, $numbers, $line));
		debugOut("\n");
	}

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
