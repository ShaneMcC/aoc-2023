#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$maps = [];
	foreach (getInputLineGroups() as $lg) {
		$map = [];
		foreach ($lg as $l) { $map[] = str_split($l); }
		$maps[] = $map;
	}

	function getSymmetry($map, $type, $ignore = []) {

		if ($type == 'row') {
			$typelen = count($map[0]);
			$typecount = count($map);
		} else {
			$typelen = count($map);
			$typecount = count($map[0]);
		}

		$symmetryPoints = array_fill(1, $typelen + 1, true);
		if (is_array($ignore) && isset($ignore[$type]) && is_array($ignore[$type])) {
			debugOut('Ignoring ', $type, ' => ', implode(', ', $ignore[$type]), "\n");
			foreach ($ignore[$type] as $i) { unset($symmetryPoints[$i]); }
		}

		for ($t = 0; $t < $typecount; $t++) {
			if ($type == 'row') {
				$line = $map[$t];
			} else {
				$line = array_column($map, $t);
			}

			debugOut('Looking at ', $type, ': ', implode('', $line), "\n");
			foreach (array_keys($symmetryPoints) as $i) {
				$beforeCount = $i;
				$afterCount = $typelen - $i;
				$len = min($beforeCount, $afterCount);

				debugOut($i, ': (Size: ' . $beforeCount . '/' . $afterCount . ' = ' . $len . ')');

				$before = array_slice($line, $i - $len, $len);
				$after = array_reverse(array_slice($line, $i, $len));

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

		return array_keys($symmetryPoints);
	}

	function getSymmetryCount($n, $map, $ignore = []) {
		$colSymmetry = getSymmetry($map, 'row', $ignore);
		if (!empty($colSymmetry)) {
			debugOut('Map ', $n, ' col symmetry: ', $colSymmetry[0], "\n");
			return $colSymmetry[0];
		}

		$rowSymmetry = getSymmetry($map, 'col', $ignore);
		if (!empty($rowSymmetry)) {
			debugOut('Map ', $n, ' row symmetry: ', $rowSymmetry[0], "\n");
			return $rowSymmetry[0] * 100;
		}

		return FALSE;
	}

	$part1 = 0;
	foreach ($maps as $n => $map) {
		$part1 += getSymmetryCount($n, $map);
	}
	echo 'Part 1: ', $part1, "\n";

	$part2 = 0;
	foreach ($maps as $n => $map) {
		$ignore = ['row' => getSymmetry($map, 'row'), 'col' => getSymmetry($map, 'col')];

		foreach (cells($map) as [$x, $y, $cell]) {
			$newCell = ($cell == '.') ? '#' : '.';

			$map[$y][$x] = $newCell;

			$count = getSymmetryCount($n, $map, $ignore);
			$map[$y][$x] = $cell;

			if ($count != FALSE) {
				$part2 += $count;
				break;
			}
		}
	}
	echo 'Part 2: ', $part2, "\n";
