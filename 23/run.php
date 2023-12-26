#!/usr/bin/php
<?php
	$__CLI['long'] = ['part1', 'part2', 'graph'];
	$__CLI['extrahelp'][] = '      --part1              Only run part 1.';
	$__CLI['extrahelp'][] = '      --part2              Only run part 2.';
	$__CLI['extrahelp'][] = '      --graph              Output graph for part 2.';

	require_once(dirname(__FILE__) . '/../common/common.php');

	$doPart1 = isset($__CLIOPTS['part1']) || (!isset($__CLIOPTS['part1']) && !isset($__CLIOPTS['part2']));
	$doPart2 = isset($__CLIOPTS['part2']) || (!isset($__CLIOPTS['part1']) && !isset($__CLIOPTS['part2']));
	$generateGraph = isset($__CLIOPTS['graph']);

	$map = getInputMap();
	$start = [1, 0];
	$end = [count($map[0]) - 2, count($map) - 1];

	$directions = [];
	$directions['^'] = [0, -1];
	$directions['<'] = [-1, 0];
	$directions['v'] = [0, 1];
	$directions['>'] = [1, 0];

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

		// Compress Graph Third Pass
		// Add reverse nodes so that the graph is complete.
		foreach ($graph as $y => $xS) {
			foreach ($xS as $x => $thing) {
				foreach ($thing['options'] as $node) {
					[[$nX, $nY], $nCost] = $node;
					$graph[$nY][$nX]['options'][] = [[$x, $y], $nCost];
				}
			}
		}

		// Compress Graph Fourth Pass
		// Remove nodes that only have a single pair of connections.
		$removeNodes = [];
		foreach ($graph as $y => $xS) {
			foreach ($xS as $x => $thing) {
				if (count($thing['options']) == 2) {
					$removeNodes[] = [$x, $y];
				}
			}
		}

		foreach ($removeNodes as $rn) {
			[$rX, $rY] = $rn;
			[$opt1, $opt2] = $graph[$rY][$rX]['options'];

			[[$x1, $y1], $cost1] = $opt1;
			[[$x2, $y2], $cost2] = $opt2;

			$graph[$y1][$x1]['options'] = array_filter($graph[$y1][$x1]['options'], fn($i) => ($i[0] != $rn));
			$graph[$y2][$x2]['options'] = array_filter($graph[$y2][$x2]['options'], fn($i) => ($i[0] != $rn));

			$graph[$y1][$x1]['options'][] = [[$x2, $y2], $cost1 + $cost2];
			$graph[$y2][$x2]['options'][] = [[$x1, $y1], $cost1 + $cost2];

			unset($graph[$rY][$rX]);
		}

		return $graph;
	}

	function findHikeMap($map, $start, $end) {
		global $directions;

		$queue = new SplQueue();
		$queue->push([$start[0], $start[1], [$start], 0]);

		$costs = [];

		while (!$queue->isEmpty()) {
			$q = $queue->pop();
			[$x, $y, $path, $cost] = $q;

			if (isDebug() && [$x, $y] == $end) {
				if (!isset($costs[$y][$x]) || $cost > $costs[$y][$x][0]) {
					echo $cost, "\n";
				}
			}

			if (!isset($costs[$y][$x]) || $cost > $costs[$y][$x][0]) {
				$costs[$y][$x] = [$cost, $path];
			}

			foreach ($directions as $pD => [$dX, $dY]) {
				if ($map[$y][$x] == '.' || $map[$y][$x] == $pD) {
					$pX = $x + $dX;
					$pY = $y + $dY;
					if (($map[$pY][$pX] ?? '#') == '#') { continue; }
					if (in_array([$pX, $pY], $path)) { continue; }

					$newPath = $path;
					$newPath[] = [$pX, $pY];

					$queue->push([$pX, $pY, $newPath, ($cost + 1)]);
				}
			}
		}

		return $costs;
	}

	function findHikeGraph($graph, $start, $end) {
		$queue = new SplQueue();
		$queue->push([$start[0], $start[1], [$start], 0]);

		$costs = [];

		while (!$queue->isEmpty()) {
			$q = $queue->pop();
			[$x, $y, $path, $cost] = $q;

			if (isDebug() && [$x, $y] == $end) {
				if (!isset($costs[$y][$x]) || $cost > $costs[$y][$x][0]) {
					echo $cost, "\n";
				}
			}

			if (!isset($costs[$y][$x]) || $cost > $costs[$y][$x][0]) {
				$costs[$y][$x] = [$cost, $path];
			}

			if (!isset($graph[$y][$x]['options'])) { continue; }

			foreach ($graph[$y][$x]['options'] as $b) {
				[[$bX, $bY], $bCost] = $b;

				if (in_array([$bX, $bY], $path)) { continue; }
				$newPath = array_merge($path, [[$bX, $bY]]);
				$queue->push([$bX, $bY, $newPath, ($cost + $bCost)]);
			}
		}

		return $costs;
	}

	if ($doPart1) {
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
	}

	if ($doPart2) {
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

		if ($generateGraph) {
			require_once(dirname(__FILE__) . '/../common/graphViz.php');

			$g = new graphViz\Graph(['strict' => true, 'layout' => 'neato']);

			foreach ($graph as $y => $xS) {
				foreach ($xS as $x => $thing) {
					$testMap[$y][$x] = 'X';
					$thisStr = json_encode([$x, $y]);
					foreach ($thing['options'] as $node) {
						$nodeStr = json_encode($node[0]);
						$weight = $node[1];

						$n1 = $g->getNodeByName($thisStr, true);
						$n2 = $g->getNodeByName($nodeStr, true);

						$g->addEdge(new graphViz\UndirectedEdge($n1, $n2, ["label" => $weight]));
					}
				}
			}

			$g->generate(__DIR__ . '/graph.svg');
			die();
		}

		$realStart = $start;
		$startOpt = $graph[$realStart[1]][$realStart[0]]['options'][0];
		$start = $startOpt[0];

		$realEnd = $end;
		$endOpt = $graph[$realEnd[1]][$realEnd[0]]['options'][0];
		$end = $endOpt[0];

		// Remove real start/end from graph.
		$graph[$start[1]][$start[0]]['options'] = array_filter($graph[$start[1]][$start[0]]['options'], fn($i) => ($i[0] != $realStart));
		$graph[$end[1]][$end[0]]['options'] = array_filter($graph[$end[1]][$end[0]]['options'], fn($i) => ($i[0] != $realEnd));
		unset($graph[$realStart[1]][$realStart[0]]);
		unset($graph[$realEnd[1]][$realEnd[0]]);

		$costs = findHikeGraph($graph, $start, $end);
		[$cost, $path] = $costs[$end[1]][$end[0]];

		$cost += $endOpt[1];
		$cost += $startOpt[1];

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
	}
