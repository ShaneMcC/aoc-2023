#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLineGroups();

	$workflows = [];
	foreach ($input[0] as $line) {
		preg_match('#(.*)\{(.*)\}#SADi', $line, $m);
		[$all, $workflow, $details] = $m;

		$flow = [];
		foreach (explode(',', $details) as $detail) {
			$bits = explode(':', $detail);

			if (isset($bits[1])) {
				preg_match("/^([xmas])([<>])([0-9]+)$/", $bits[0], $cm);
				[$all, $check, $comparison, $value] = $cm;
				$flow[] = ['condition' => ['check' => $check, 'comparison' => $comparison, 'value' => $value], 'then' => $bits[1]];
			} else {
				$flow[] = ['condition' => true, 'then' => $bits[0]];
			}
		}

		$workflows[$workflow] = $flow;
	}

	$parts = [];
	foreach ($input[1] as $line) {
		preg_match('#\{(.*)\}#SADi', $line, $m);
		[$all, $details] = $m;
		$part = [];
		foreach (explode(',', $details) as $d) {
			$bits = explode('=', $d);
			$part[$bits[0]] = $bits[1];
		}
		$parts[] = $part;
	}

	function processPart($part) {
		global $workflows;

		if (isDebug()) { debugOut(json_encode($part), ': '); }

		$next = 'in';
		while (true) {
			if (isDebug()) { debugOut($next); }
			if (!isset($workflows[$next])) {
				if (isDebug()) { debugOut("\n"); }
				return $next;
			}
			if (isDebug()) { debugOut(' -> '); }

			foreach ($workflows[$next] as $step) {
				$result = true;
				if ($step['condition'] !== true) {
					$check = $step['condition']['check'];
					$value = $step['condition']['value'];

					if ($step['condition']['comparison'] === '<') {
						$result = $part[$check] < $value;
					} else if ($step['condition']['comparison'] === '>') {
						$result = $part[$check] > $value;
					}
				}


				if ($result) {
					$next = $step['then'];
					break;
				}
			}
		}
	}

	$part1 = 0;
	foreach ($parts as $part) {
		$result = processPart($part);
		if ($result == 'A') {
			$part1 += array_sum($part);
		}
	}
	echo 'Part 1: ', $part1, "\n";

	// $part2 = 0;
	// echo 'Part 2: ', $part2, "\n";
