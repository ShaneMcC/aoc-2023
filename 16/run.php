#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$map = getInputMap();

	function getEnergised($map, $start = [-1, 0, 'right']) {
		$ends = [];

		$ends[] = $start;
		$visited = [];

		while (!empty($ends)) {
			[$x, $y, $direction] = array_pop($ends);

			if ($direction == 'right') { $x += 1; }
			else if ($direction == 'left') { $x -= 1; }
			else if ($direction == 'down') { $y += 1; }
			else if ($direction == 'up') { $y -= 1; }

			$cell = $map[$y][$x] ?? FALSE;
			if ($cell !== FALSE) {
				if (isset($visited["{$x},{$y}"][$direction])) {
					continue;
				} else {
					$visited["{$x},{$y}"][$direction] = true;
				}

				$direction2 = FALSE;
				if ($cell == '\\') {
					if ($direction == 'left') { $direction = 'up'; }
					else if ($direction == 'right') { $direction = 'down'; }
					else if ($direction == 'down') { $direction = 'right'; }
					else if ($direction == 'up') { $direction = 'left'; }
				} else if ($cell == '/') {
					if ($direction == 'left') { $direction = 'down'; }
					else if ($direction == 'right') { $direction = 'up'; }
					else if ($direction == 'down') { $direction = 'left'; }
					else if ($direction == 'up') { $direction = 'right'; }
				} else if ($cell == '-') {
					if ($direction == 'down' || $direction == 'up') {
						$direction = 'right';
						$direction2 = 'left';
					}
				} else if ($cell == '|') {
					if ($direction == 'left' || $direction == 'right') {
						$direction = 'down';
						$direction2 = 'up';
					}
				}

				$ends[] = [$x, $y, $direction];
				if ($direction2 !== FALSE) { $ends[] = [$x, $y, $direction2]; }
			}
		}

		return $visited;
	}

	if (isDebug()) {
		drawMap($map, true, 'Original');
	}
	$energised = getEnergised($map);
	if (isDebug()) {
		foreach ($energised as $e => $_) {
			[$x, $y] = explode(",", $e);
			if ($map[$y][$x] == '.') {
				$map[$y][$x] = '#';
			}
		}
		drawMap($map, true, 'Energised');
	}

	$part1 = count($energised);;
	echo 'Part 1: ', $part1, "\n";

	$starts = [];
	for ($y = 0; $y < count($map); $y++) {
		$starts[] = [-1, $y, 'right'];
		$starts[] = [count($map[0]), $y, 'left'];
	}

	for ($x = 0; $x < count($map[0]); $x++) {
		$starts[] = [$x, -1, 'down'];
		$starts[] = [$x, count($map), 'up'];
	}

	$part2 = 0;
	foreach ($starts as $start) {
		$energised = getEnergised($map, $start);
		if (count($energised) > $part2) {
			$part2 = count($energised);
		}
	}

	echo 'Part 2: ', $part2, "\n";
