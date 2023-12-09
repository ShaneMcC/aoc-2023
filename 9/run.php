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

	function getDifferences($set) {
		$diffs = [];
		$last = false;
		foreach ($set as $s) {
			if ($last !== false) {
				$diffs[] = $s - $last;
			}
			$last = $s;
		}
		return $diffs;
	}

	function getNextValues($set) {
		$diffs = $set;
		$list = [];

		$list[] = [0, ...$diffs, 0];
		do {
			$diffs = getDifferences($diffs);
			$list[] = [0, ...$diffs, 0];
		} while (min($diffs) !== 0 || max($diffs) !== 0);

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
