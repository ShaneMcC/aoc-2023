#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = [];
	foreach ($input as $line) {
		preg_match_all('#(\-?\d+)#i', $line, $m);
		$nums = [];
		foreach ($m[1] as $n) { $nums[] = (int)$n; }
		$entries[] = $nums;
	}

	function getNextValues($startValues) {
		$values = $startValues;
		$list = [[0, ...$values, 0]];
		do {
			$diffs = [];
			$last = false;
			foreach ($values as $v) {
				if ($last !== false) {
					$diffs[] = $v - $last;
				}
				$last = $v;
			}

			$list[] = [0, ...$diffs, 0];
			$values = $diffs;
		} while (!empty($diffs) && (min($diffs) !== 0 || max($diffs) !== 0));

		// Fix start/end
		for ($i = count($list) - 2; $i >= 0; $i--) {
			$lowerList = &$list[$i + 1];
			$thisList = &$list[$i];

			$thisList[count($thisList) - 1] = ($lowerList[count($lowerList) - 1] + $thisList[count($thisList) - 2]);
			$thisList[0] = ($thisList[1] - $lowerList[0]);
		}

		if (isDebug()) {
			echo "==========", "\n";
			foreach ($list as $l) { echo json_encode($l), "\n"; }
			echo "==========", "\n";
		}

		return [$list[0][0], $list[0][count($list[0]) - 1]];
	}

	$part1 = $part2 = 0;
	foreach ($entries as $e) {
		$next = getNextValues($e);
		$part1 += $next[1];
		$part2 += $next[0];
	}
	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
