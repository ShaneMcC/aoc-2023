#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$races = [];
	$megaRace = [];
	foreach ($input as $line) {
		$bits = explode(':', $line);
		$type = $bits[0];
		$numbers = $bits[1];
		preg_match_all('/(\d+)/', $numbers, $m);

		foreach ($m[1] as $id => $num) {
			$races[$id][strtolower($type)] = $num;
		}

		$megaRace[strtolower($type)] = preg_replace('/[^0-9]/', '', $numbers);
	}

	function isWinner($time, $value, $distance) {
		return ($value * ($time - $value)) > $distance;
	}

	function getWinningOptions($race) {
		$time = $race['time'];
		$distance = $race['distance'];

		// Look for the first value that wins.
		// This will be the value that is a winner, but 1 before it is not.
		$lower = doBinarySearch(0, $time, function($value) use ($time, $distance) {
			$thisResult = isWinner($time, $value, $distance);
			$lowerResult = isWinner($time, $value - 1, $distance);
			$isLowest = ($thisResult && !$lowerResult);

			if ($isLowest) {
				return 0;
			} else if ($thisResult) {
				return -1;
			} else {
				return 1;
			}
		});

		// Look for the last value that wins.
		// This will be the value that is a winner, but 1 after it is not.
		$higher = doBinarySearch(0, $time, function($value) use ($time, $distance) {
			$thisResult = isWinner($time, $value, $distance);
			$upperResult = isWinner($time, $value + 1, $distance);
			$isHighest = ($thisResult && !$upperResult);

			if ($isHighest) {
				return 0;
			} else if ($thisResult) {
				return 1;
			} else {
				return -1;
			}
		});

		return $higher - $lower + 1;
	}

	$part1 = 1;
	foreach ($races as $raceid => $race) {
		$part1 *= getWinningOptions($race);
	}

	echo 'Part 1: ', $part1, "\n";

	echo 'Part 2: ', getWinningOptions($megaRace), "\n";
