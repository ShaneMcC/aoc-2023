#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$directions = [];
	$directions['R'] = [1, 0];
	$directions['L'] = [-1, 0];
	$directions['D'] = [0, 1];
	$directions['U'] = [0, -1];

	$directions['0'] = $directions['R'];
	$directions['1'] = $directions['D'];
	$directions['2'] = $directions['L'];
	$directions['3'] = $directions['U'];

	$entries = [];
	foreach ($input as $line) {
		preg_match('#(.*) (.*) \((.*)\)#SADi', $line, $m);
		[$all, $direction, $distance, $colour] = $m;
		$entries[] = ['direction' => $direction, 'distance' => $distance, 'colour' => $colour];
	}

	function getOutlinePoints($instructions, $useColours = false) {
		global $directions;

		$x = $y = 0;

		$points = [[0, 0]];
		$len = 0;

		foreach ($instructions as $in) {
			$distance = $useColours ? hexdec(substr($in['colour'], 1, 5)) : $in['distance'];
			$dXY = $directions[$useColours ? $in['colour'][6] : $in['direction']];

			$x += $dXY[0] * $distance;
			$y += $dXY[1] * $distance;
			$len += $distance;
			$points[] = [$x, $y];
		}

		return [$points, $len];
	}

	function shoelace($points) {
		$area = 0;
		$count = count($points);
		for ($i = 0; $i < $count - 1; $i++) {
			$area += $points[$i][0] * $points[$i + 1][1] - $points[$i + 1][0] * $points[$i][1];
		}
		$area += $points[$count - 1][0] * $points[0][1] - $points[0][0] * $points[$count - 1][1];

		return abs($area) / 2;
	}

	[$points, $len] = getOutlinePoints($entries);
	$part1 = shoelace($points) + ($len / 2) + 1;
	echo 'Part 1: ', $part1, "\n";

	[$points, $len] = getOutlinePoints($entries, true);
	$part2 = shoelace($points) + ($len / 2) + 1;
	echo 'Part 2: ', $part2, "\n";
