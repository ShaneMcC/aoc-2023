#!/usr/bin/php
<?php
require_once(dirname(__FILE__) . '/../common/common.php');
$map = getInputMap();

$directions = [];
$directions['N'] = [0, -1];
$directions['E'] = [-1, 0];
$directions['S'] = [0, 1];
$directions['W'] = [1, 0];

$start = findCells($map, 'S')[0];

function findLocations($map, $start, $steps, $canWrap = false) {
	global $directions;

	$queue = new SPLPriorityQueue();
	$queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
	$queue->insert([$start[0], $start[1]], 0);

	$height = count($map);
	$width = count($map[0]);

	$costs = [];

	while (!$queue->isEmpty()) {
		$q = $queue->extract();
		[$x, $y] = $q['data'];
		$cost = abs($q['priority']);

		if ($cost > $steps) { break; }
		if (isset($costs["{$x},{$y}"])) { continue; }
		$costs["{$x},{$y}"] = $cost;

		foreach ($directions as [$dX, $dY]) {
			$pX = $x + $dX;
			$pY = $y + $dY;

			$testX = $canWrap ? wrapmod($pX, $width) : $pX;
			$testY = $canWrap ? wrapmod($pY, $height) : $pY;

			if (($map[$testY][$testX] ?? '#') != '#') {
				$queue->insert([$pX, $pY], -($cost + 1));
			}
		}
	}

	return $costs;
}

$steps = 64;
$locations = findLocations($map, $start, $steps);
$part1 = 0;

$testMap = $map;
foreach ($locations as $loc => $cost) {
	[$x, $y] = explode(',', $loc);

	if ($cost %2 == 0) {
		$part1++;
		$testMap[$y][$x] = 'O';
	}
}
if (isDebug()) {
	drawMap($testMap);
}

echo 'Part 1: ', $part1, "\n";

$steps = 64;
$part2 = 0;
$height = count($map);
$width = count($map[0]);
$yDiff = $steps;
for ($y = ($start[1] - $yDiff); $y != ($start[1] + $yDiff + 1); $y++) {
	$diff = abs($start[0] - $y);
	$xDiff = $steps - $diff;
	$minX = $start[0] - $xDiff;
	$maxX = $start[0] + $xDiff;

	for ($x = $minX; $x <= $maxX; $x += 2) {
		$testX = wrapmod($x, $width);
		$testY = wrapmod($y, $height);

		if ($map[$testY][$testX] != '#') {
			$part2++;
		}
	}
}

echo 'Part 2: ', $part2, "\n";
