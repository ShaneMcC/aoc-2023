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

					for ($y = $yStart; $y != $yEnd; $y += $yChange) {
						if ($newMap[$y][$x] == 'O') {
							$testY = $y;
							while (($newMap[$testY - $yChange][$x] ?? '#') == '.') {
								$newMap[$testY - $yChange][$x] = 'O';
								$newMap[$testY][$x] = '.';
								$testY -= $yChange;
							}
						}
					}
				}
			} else if ($direction == 'E' || $direction == 'W') {
				for ($y = 0; $y < count($map); $y++) {
					$xStart = ($direction == 'W') ? 0 : count($map[0]) - 1;
					$xEnd = ($direction == 'W') ? count($map[0]) : 0 - 1;
					$xChange = ($direction == 'W') ? 1 : -1;

					for ($x = $xStart; $x != $xEnd; $x += $xChange) {
						if ($newMap[$y][$x] == 'O') {
							$testX = $x;
							while (($newMap[$y][$testX - $xChange] ?? '#') == '.') {
								$newMap[$y][$testX - $xChange] = 'O';
								$newMap[$y][$testX] = '.';
								$testX -= $xChange;
							}
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
