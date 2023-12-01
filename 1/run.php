#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = [];
	foreach ($input as $line) {
		$first = false;
		$last = false;
		for ($i = 0; $i < strlen($line); $i++) {
			if (is_numeric($line[$i])) {
				if ($first == false) { $first = $line[$i]; }
				$last = $line[$i]; 
			}
		}

		$entries[] = $first . $last;
	}

	var_dump($entries);

	$part1 = array_sum($entries);
	echo 'Part 1: ', $part1, "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
