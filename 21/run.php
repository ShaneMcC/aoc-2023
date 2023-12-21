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

function countLocations($locations, $parity = 0) {
	$res = 0;
	foreach ($locations as $cost) {
		if ($cost % 2 == $parity) {
			$res++;
		}
	}
	return $res;
}

$steps = 64;
$locations = findLocations($map, $start, $steps);
$part1 = 0;

if (isDebug()) {
	$testMap = $map;
	foreach ($locations as $loc => $cost) {
		[$x, $y] = explode(',', $loc);

		if ($cost % 2 == 0) {
			$testMap[$y][$x] = 'O';
		}
	}
	drawMap($testMap);
}

echo 'Part 1: ', countLocations($locations), "\n";

// This is some maths I don't understand.
// or as it turns out, care to.
//
// https://www.reddit.com/r/adventofcode/comments/18nevo3/comment/keaidqr/?utm_source=reddit&utm_medium=web2x&context=3
//
// We find the number of available points for 3 step counts.

// I don't know if this is right, or why t1/t2 need to check parity 1, but oh well.

$t1 = countLocations(findLocations($map, $start, 65, true), 1);
$t2 = countLocations(findLocations($map, $start, 65 + (131), true));
$t3 = countLocations(findLocations($map, $start, 65 + (131 * 2), true), 1);

// For my input, this generates:
// [3751, 33531, 92991]

// These numbers can be put into wolfram alpha:
// https://www.wolframalpha.com/input?i=quadratic+fit+calculator&assumption=%7B%22F%22%2C+%22QuadraticFitCalculator%22%2C+%22data3x%22%7D+-%3E%22%7B0%2C+1%2C+2%7D%22&assumption=%7B%22F%22%2C+%22QuadraticFitCalculator%22%2C+%22data3y%22%7D+-%3E%22%7B3751%2C+33531%2C+92991%7D%22

// Which then gives us the answer.
// https://www.wolframalpha.com/input?i=3651+-+14740+x+%2B+14840+x%5E2%2C+x%3D202300

// However I don't actually know where 14740 and 14840 come from in this, and you know what, I don't care.

// Someone else posted this alternative way of getting the answer, given the 3 values above, so lets use that.

// This is also maths I don't care about.

$x = intval(26501365 / count($map[0]));
$delta = ($t3 - $t2) - ($t2 - $t1);
$step = $t2 - $t1;
$part2 = $t1;

for ($i = 0; $i < $x; $i++) {
	$part2 += $step;
	$step += $delta;
}

echo 'Part 2: ', $part2, "\n";
