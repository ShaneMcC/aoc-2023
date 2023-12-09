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

		$list[] = $diffs;
		while (true) {
			$diffs = getDifferences($diffs);
			$list[] = $diffs;
			foreach ($diffs as $d) { if ($d !== 0) { continue 2; }}
			break;
		}

		$list[count($list) - 1][] = 0;
		array_unshift($list[count($list) - 1], 0);

		for ($i = count($list) - 2; $i >= 0; $i--) {
			$lowerList = $list[$i + 1];
			$thisList = $list[$i];

			$list[$i][] = ($lowerList[count($lowerList) - 1] + $thisList[count($thisList) - 1]);
			array_unshift($list[$i], ($thisList[0] - $lowerList[0]));
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
