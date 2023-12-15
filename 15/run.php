#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function getHash($string) {
		$val = 0;

		for ($i = 0; $i < strlen($string); $i++) {
			$val += ord($string[$i]);
			$val = $val * 17;
			$val = $val % 256;
		}

		return $val;
	}

	$part1 = 0;
	foreach (explode(',', $input) as $step) {
		$part1 += getHash($step);
	}
	echo 'Part 1: ', $part1, "\n";

	$boxes = [];
	foreach (explode(',', $input) as $step) {
		preg_match('/^(.+)([=-])(\d?)$/', $step, $m);
		[$all, $label, $action, $value] = $m;
		$hash = getHash($label);

		if ($action == '-') {
			if (isset($boxes[$hash][$label])) {
				$thing = $boxes[$hash][$label];
				unset($boxes[$hash][$label]);
			}
		} else if ($action == '=') {
			if (!isset($boxes[$hash])) { $boxes[$hash] = []; }
			$boxes[$hash][$label] = $value;
		}
	}

	$part2 = 0;
	foreach ($boxes as $boxNum => $box) {
		$pos = 1;
		foreach ($box as $lens) {
			$part2 += ($boxNum + 1) * $pos * $lens;
			$pos++;
		}
	}
	echo 'Part 2: ', $part2, "\n";
