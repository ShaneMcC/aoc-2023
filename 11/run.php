#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputMap();

	function expandGalaxy($map, $gap) {
		$newMap = [];

		$yVal = 0;
		foreach ($map as $row) {
			$newMap[$yVal] = $row;
			$acv = array_count_values($row);
			if (isset($acv['.']) && $acv['.'] == count($row)) {
				$yVal += $gap;
			}
			$yVal++;
		}

		$dupeCols = [];
		for ($i = 0; $i < count($map[0]); $i++) {
			$col = array_column($map, $i);
			$acv = array_count_values($col);
			if (isset($acv['.']) && $acv['.'] == count($col)) {
				$dupeCols[] = $i;
			}
		}


		foreach ($newMap as $y => $row) {
			$newRow = [];
			$incX = 0;
			foreach ($row as $x => $cell) {
				if (in_array($x, $dupeCols)) { $incX += $gap; }
				$newX = $x + $incX;
				$newRow[$newX] = $cell;
			}
			$newMap[$y] = $newRow;
		}

		return $newMap;
	}

	function getManhattanCount($input, $gap = 1) {
		$expanded = expandGalaxy($input, $gap);
		$galaxies = findCells($expanded, '#');

		// drawSparseMap($expanded, '@', true);

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
	echo 'Part 1: ', getManhattanCount($input, 1), "\n";
	echo 'Part 2: ', getManhattanCount($input, 1000000-1), "\n";
