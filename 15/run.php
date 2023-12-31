#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function getHash($string) {
		$val = 0;

		for ($i = 0; $i < strlen($string); $i++) {
			$val = (($val + ord($string[$i])) * 17) % 256;
		}

		return $val;
	}

	$part1 = $part2 = 0;
	$boxes = [];
	foreach (explode(',', $input) as $step) {
		$part1 += getHash($step);

		preg_match('/^(.+)([=-])(\d?)$/', $step, $m);
		[$all, $label, $action, $value] = $m;
		$hash = getHash($label);

		if ($action == '-') {
			unset($boxes[$hash][$label]);
		} else if ($action == '=') {
			$boxes[$hash][$label] = $value;
		}
	}

	foreach ($boxes as $boxNum => $box) {
		$pos = 1;
		foreach ($box as $lens) {
			$part2 += ($boxNum + 1) * $pos * $lens;
			$pos++;
		}
	}
	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
