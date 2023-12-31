#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$map = getInputMap();

	$directions = [];
	$directions['N'] = [0, -1, 'S'];
	$directions['E'] = [-1, 0, 'W'];
	$directions['S'] = [0, 1, 'N'];
	$directions['W'] = [1, 0, 'E'];

	function getPathCost($map, $start, $end, $minSteps = 1, $maxSteps = 3) {
		global $directions;

		$queue = new SPLPriorityQueue();
		$queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
		$queue->insert([$start[0], $start[1], ''], 0);

		$costs = [];

		while (!$queue->isEmpty()) {
			$q = $queue->extract();
			[$x, $y, $direction] = $q['data'];
			$loss = abs($q['priority']);

			if ([$x, $y] == $end) { return $loss; }

			if (isset($costs[$y][$x][$direction])) { continue; }
			$costs[$y][$x][$direction] = $loss;

			foreach ($directions as $pD => [$dX, $dY, $oD]) {
				if ($direction == $pD) { continue; }
				else if ($direction == $oD) { continue; }

				$moveCost = 0;
				$pX = $x;
				$pY = $y;
				for ($i = 1; $i <= $maxSteps; $i++) {
					$pX += $dX;
					$pY += $dY;
					if (!isset($map[$pY][$pX])) { continue; }

					$moveCost += $map[$pY][$pX];
					if ($i < $minSteps) { continue; }

					$queue->insert([$pX, $pY, $pD], -($loss + $moveCost));
				}
			}
		}

		return PHP_INT_MAX;
	}

	$start = [0, 0];
	$end = [count($map[0]) - 1, count($map) - 1];

	$part1 = getPathCost($map, $start, $end);
	echo 'Part 1: ', $part1, "\n";

	$part2 = getPathCost($map, $start, $end, 4, 10);
	echo 'Part 2: ', $part2, "\n";
