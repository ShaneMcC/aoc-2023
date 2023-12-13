#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$maps = [];
	foreach (getInputLineGroups() as $lg) {
		$map = [];
		foreach ($lg as $l) { $map[] = str_split($l); }
		$maps[] = $map;
	}

	function getSymmetry($map, $type) {

		if ($type == 'row') {
			$typelen = count($map[0]);
			$typecount = count($map);
		} else {
			$typelen = count($map);
			$typecount = count($map[0]);
		}

		$symmetryPoints = array_fill(0, $typelen, true);
		for ($t = 0; $t < $typecount; $t++) {
			if ($type == 'row') {
				$line = $map[$t];
			} else {
				$line = array_column($map, $t);
			}

			debugOut('Looking at ', $type, ': ', implode('', $line), "\n");
			foreach (array_keys($symmetryPoints) as $i) {
				$beforeCount = $i + 1;
				$afterCount = $typelen - $i - 1;
				$len = min($beforeCount, $afterCount);

				debugOut($i, ': (Size: ' . $beforeCount . '/' . $afterCount . ' = ' . $len . ')');

				$before = array_slice($line, $i + 1 - $len, $len);
				$after = array_reverse(array_slice($line, $i + 1, $len));

				debugOut("\t", '[' . implode('', $before) . '] vs [' . implode('', $after) . '] => ');
				if ($len > 0 && $before == $after) {
					debugOut("Same!\n");
				} else {
					debugOut("Different.\n");
					unset($symmetryPoints[$i]);
				}
			}
			debugOut("\n");

			if (empty($symmetryPoints)) { break; }
		}

		if (!empty($symmetryPoints)) {
			return array_keys($symmetryPoints)[0] + 1;
		}

		return FALSE;
	}

	$part1 = 0;
	foreach ($maps as $n => $map) {
		$colSymmetry = getSymmetry($map, 'row');
		if ($colSymmetry !== FALSE) {
			debugOut('Map ', $n, ' col symmetry: ', $colSymmetry, "\n");
			$part1 += $colSymmetry;
		} else {
			$rowSymmetry = getSymmetry($map, 'col');
			debugOut('Map ', $n, ' row symmetry: ', $rowSymmetry, "\n");
			$part1 += $rowSymmetry * 100;
		}
	}

	echo 'Part 1: ', $part1, "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
