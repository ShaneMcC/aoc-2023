#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$map = getInputMap();
	$start = [1, 0];
	$end = [count($map[0]) - 2, count($map) - 1];

	$directions = [];
	$directions['^'] = [0, -1];
	$directions['<'] = [-1, 0];
	$directions['v'] = [0, 1];
	$directions['>'] = [1, 0];

	function findHike($map, $start, $end) {
		global $directions;

		$queue = new SPLPriorityQueue();
		$queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
		$queue->insert([$start[0], $start[1], [$start]], 0);

		$costs = [];

		while (!$queue->isEmpty()) {
			$q = $queue->extract();
			[$x, $y, $path] = $q['data'];
			$cost = abs($q['priority']);

			// if (isset($costs[$y][$x])) { continue; }
			$costs[$y][$x] = [$cost, $path];

			// if ([$x, $y] == $end) { return $costs; }

			foreach ($directions as $pD => [$dX, $dY]) {
				if ($map[$y][$x] == '.' || $map[$y][$x] == $pD) {
					$pX = $x + $dX;
					$pY = $y + $dY;
					if (($map[$pY][$pX] ?? '#') == '#') { continue; }
					if (in_array([$pX, $pY], $path)) { continue; }

					$newPath = $path;
					$newPath[] = [$pX, $pY];

					$queue->insert([$pX, $pY, $newPath], -($cost + 1));
				}
			}
		}

		return $costs;
	}

	$costs = findHike($map, $start, $end);

	[$cost, $path] = $costs[$end[1]][$end[0]];

	if (isDebug()) {
		$testMap = $map;
		foreach ($path as [$x, $y]) {
			$testMap[$y][$x] = 'O';
		}
		drawMap($testMap);
	}

	$part1 = $cost;
	echo 'Part 1: ', $part1, "\n";

	// $part2 = 0;
	// echo 'Part 2: ', $part2, "\n";
