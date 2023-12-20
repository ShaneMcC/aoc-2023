#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$modules = [];
	foreach ($input as $line) {
		preg_match('#([%&]?)(.*) -> (.*)#SADi', $line, $m);
		[$all, $type, $module, $connected] = $m;
		$val = 0;
		if ($type == '&') { $val = []; }
		$modules[$module] = ['type' => $type, 'connected' => explode(', ', $connected), 'value' => $val];
	}

	foreach ($modules as $m => $mod) {
		foreach ($mod['connected'] as $c) {
			if (isset($modules[$c]) && $modules[$c]['type'] == '&') {
				$modules[$c]['value'][$m] = 0;
			}
		}
	}

	function processPulses($modules) {
		$queue = [];

		$queue[] = ['broadcaster', 0, 'button'];

		$low = 0;
		$high = 0;

		while (!empty($queue)) {
			[$target, $value, $source] = array_shift($queue);

			$valStr = $value ? 'high' : 'low';
			debugOut("{$source} -{$valStr}-> {$target}\n");

			if ($value == 0) {
				$low++;
			} else {
				$high++;
			}

			if (!isset($modules[$target])) { continue; }

			$mod = &$modules[$target];

			if ($mod['type'] == '%') {
				if ($value == 1) { continue; }
				else if ($mod['value'] == 0) {
					$mod['value'] = 1;
				} else if ($mod['value'] == 1) {
					$mod['value'] = 0;
				}
				$value = $mod['value'];
			} else if ($mod['type'] == '&') {
				$mod['value'][$source] = $value;

				if (array_sum($mod['value']) == count($mod['value'])) {
					$value = 0;
				} else {
					$value = 1;
				}
			}

			if (!is_array($mod['connected'])) {
				die('#');
			}
			foreach ($mod['connected'] as $t) {
				$queue[] = [$t, $value, $target];
			}
		}

		return [$modules, $low, $high];
	}

	$part1 = 0;
	$part1Low = 0;
	$part1High = 0;
	$p1Modules = $modules;
	for ($i = 0; $i < 1000; $i++) {
		[$p1Modules, $low, $high] = processPulses($p1Modules);
		$part1Low += $low;
		$part1High += $high;
	}
	$part1 += ($part1Low * $part1High);

	echo 'Part 1: ', $part1, "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
