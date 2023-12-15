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

	// $part2 = 0;
	// echo 'Part 2: ', $part2, "\n";
