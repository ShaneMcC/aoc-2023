#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$directions = [];
	$directions['R'] = [1, 0];
	$directions['L'] = [-1, 0];
	$directions['D'] = [0, 1];
	$directions['U'] = [0, -1];

	$entries = [];
	foreach ($input as $line) {
		preg_match('#(.*) (.*) \((.*)\)#SADi', $line, $m);
		[$all, $direction, $distance, $colour] = $m;
		$entries[] = ['direction' => $direction, 'distance' => $distance, 'colour' => $colour];
	}

	function getOutline($instructions) {
		global $directions;

		$map = [];
		$x = $y = 0;

		$map[0][0] = '#';

		foreach ($instructions as $in) {
			$dXY = $directions[$in['direction']];
			for ($i = 0; $i < $in['distance']; $i++) {
				$x += $dXY[0];
				$y += $dXY[1];

				$map[$y][$x] = '#';
			}
		}

		return $map;
	}

	function digInterior($map) {

		$start = [1,1];
		$points = [$start];
		while (!empty($points)) {
			[$x, $y] = array_pop($points);
			$map[$y][$x] = '#';
			foreach (getAllAdjacentCells($map, $x, $y) as [$pX, $pY]) {
				if (($map[$pY][$pX] ?? '.') == '.') {
					$points[] = [$pX, $pY];
				}
			}
		}

		return $map;
	}

	$map = getOutline($entries);
	if (isDebug()) { drawSparseMap($map, ' ', true, 'Outline'); }
	$map = digInterior($map);
	if (isDebug()) { drawSparseMap($map, '.', true, 'Filled'); }

	$part1 = count(findCells($map, '#'));
	echo 'Part 1: ', $part1, "\n";

	// $part2 = 0;
	// echo 'Part 2: ', $part2, "\n";
