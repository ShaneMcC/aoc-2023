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

	$p1entries = $p2entries = [];
	foreach ($input as $line) {
		preg_match('/(.*) (.*) \(#(.{5})(.)\)/SADi', $line, $m);
		[$all, $p1direction, $p1distance, $p2distance, $p2direction] = $m;
		$p1entries[] = ['direction' => $p1direction, 'distance' => $p1distance];
		$p2entries[] = ['direction' => $p2direction, 'distance' => hexdec($p2distance)];
	}

	function getOutlinePoints($instructions, $useColours = false) {
		global $directions;

		$x = $y = 0;

		$points = [[0, 0]];
		$len = 0;

		foreach ($instructions as $in) {
			$dXY = $directions[$in['direction']];
			$x += $dXY[0] * $in['distance'];
			$y += $dXY[1] * $in['distance'];
			$len += $in['distance'];
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

	[$points, $len] = getOutlinePoints($p1entries);
	$part1 = shoelace($points) + ($len / 2) + 1;
	echo 'Part 1: ', $part1, "\n";

	[$points, $len] = getOutlinePoints($p2entries);
	$part2 = shoelace($points) + ($len / 2) + 1;
	echo 'Part 2: ', $part2, "\n";
