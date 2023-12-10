#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');

	if (isDebug()) {
		$replacements = [
						'|' => '│',
						'-' => '━',
						'7' => '┑',
						'J' => '┙',
						'L' => '┕',
						'F' => '┍',
						];
		$input = str_replace(array_keys($replacements), array_values($replacements), getInputContent());
		$map = [];
		foreach (explode("\n", $input) as $line) {
			if (!empty($line)) {
				$map[] = mb_str_split($line);
			}
		}
	} else {
		$map = getInputMap();
	}

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

		$currentMap[$y][$x] = "\033[1;33m" . $currentMap[$y][$x] . "\033[0m";
		while (true) {
			$count++;
			$dir = $directions[$direction];
			$y = $y + $dir[1];
			$x = $x + $dir[0];

			$nextTile = $map[$y][$x] ?? FALSE;
			$currentMap[$y][$x] = "\033[1;33m" . $currentMap[$y][$x] . "\033[0m";
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
				if ($nextTile == '|' || $nextTile == '-' || $nextTile == '│' || $nextTile == '━') {
					$newDirection = $direction;
				} else if ($nextTile == '7' || $nextTile == '┑') {
					if ($direction == 'right') { $newDirection = 'down'; }
					else if ($direction == 'up') { $newDirection = 'left'; }
				} else if ($nextTile == 'J' || $nextTile == '┙') {
					if ($direction == 'right') { $newDirection = 'up'; }
					else if ($direction == 'down') { $newDirection = 'left'; }
				} else if ($nextTile == 'L' || $nextTile == '┕') {
					if ($direction == 'left') { $newDirection = 'up'; }
					else if ($direction == 'down') { $newDirection = 'right'; }
				} else if ($nextTile == 'F' || $nextTile == '┍') {
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

	$count = 0;
	$outMap = [];
	foreach ($map as $y => $row) {
		$inside = false;
		$thisRow = [];
		foreach ($row as $x => $cell) {
			if ($currentMap[$y][$x] != $cell) {
				if (in_array($cell, ['|', 'J', 'L', '│', '┙', '┕']) || ($cell == 'S' && $direction == 'up')) {
					$inside = !$inside;
				}
				$thisRow[] = $currentMap[$y][$x];
			} else {
				if ($inside) {
					$count++;
					$thisRow[] = "\033[0;32m" . 'I' . "\033[0m";
				} else {
					$thisRow[] = "\033[1;31m" . 'O' . "\033[0m";
				}
			}
		}
		$outMap[] = $thisRow;
	}

	if (isDebug()) {
		drawMap($outMap, true, 'Part 2 - ' . $count);
	}

	echo 'Part 2: ', $count, "\n";
