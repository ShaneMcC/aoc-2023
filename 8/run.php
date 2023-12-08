#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$directions = array_shift($input);

	$nodes = [];
	foreach ($input as $line) {
		if (preg_match('#(.*) = \((.*), (.*)\)#SADi', $line, $m)) {
			[$all, $node, $left, $right] = $m;
			$nodes[$node] = ['L' => $left, 'R' => $right];
		}
	}

	function findPath($directions, $nodes) {
		$current = 'AAA';
		$count = 0;
		$target = 'ZZZ';

		while ($current != $target) {
			$dir = $directions[$count % strlen($directions)];
			$current = $nodes[$current][$dir];
			$count++;
		}

		return $count;
	}

	$part1 = findPath($directions, $nodes);
	echo 'Part 1: ', $part1, "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
