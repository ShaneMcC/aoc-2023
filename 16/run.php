#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$map = getInputMap();

	$directions = [];
	$directions['right'] = [1, 0];
	$directions['left'] = [-1, 0];
	$directions['down'] = [0, 1];
	$directions['up'] = [0, -1];

	$tiles = [];

	$tiles['\\'] = ['left' => ['up'],
	                'right' => ['down'],
	                'down' => ['right'],
	                'up' => ['left'],
	               ];
	$tiles['/'] = ['left' => ['down'],
	               'right' => ['up'],
	               'down' => ['left'],
	               'up' => ['right'],
	              ];
	$tiles['-'] = ['down' => ['right', 'left'], 'up' => ['right', 'left']];
	$tiles['|'] = ['right' => ['up', 'down'], 'left' => ['up', 'down']];

	function getEnergised($map, $start = [-1, 0, 'right']) {
		global $directions, $tiles;
		$ends = [];

		$ends[] = $start;
		$visited = [];
		$exits = [];
		$exits["{$start[0]},{$start[1]}"] = true;

		if (isDebug()) { drawMap($map, true, 'Original'); }

		while (!empty($ends)) {
			if (isDebug()) {
				// Shift makes a prettier drawing, but is slower.
				[$x, $y, $direction] = array_shift($ends);
				drawEnergised($map, $visited, true);
			} else {
				[$x, $y, $direction] = array_pop($ends);
			}

			$x += $directions[$direction][0];
			$y += $directions[$direction][1];

			$cell = $map[$y][$x] ?? FALSE;
			if ($cell !== FALSE) {
				if (isset($visited["{$x},{$y}"][$direction])) {
					continue;
				} else {
					$visited["{$x},{$y}"][$direction] = true;
				}

				foreach ($tiles[$cell][$direction] ?? [$direction] as $newDirection) {
					$ends[] = [$x, $y, $newDirection];
				}
			} else {
				$exits["{$x},{$y}"] = true;
			}
		}

		return [$visited, $exits];
	}

	[$energised, $exits] = getEnergised($map);
	$part1 = count($energised);
	echo 'Part 1: ', $part1, "\n";
	if (isDebug()) { die(); }

	$starts = [];
	for ($y = 0; $y < count($map); $y++) {
		$starts[] = [-1, $y, 'right'];
		$starts[] = [count($map[0]), $y, 'left'];
	}

	for ($x = 0; $x < count($map[0]); $x++) {
		$starts[] = [$x, -1, 'down'];
		$starts[] = [$x, count($map), 'up'];
	}

	$part2 = $part1;
	foreach ($starts as $start) {
		if (isset($exits["{$start[0]},{$start[1]}"])) { continue; }

		[$energised, $newExits] = getEnergised($map, $start);
		$exits = array_merge($exits, $newExits);
		if (count($energised) > $part2) {
			$part2 = count($energised);
		}
	}

	echo 'Part 2: ', $part2, "\n";
