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

	class ReverseSplPriorityQueue extends SplPriorityQueue {
		public function compare($a, $b) {
			if ($a === $b) return 0;
			return $a < $b ? 1 : -1;
		}
	}

	function buildGraph($map, $start) {
		global $directions;

		$queue = new SplPriorityQueue();
		$queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
		$queue->insert([$start[0], $start[1]], 0);

		// Build Graph
		$seen = [];
		while (!$queue->isEmpty()) {
			$q = $queue->extract();
			[$x, $y] = $q['data'];
			$cost = abs($q['priority']);

			if (isset($seen[$y][$x])) { continue; }
			$seen[$y][$x] = $cost;

			foreach ($directions as $pD => [$dX, $dY]) {
				$pX = $x + $dX;
				$pY = $y + $dY;
				if (($map[$pY][$pX] ?? '#') == '#') { continue; }
				if (isset($seen[$pY][$pX])) { continue; }

				if (!isset($graph[$pY][$pX])) { $graph[$pY][$pX] = ['options' => []]; }
				$graph[$y][$x]['options'][] = [[$pX, $pY], 1];

				$queue->insert([$pX, $pY], -($cost + 1));
			}
		}

		// return $graph;

		// Compress Graph First Pass
		// Remove nodes that only have a single destination and compress them up into the parent
		$queue = [$start];
		while (!empty($queue)) {
			[$x, $y] = array_shift($queue);
			if (!isset($graph[$y][$x]['options'])) {
				continue;
			}

			while (count($graph[$y][$x]['options']) == 1) {
				[[$fX, $fY], $fCost] = $graph[$y][$x]['options'][0];

				if (!isset($graph[$fY][$fX]['options'])) { break; }
				if (empty($graph[$fY][$fX]['options'])) { break; }
				if (count($graph[$fY][$fX]['options']) != 1) { break; }

				$graph[$y][$x]['options'] = [];
				foreach ($graph[$fY][$fX]['options'] as $o) {
					$graph[$y][$x]['options'][] = [$o[0], $o[1] + $fCost];
				}
				unset($graph[$fY][$fX]);
			}

			foreach ($graph[$y][$x]['options'] as $o) {
				$queue[] = $o[0];
			}
		}

		// Compress Graph Second Pass
		// This removes any 1-cost nodes and compresses them
		$queue = [$start];
		while (!empty($queue)) {
			[$x, $y] = array_shift($queue);
			if (!isset($graph[$y][$x]['options'])) {
				continue;
			}

			$newOptions = [];
			foreach (array_keys($graph[$y][$x]['options']) as $k) {
				[[$fX, $fY], $fCost] = $graph[$y][$x]['options'][$k];

				if ($fCost == 1) {
					foreach ($graph[$fY][$fX]['options'] as $opt) {
						$newOptions[] = [$opt[0], $opt[1] + $fCost];
					}
					unset($graph[$fY][$fX]);
				} else {
					$newOptions[] = $graph[$y][$x]['options'][$k];
				}
			}
			$graph[$y][$x]['options'] = $newOptions;

			foreach ($graph[$y][$x]['options'] as $o) {
				$queue[] = $o[0];
			}
		}

		// Add reverse nodes so that the graph is complete.
		foreach ($graph as $y => $xS) {
			foreach ($xS as $x => $thing) {
				foreach ($thing['options'] as $node) {
					[[$nX, $nY], $nCost] = $node;
					$graph[$nY][$nX]['options'][] = [[$x, $y], $nCost];
				}
			}
		}

		return $graph;
	}

	function findHikeMap($map, $start, $end) {
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

	function findHikeGraph($graph, $start, $end) {
		$queue = new ReverseSplPriorityQueue();
		$queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
		$queue->insert([$start[0], $start[1], [$start], []], 0);

		$costs = [];

		while (!$queue->isEmpty()) {
			$q = $queue->extract();
			[$x, $y, $path] = $q['data'];
			$cost = abs($q['priority']);

			if (isDebug() && [$x, $y] == $end) {
				if (!isset($costs[$y][$x]) || $cost > $costs[$y][$x][0]) {
					echo $cost, "\n";
				}
			}

			// if (isset($costs[$y][$x])) { continue; }
			if (!isset($costs[$y][$x]) || $cost > $costs[$y][$x][0]) {
				$costs[$y][$x] = [$cost, $path];
			}

			if (!isset($graph[$y][$x]['options'])) { continue; }

			foreach ($graph[$y][$x]['options'] as $b) {
				[[$bX, $bY], $bCost] = $b;

				if (in_array([$bX, $bY], $path)) { continue; }
				$newPath = array_merge($path, [[$bX, $bY]]);
				$queue->insert([$bX, $bY, $newPath], -($cost + $bCost));
			}
		}

		return $costs;
	}

	$costs = findHikeMap($map, $start, $end);
	[$cost, $path] = $costs[$end[1]][$end[0]];

	if (isDebug()) {
		$testMap = $map;
		foreach ($path as [$x, $y]) {
			$testMap[$y][$x] = 'O';
		}
		drawMap($testMap, true, 'Part 1');
	}

	$part1 = $cost;
	echo 'Part 1: ', $part1, "\n";

	$graph = buildGraph($map, $start, false);

	if (isDebug()) {
		echo 'Start: ', json_encode($start), "\n";
		echo 'End: ', json_encode($end), "\n";

		$testMap = $map;

		foreach ($graph as $y => $xS) {
			foreach ($xS as $x => $thing) {
				$testMap[$y][$x] = 'X';
				echo json_encode([$x, $y]), "\n";
				foreach ($thing['options'] as $node) {
					echo "\t", json_encode($node), "\n";
				}
			}
		}

		drawMap($testMap, true, 'Nodes');
	}

	$costs = findHikeGraph($graph, $start, $end);
	[$cost, $path] = $costs[$end[1]][$end[0]];

	if (isDebug()) {
		$testMap = $map;
		foreach ($path as $i => [$x, $y]) {
			$testMap[$y][$x] = chr(65+$i);
			echo chr(65+$i), ': ', json_encode([$x, $y]), "\n";
		}
		drawMap($testMap, true, 'Part 2');
	}

	$part2 = $cost;
	echo 'Part 2: ', $part2, "\n";
