#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputSparseMap();

	function expandGalaxy($map, $gapSize = 1) {
		$gapSize = max(0, $gapSize - 1);
		[$minX, $minY, $maxX, $maxY] = getBoundingBox($map);
		$newMap = [];

		$dupeCols = [];
		for ($x = $minX; $x < $maxX; $x++) {
			if (empty(array_column($map, $x))) {
				$dupeCols[$x] = true;
			}
		}

		$yVal = $minY;
		for ($y = $minY; $y <= $maxY; $y++) {
			if (isset($map[$y])) {
				$xVal = $minX;
				for ($x = $minX; $x <= $maxX; $x++) {
					if (isset($dupeCols[$x])) {
						$xVal += $gapSize;
					} else {
						$cell = $map[$y][$x] ?? null;
						if ($cell != null) {
							$newMap[$yVal][$xVal] = $cell;
						}
					}
					$xVal++;
				}
			} else {
				$yVal += $gapSize;
			}
			$yVal++;
		}

		return $newMap;
	}

	function getManhattanCount($input, $gapSize = 2) {
		$expanded = expandGalaxy($input, $gapSize);
		$galaxies = findCells($expanded, '#');

		$result = 0;
		foreach ($galaxies as $g1num => $g1) {
			foreach ($galaxies as $g2num => $g2) {
				if ($g1num > $g2num) {
					$result += manhattan($g1[0], $g1[1], $g2[0], $g2[1]);
				}
			}
		}

		return $result;
	}
	echo 'Part 1: ', getManhattanCount($input, 2), "\n";
	echo 'Part 2: ', getManhattanCount($input, 1000000), "\n";
