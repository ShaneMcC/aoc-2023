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
		$colCount = count($map[0]);
		$rowCount = count($map);
		$typelen = ($type == 'row') ? $colCount : $rowCount;
		$typecount = ($type == 'row') ? $rowCount : $colCount;

		$symmetryPoints = array_fill(1, $typelen + 1, true);
		if (is_array($ignore) && isset($ignore[$type]) && is_array($ignore[$type])) {
			if (isDebug()) { echo "\t", 'Ignoring ', $type, ' => ', implode(', ', $ignore[$type]), "\n"; }
			foreach ($ignore[$type] as $i) { unset($symmetryPoints[$i]); }
		}

		for ($t = 0; $t < $typecount; $t++) {
			$line = ($type == 'row') ? $map[$t] : array_column($map, $t);

			if (isDebug()) { echo "\t\t", 'Looking at ', $type, ' ', ($t + 1), ': ', implode('', $line), "\n"; }
			foreach (array_keys($symmetryPoints) as $i) {
				$beforeCount = $i;
				$afterCount = $typelen - $i;
				$len = min($beforeCount, $afterCount);

				if (isDebug()) { echo "\t\t\t", $i, ': (Size: ' . $beforeCount . '/' . $afterCount . ' = ' . $len . ')'; }

				$before = array_slice($line, $i - $len, $len);
				$after = array_reverse(array_slice($line, $i, $len));

				if (isDebug()) { echo "\t\t\t\t", '[' . implode('', $before) . '] vs [' . implode('', $after) . '] => '; }
				if ($len > 0 && $before == $after) {
					if (isDebug()) { echo "Same!\n"; }
				} else {
					if (isDebug()) { echo "Different.\n"; }
					unset($symmetryPoints[$i]);
				}
			}
			if (isDebug()) { echo "\n"; }

			if (empty($symmetryPoints)) { break; }
		}

		return array_keys($symmetryPoints);
	}

	function getSymmetryCount($n, $map, $ignore = []) {
		if (isDebug()) { echo 'Map ', $n, ': ', "\n"; }
		$colSymmetry = getSymmetry($map, 'row', $ignore);
		if (!empty($colSymmetry)) {
			if (isDebug()) { echo "\t", 'Map ', $n, ' col symmetry: ', $colSymmetry[0], "\n"; }
			return $colSymmetry[0];
		} else {
			if (isDebug()) { echo "\t", 'Map ', $n, ' has no col symmetry', "\n"; }
		}

		$rowSymmetry = getSymmetry($map, 'col', $ignore);
		if (!empty($rowSymmetry)) {
			if (isDebug()) { echo "\t", 'Map ', $n, ' row symmetry: ', $rowSymmetry[0], "\n"; }
			return $rowSymmetry[0] * 100;
		} else {
			if (isDebug()) { echo "\t", 'Map ', $n, ' has no col symmetry', "\n"; }
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
