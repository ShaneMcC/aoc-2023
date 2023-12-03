#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputMap();

	$numbers = [];

	function getSymbols($startx, $starty, $endx, $endy) {
		global $input;

		$symbols = [];

		foreach (yieldXY($startx, $starty, $endx, $endy, true) as $x => $y) {
			$val = ($input[$y][$x] ?? '.');
			if (!is_numeric($val) && $val != '.') {
				$symbols[] = [$val, $x . ',' . $y];
			}
		}

		return $symbols;
	}

	$part1 = 0;
	$gears = [];

	foreach ($input as $y => $row) {
		$current = null;
		$startX = null;
		foreach ($row as $x => $col) {
			if ($current == null && is_numeric($col)) {
				$startX = $x;
				$current = $col;
			} else if (is_numeric($col)) {
				$current .= $col;
			} else if ($current != null) {
				$symbols = getSymbols($startX - 1, $y - 1, $x, $y + 1);
				if (!empty($symbols)) {
					$part1 += $current;
					foreach ($symbols as $symbolInfo) {
						[$symbol, $location] = $symbolInfo;

						if ($symbol == '*') {
							if (!isset($gears[$location])) { $gears[$location] = []; }
							$gears[$location][] = $current;
						}
					}
				}

				debugOut('Number: ', $current, "\n");
				debugOut("\t", (!empty($symbols) ? "VALID (" . implode('', array_column($symbols, 0)) . ")" : "NOT VALID"), "\n");
				$current = null;
			}
		}

		if ($current != null) {
			$symbols = getSymbols($startX - 1, $y - 1, $x, $y + 1);
			if (!empty($symbols)) {
				$part1 += $current;
				foreach ($symbols as $symbolInfo) {
					[$symbol, $location] = $symbolInfo;

					if ($symbol == '*') {
						if (!isset($gears[$location])) { $gears[$location] = []; }
						$gears[$location][] = $current;
					}
				}
			}

			debugOut('Number: ', $current, "\n");
			debugOut("\t", (!empty($symbols) ? "VALID (" . implode('', array_column($symbols, 0)) . ")" : "NOT VALID"), "\n");
			$current = null;
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