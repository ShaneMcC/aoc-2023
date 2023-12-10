#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$map = getInputMap();

	function findStart($map) {
		foreach (cells($map) as [$x,$y,$cell]) {
			if ($cell == 'S') {
				return [$x, $y];
			}
		}

		die('No Start');
	}

	$directions = [];
	$directions['up'] = [0, -1];
	$directions['down'] = [0, 1];
	$directions['left'] = [-1, 0];
	$directions['right'] = [1, 0];

	$start = findStart($map);
	function checkLoops($map, $start, $direction) {
		global $directions;

		$startDir = $direction;

		debugOut('Begin in ', $direction, "\n");

		$currentMap = $map;

		[$x,$y] = $start;
		$count = 0;
		$thisTile = $currentMap[$y][$x] ?? FALSE;
		$path = [];
		$path[] = [$x, $y];

		$currentMap[$y][$x] = '#';
		while (true) {
			$count++;
			$dir = $directions[$direction];
			$y = $y + $dir[1];
			$x = $x + $dir[0];

			$nextTile = $map[$y][$x] ?? FALSE;
			$currentMap[$y][$x] = '#';
			debugOut('Tile: ', $thisTile, ' => ', $direction, ' => ', $nextTile, ' => ');
			$thisTile = $nextTile;

			$path[] = [$x, $y];

			if ($nextTile === False) {
				debugOut('Bad tile access attempt.');
				return [False, $path, $currentMap];
			} if ($nextTile == '.') {
				debugOut('Nothing', "\n");
				return [False, $path, $currentMap];
			} else if ($nextTile == 'S') {
				debugOut('Loop of length ' . $count . ' from ' . $startDir, "\n");
				if (isDebug()) {
					drawMap($currentMap, true, 'Loop of length ' . $count . ' from ' . $startDir);
				}
				return [True, $path, $currentMap];
			} else {
				$newDirection = FALSE;
				if ($nextTile == '|' || $nextTile == '-') {
					$newDirection = $direction;
				} else if ($nextTile == '7') {
					if ($direction == 'right') { $newDirection = 'down'; }
					else if ($direction == 'up') { $newDirection = 'left'; }
				} else if ($nextTile == 'J') {
					if ($direction == 'right') { $newDirection = 'up'; }
					else if ($direction == 'down') { $newDirection = 'left'; }
				} else if ($nextTile == 'L') {
					if ($direction == 'left') { $newDirection = 'up'; }
					else if ($direction == 'down') { $newDirection = 'right'; }
				} else if ($nextTile == 'F') {
					if ($direction == 'left') { $newDirection = 'down'; }
					else if ($direction == 'up') { $newDirection = 'right'; }
				}

				if ($newDirection === False) {
					debugOut(' Dead end.', "\n");
					return [False, $path, $currentMap];
				} else {
					debugOut(" change to: ", $newDirection, "\n");
					$direction = $newDirection;
				}
			}
		}
	}

	if (isDebug()) {
		drawMap($map, true);
	}

	foreach (array_keys($directions) as $direction) {
		[$result, $path, $currentMap] = checkLoops($map, $start, $direction);
		array_pop($path);
		if ($result) { break; }
	}

	$part1 = count($path) / 2;
	echo 'Part 1: ', $part1, "\n";

	if (isDebug()) {
		drawMap($currentMap, true, 'Blocked Out');
	}

	$count = 0;
	foreach ($map as $y => $row) {
		$inside = false;
		debugOut('O');
		foreach ($row as $x => $cell) {
			if ($currentMap[$y][$x] == '#') {
				if (in_array($cell, ['|', 'J', 'L']) || ($cell == 'S' && $direction == 'up')) {
					$inside = !$inside;
				}
				debugOut('#');
			} else {
				if ($inside) {
					$count++;
					debugOut('I');
				} else {
					debugOut('O');
				}
			}
		}
		debugOut("\n");
	}

	echo 'Part 2: ', $count, "\n";
