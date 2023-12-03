#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputMap();

	function getSymbols($input, $startx, $starty, $endx, $endy) {
		$symbols = [];

		foreach (yieldXY($startx, $starty, $endx, $endy, true) as $x => $y) {
			$val = ($input[$y][$x] ?? '.');
			if (!is_numeric($val) && $val != '.') {
				if (!isset($symbols[$val])) { $symbols[$val] = []; }
				$symbols[$val][] = $x . ',' . $y;
			}
		}

		return $symbols;
	}

	function getNumberList($input) {
		$numbers = [];
		foreach ($input as $y => $row) {
			$current = null;
			$startx = null;
			foreach ($row as $x => $col) {
				if ($current == null && is_numeric($col)) {
					$startx = $x;
					$current = $col;
				} else if (is_numeric($col)) {
					$current .= $col;
				} else if ($current != null) {
					$numbers[] = [$current, $startx, $y];
					$current = null;
				}
			}

			if ($current != null) {
				$numbers[] = [$current, $startx, $y];
				$current = null;
			}
		}

		return $numbers;
	}

	$numbers = getNumberList($input);
	$part1 = 0;
	$gears = [];
	foreach ($numbers as $number) {
		[$number, $x, $y] = $number;
		$endx = $x + (strlen($number) - 1);

		$symbols = getSymbols($input, $x - 1, $y - 1, $endx + 1, $y + 1);
		if (!empty($symbols)) {
			$part1 += $number;
			foreach ($symbols as $symbol => $locations) {
				if ($symbol == '*') {
					foreach ($locations as $location) {
						if (!isset($gears[$location])) { $gears[$location] = []; }
						$gears[$location][] = $number;
					}
				}
			}
		}

		if (isDebug()) {
			debugOut('Number: ', $number, ' => ');
			debugOut((!empty($symbols) ? "VALID (" . implode('', array_keys($symbols)) . ")" : "NOT VALID"), "\n");
		}
	}

	echo 'Part 1: ', $part1, "\n";

	$part2 = 0;
	foreach ($gears as $location => $numbers) {
		if (count($numbers) == 2) {
			$part2 += array_product($numbers);
		}
	}
	echo 'Part 2: ', $part2, "\n";
