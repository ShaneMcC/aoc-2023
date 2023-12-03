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
				$symbols[] = $val;
			}
		}

		return $symbols;
	}

	$part1 = 0;

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
				debugOut('Number: ', $current, "\n");
				$symbols = getSymbols($startX - 1, $y - 1, $x, $y + 1);
				debugOut("\t", (!empty($symbols) ? "VALID (" . implode('', $symbols) . ")" : "NOT VALID"), "\n");
				if (!empty($symbols)) { $part1 += $current; }
				$current = null;
			}
		}

		if ($current != null) {
			debugOut('Number: ', $current, "\n");
			$symbols = getSymbols($startX - 1, $y - 1, $x, $y + 1);
			debugOut("\t", (!empty($symbols) ? "VALID (" . implode('', $symbols) . ")" : "NOT VALID"), "\n");
			if (!empty($symbols)) { $part1 += $current; }
			$current = null;
		}
	}


	echo 'Part 1: ', $part1, "\n";