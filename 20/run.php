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
		$modules[$module] = ['type' => $type, 'connected' => explode(', ', $connected), 'value' => $val, 'linked' => [], 'wasOn' => false];
	}

	foreach ($modules as $m => $mod) {
		foreach ($mod['connected'] as $c) {
			if (isset($modules[$c]) && $modules[$c]['type'] == '&') {
				$modules[$c]['value'][$m] = 0;
			} else if (!isset($modules[$c])) {
				$modules[$c] = ['type' => 'rx', 'connected' => [], 'value' => false, 'linked' => [], 'wasOn' => false];
			}

			$modules[$c]['linked'][] = $m;
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

			$mod['wasOn'] = $mod['wasOn'] || ($value == 1);

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
		debugOut('==========[ ', sprintf('%4d', $i), ' ]====', "\n");
		[$p1Modules, $low, $high] = processPulses($p1Modules);
		$part1Low += $low;
		$part1High += $high;
	}
	$part1 += ($part1Low * $part1High);

	echo 'Part 1: ', $part1, "\n";

	$p2Modules = $modules;
	$part2 = 0;
	if (isset($p2Modules['rx'])) {

		$checkMods = [];
		foreach ($modules['rx']['linked'] as $l) {
			foreach ($modules[$l]['linked'] as $m) {
				$checkMods[$m] = 0;
			}
		}

		$c = 0;
		while (true) {
			[$p2Modules, $low, $high] = processPulses($p2Modules);
			$c++;
			$hasValues = true;
			foreach (array_keys($checkMods) as $cm) {
				if ($p2Modules[$cm]['wasOn'] && $checkMods[$cm] == 0) {
					$checkMods[$cm] = $c;
				}
				if ($checkMods[$cm] == 0) { $hasValues = false; }
			}

			if ($hasValues) {
				break;
			}
		}

		$part2 = 1;
		foreach ($checkMods as $v) { $part2 = lcm($part2, $v); }

		echo 'Part 2: ', $part2, "\n";
	}
