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

	function findPath($directions, $nodes, $current = 'AAA', $target = 'ZZZ') {
		$count = 0;

		if (isset($nodes[$current])) {
			while (!preg_match('/^' . $target . '$/', $current)) {
				$dir = $directions[$count % strlen($directions)];
				$current = $nodes[$current][$dir];
				$count++;
			}
		}

		return $count;
	}

	$part1 = findPath($directions, $nodes);
	echo 'Part 1: ', $part1, "\n";

	function findAllPaths($directions, $nodes) {
		$count = [];

		foreach (array_keys($nodes) as $n) {
			if ($n[strlen($n) - 1] == 'A') {
				$count[$n] = findPath($directions, $nodes, $n, '..Z');
			}
		}

		$result = 1;
		foreach ($count as $c) {
			$result = lcm($result, $c);
		}

		return $result;
	}

	$part2 = findAllPaths($directions, $nodes);
	echo 'Part 2: ', $part2, "\n";
