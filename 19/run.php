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

		if (isDebug()) { echo json_encode($part), ': '; }

		$next = 'in';
		while (true) {
			if (isDebug()) { echo $next; }
			if (!isset($workflows[$next])) {
				if (isDebug()) { echo "\n"; }
				return $next;
			}
			if (isDebug()) { echo ' -> '; }

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

	$part2 = 0;

	function getAcceptedCount($accepted, $workflowName) {
		global $workflows;

		if (isDebug()) { echo 'GetAcceptedCount(', json_encode($accepted), ', ', $workflowName, ');', "\n"; }

		if ($workflowName == 'A') {
			$v = 1;
			foreach ($accepted as $a) {
				$v *= ($a['max'] - $a['min']) + 1;
			}
			if (isDebug()) { echo "\t => ", $v, "\n"; }
			return $v;
		} else if ($workflowName == 'R') {
			if (isDebug()) { echo "\t => ", 0, "\n"; }
			return 0;
		}

		$workflow = $workflows[$workflowName];
		$count = 0;

		foreach ($workflow as $step) {
			$stepAccepted = $accepted;
			if ($step['condition'] !== true) {
				$check = $step['condition']['check'];
				$value = $step['condition']['value'];

				if ($step['condition']['comparison'] === '<') {
					$stepAccepted[$check]['max'] = min($value - 1, $accepted[$check]['max']);
					$accepted[$check]['min'] = max($value, $accepted[$check]['min']);
				} else if ($step['condition']['comparison'] === '>') {
					$stepAccepted[$check]['min'] = max($value + 1, $accepted[$check]['min']);
					$accepted[$check]['max'] = min($value, $accepted[$check]['max']);
				}
			}

			$count += getAcceptedCount($stepAccepted, $step['then']);
		}

		return $count;
	}

	$accepted['x'] = ['min' => 1, 'max' => 4000];
	$accepted['m'] = ['min' => 1, 'max' => 4000];
	$accepted['a'] = ['min' => 1, 'max' => 4000];
	$accepted['s'] = ['min' => 1, 'max' => 4000];

	$part2 = getAcceptedCount($accepted, 'in');

	echo 'Part 2: ', $part2, "\n";
