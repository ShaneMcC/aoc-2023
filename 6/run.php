#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$races = [];
	foreach ($input as $line) {
		$bits = explode(':', $line);
		$type = $bits[0];
		$numbers = $bits[1];
		preg_match_all('/(\d+)/', $numbers, $m);

		foreach ($m[1] as $id => $num) {
			$races[$id][strtolower($type)] = $num;
		}
	}

	function simulate($time, $hold) {
		$distance = $hold * ($time - $hold);

		debugOut("\t", $hold, ' => ', $distance);

		return $distance;
	}

	function getWinningOptions($race) {
		$winning = [];
		for ($i = 0; $i <= $race['time']; $i++) {
			$final = simulate($race['time'], $i);

			if ($final > $race['distance']) {
				debugOut(' => Winner!');
				$winning[] = $i;
			}

			debugOut("\n");
		}

		return $winning;
	}

	$part1 = 1;
	foreach ($races as $raceid => $race) {
		echo json_encode($race), "\n";
		$part1 *= count(getWinningOptions($race));
	}

	echo 'Part 1: ', $part1, "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
