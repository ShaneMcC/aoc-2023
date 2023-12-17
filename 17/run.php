#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$map = getInputMap();

	$directions = [];
	$directions[] = [0, -1, 'N'];
	$directions[] = [-1, 0, 'E'];
	$directions[] = [0, 1, 'S'];
	$directions[] = [1, 0, 'W'];

	function getPath($map, $start, $end, $minSteps = 1, $maxSteps = 3) {
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

			foreach ($directions as [$dX, $dY, $pD]) {
				if ($direction == 'N' && $pD == 'S') { continue; }
				else if ($direction == 'E' && $pD == 'W') { continue; }
				else if ($direction == 'W' && $pD == 'E') { continue; }
				else if ($direction == 'S' && $pD == 'N') { continue; }
				else if ($direction == $pD) { continue; }

				$moveCost = 0;
				$pX = $x;
				$pY = $y;
				for ($i = 1; $i <= $maxSteps; $i++) {
					$pX += $dX;
					$pY += $dY;

					if (isset($map[$pY][$pX])) {
						$moveCost += $map[$pY][$pX];

						if ($i < $minSteps) { continue; }

						$newCost = $loss + $moveCost;
						$queue->insert([$pX, $pY, $pD], -($newCost));
					}
				}
			}
		}

		return $costs;
	}

	$start = [0, 0];
	$end = [count($map[0]) - 1, count($map) - 1];

	$part1 = getPath($map, $start, $end);
	echo 'Part 1: ', $part1, "\n";

	$part2 = getPath($map, $start, $end, 4, 10);
	echo 'Part 2: ', $part2, "\n";
