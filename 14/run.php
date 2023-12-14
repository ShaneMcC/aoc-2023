#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$map = getInputMap();

	function tiltMap($map) {
			$newMap = $map;
			for ($x = 0; $x < count($map[0]); $x++) {
				for ($y = 0; $y < count($map); $y++) {
					if ($newMap[$y][$x] == 'O' && ($newMap[$y - 1][$x] ?? '#') == '.') {
						$newMap[$y - 1][$x] = 'O';
						$newMap[$y][$x] = '.';
						$y -= 2;
					}
				}
			}

		return $newMap;
	}

	if (isDebug()) { drawMap($map, true, 'Map'); }
	$map = tiltMap($map);
	if (isDebug()) { drawMap($map, true, 'Final Tilted Map'); }

	$part1 = 0;
	for ($y = 0; $y < count($map); $y++) {
		$weight = count($map) - $y;
		$rocks = array_count_values($map[$y])['O'] ?? 0;
		$part1 += ($weight * $rocks);
	}

	echo 'Part 1: ', $part1, "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
