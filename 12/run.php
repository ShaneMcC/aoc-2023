#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = [];
	foreach ($input as $line) {
		preg_match('#([\.\#\?]+) (.*)#SADi', $line, $m);
		[$all, $springs, $counts] = $m;
		$entries[] = ['springs' => $springs, 'counts' => explode(',', $counts)];
	}

	// I hate this.
	function getOptions($springs, $counts) {
		$current = [''];
		for ($i = 0; $i < strlen($springs); $i++) {
			$newCurrent = [];
			foreach ($current as $c) {
				if ($springs[$i] == '?') {
					$newCurrent[] = $c . '.';
					$newCurrent[] = $c . '#';
				} else {
					$newCurrent[] = $c . $springs[$i];
				}
			}
			$current = $newCurrent;
		}

		// I really hate this bit.
		$regexBits = [];
		foreach ($counts as $i => $c) {
			$regexBits[] = '([^#]' . ($i == 0 ? '*' : '+') . '[#]{'.$c.'})';
		}

		$regex = '/^' . implode('', $regexBits) . '[^#]*$/';

		debugOut('Testing: ', $springs, ' for ', implode(', ', $counts), ' using: ', $regex, "\n");

		$valid = [];
		foreach ($current as $c) {
			$result = preg_match($regex, $c);
			debugOut("\t", $c, ' => ', ($result ? 'valid' : 'invalid'), "\n");
			if ($result) {
				$valid[] = $c;
			}
		}

		debugOut("\t\tTotal: ", count($valid), "\n");

		return $valid;
	}

	$part1 = 0;
	foreach ($entries as $e) {
		$c = getOptions($e['springs'], $e['counts']);
		$part1 += count($c);
	}
	echo 'Part 1: ', $part1, "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
