#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputMap();

	function expandGalaxy($map) {
		$newMap = [];

		foreach ($map as $row) {
			$newMap[] = $row;

			$acv = array_count_values($row);
			if (isset($acv['.']) && $acv['.'] == count($row)) { $newMap[] = $row; }
		}

		$dupeCols = [];
		for ($i = 0; $i < count($map[0]); $i++) {
			$col = array_column($map, $i);
			$acv = array_count_values($col);
			if (isset($acv['.']) && $acv['.'] == count($col)) {
				$dupeCols[] = $i;
			}
		}

		for ($y = 0; $y < count($newMap); $y++) {
			foreach ($dupeCols as $i => $col) {
				array_splice($newMap[$y], $col + $i, 0, ['.']);
			}
		}

		return $newMap;
	}

	$expanded = expandGalaxy($input);
	$galaxies = findCells($expanded, '#');

	$part1 = 0;
	foreach ($galaxies as $g1num => $g1) {
		foreach ($galaxies as $g2num => $g2) {
			if ($g1num > $g2num) {
				$part1 += manhattan($g1[0], $g1[1], $g2[0], $g2[1]);
			}
		}
	}

	echo 'Part 1: ', $part1, "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
