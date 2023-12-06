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

	function simulate($time, $hold) {
		$distance = $hold * ($time - $hold);
		return $distance;
	}

	function getWinningOptions($race) {
		$lower = findLower($race);
		$higher = findHigher($race);

		return $higher - $lower + 1;
	}

	function findLower($race) {
		$time = $race['time'];
		$distance = $race['distance'];

		$low = 0;
		$high = $time - 1;

		while ($low <= $high) {
			$mid = floor(($low + $high) / 2);

			$thisResult = (simulate($time, $mid) > $distance);
			$lowerResult = (simulate($time, $mid - 1) > $distance);
			$isLowest = ($thisResult && !$lowerResult);

			if ($isLowest) {
				return $mid;
			}

			if ($thisResult) {
				$high = $mid -1;
			} else {
				$low = $mid + 1;
			}
		}

		return false;
	}

	function findHigher($race) {
		$time = $race['time'];
		$distance = $race['distance'];

		$low = 0;
		$high = $time - 1;

		while ($low <= $high) {
			$mid = floor(($low + $high) / 2);

			$thisResult = (simulate($time, $mid) > $distance);
			$upperResult = (simulate($time, $mid + 1) > $distance);
			$isHighest = ($thisResult && !$upperResult);

			if ($isHighest) {
				return $mid;
			}

			if (!$thisResult) {
				$high = $mid -1;
			} else {
				$low = $mid + 1;
			}
		}

		return false;
	}

	$part1 = 1;
	foreach ($races as $raceid => $race) {
		$part1 *= getWinningOptions($race);
	}

	echo 'Part 1: ', $part1, "\n";

	$part2 = getWinningOptions($megaRace);
	echo 'Part 2: ', $part2, "\n";
