#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$map = getInputMap();

	function tiltMap($map, $direction) {
			$newMap = $map;

			if ($direction == 'N' || $direction == 'S') {
				for ($x = 0; $x < count($map[0]); $x++) {
					$yStart = ($direction == 'N') ? 0 : count($map) - 1;
					$yEnd = ($direction == 'N') ? count($map) : 0 - 1;
					$yChange = ($direction == 'N') ? 1 : -1;
					$lastFreeY = $yStart;

					for ($y = $yStart; $y != $yEnd; $y += $yChange) {
						if ($newMap[$y][$x] == 'O' && $lastFreeY != $y) {
							$newMap[$y][$x] = '.';
							$newMap[$lastFreeY][$x] = 'O';
							$lastFreeY = $lastFreeY + $yChange;
						} else if ($newMap[$y][$x] != '.') {
							$lastFreeY = $y + $yChange;
						}
					}
				}
			} else if ($direction == 'E' || $direction == 'W') {
				for ($y = 0; $y < count($map); $y++) {
					$xStart = ($direction == 'W') ? 0 : count($map[0]) - 1;
					$xEnd = ($direction == 'W') ? count($map[0]) : 0 - 1;
					$xChange = ($direction == 'W') ? 1 : -1;
					$lastFreeX = $xStart;

					for ($x = $xStart; $x != $xEnd; $x += $xChange) {
						if ($newMap[$y][$x] == 'O' && $lastFreeX != $x) {
							$newMap[$y][$x] = '.';
							$newMap[$y][$lastFreeX] = 'O';
							$lastFreeX = $lastFreeX + $xChange;
						} else if ($newMap[$y][$x] != '.') {
							$lastFreeX = $x + $xChange;
						}
					}
				}
			}

		return $newMap;
	}

	function cycleMap($map) {
		$map = tiltMap($map, 'N');
		$map = tiltMap($map, 'W');
		$map = tiltMap($map, 'S');
		$map = tiltMap($map, 'E');
		return $map;
	}

	function getWeight($map) {
		$result = 0;
		for ($y = 0; $y < count($map); $y++) {
			$weight = count($map) - $y;
			$rocks = array_count_values($map[$y])['O'] ?? 0;
			$result += ($weight * $rocks);
		}
		return $result;
	}

	$tilted = tiltMap($map, 'N');
	$part1 = getWeight($tilted);
	echo 'Part 1: ', $part1, "\n";

	$cycled = $map;
	$seen = [];
	$seen[json_encode($cycled)] = 0;
	$cycleCount = 1000000000;
	for ($i = 1; $i <= $cycleCount; $i++) {
		$cycled = cycleMap($cycled);
		$key = json_encode($cycled);

		if (isset($seen[$key])) {
			$i = $cycleCount - (($cycleCount - $i) % ($i - $seen[$key]));
			$seen = [];
		} else {
			$seen[$key] = $i;
		}
	}

	$part2 = getWeight($cycled);
	echo 'Part 2: ', $part2, "\n";
