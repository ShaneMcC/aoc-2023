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

		debugOut('Begin in ', $direction, "\n");

		$currentMap = $map;

		[$x,$y] = $start;
		$count = 0;
		$thisTile = $currentMap[$y][$x] ?? FALSE;
		$path = [];
		$path[] = $thisTile;

		while (true) {
			$count++;
			$dir = $directions[$direction];
			$y = $y + $dir[1];
			$x = $x + $dir[0];

			$nextTile = $currentMap[$y][$x] ?? FALSE;
			debugOut('Tile: ', $thisTile, ' => ', $direction, ' => ', $nextTile, ' => ');
			$thisTile = $nextTile;

			$path[] = $thisTile;

			if ($nextTile === False) {
				debugOut('Bad tile access attempt.');
				return [False, $path];
			} if ($nextTile == '.') {
				debugOut('Nothing', "\n");
				return [False, $path];
			} else if ($nextTile == 'S') {
				debugOut("\n");
				if (isDebug()) {
					drawMap($currentMap, true, 'Loop of length ' . $count);
				}
				return [True, $path];
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
					return [False, $path];
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
		[$result, $path] = checkLoops($map, $start, $direction);

		if ($result) { break; }
	}

	$part1 = floor(count($path) / 2);
	echo 'Part 1: ', $part1, "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
