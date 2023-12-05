#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLineGroups();

	$seeds = explode(" ", array_shift($input)[0]);
	array_shift($seeds);

	$maps = [];
	foreach ($input as $section) {
		$title = explode(" ", array_shift($section))[0];
		$steps = [];
		// Get Steps
		foreach ($section as $mapping) {
			[$dest, $start, $range] = explode(" ", $mapping);
			$steps[] = ['start' => (int)$start, 'end' => $start + $range - 1, 'dest' => (int)$dest];
		}
		// Sort in order
		usort($steps, function($a, $b) { return $a['start'] <=> $b['start']; });

		// Add missing games as a mapping entry so that we have a complete set of mappings from 0 => PHP_INT_MAX;
		$newRanges = [];
		for ($i = 0; $i < PHP_INT_MAX; $i++) {
			$next = $steps[0];

			// If the next range is higher than us, add a mapping from here to there.
			if ($i < $next['start']) {
				$newRanges[] = ['start' => $i, 'end' => $next['start'] - 1, 'dest' => (int)$i, 'computed' => true];
			}

			// Add the next range
			$newRanges[] = array_shift($steps);
			$i = $next['end'];

			// If there are no more ranges, add a final range.
			if (empty($steps)) {
				$newRanges[] = ['start' => $i + 1, 'end' => PHP_INT_MAX, 'dest' => (int)$i + 1, 'computed' => true];
				$i = PHP_INT_MAX;
			}
		}

		$maps[$title] = $newRanges;
	}

	debugOut(json_encode($maps, JSON_PRETTY_PRINT), "\n");

	function convert($type, $value) {
		global $maps;
		debugOut("Converting {$value} {$type} => ");

		$newValue = $value;
		foreach ($maps[$type] as $mapping) {
			if ($value >= $mapping['start'] && $value <= $mapping['end']) {
				$newValue = $mapping['dest'] + ($value - $mapping['start']);
				debugOut("{$newValue}\n");
				return $newValue;
			}
		}
	}

	$part1 = PHP_INT_MAX;
	foreach ($seeds as $seed) {
		$value = $seed;
		foreach (array_keys($maps) as $step) {
			$value = convert($step, $value);
		}

		$part1 = min($part1, $value);
	}

	echo 'Part 1: ', $part1, "\n";

	function getRangesForStep($ranges, $stepName) {
		global $maps;

		debugOut("== ", $stepName, "\n");
		// Split each range into as many bits as needed.
		$newRanges = [];
		foreach ($ranges as [$rangeStart, $rangeEnd]) {
			debugOut(json_encode([$rangeStart, $rangeEnd]), "\n");
			foreach ($maps[$stepName] as $mapping) {
				if ($mapping['end'] < $rangeStart || $mapping['start'] > $rangeEnd) { continue; }

				$bitStart = max($mapping['start'], $rangeStart);
				$bitEnd = min($mapping['end'], $rangeEnd);

				debugOut("\t\t => ", json_encode([$bitStart, $bitEnd]));

				$convertedStart = $mapping['dest'] + ($bitStart - $mapping['start']);
				$convertedEnd = $mapping['dest'] + ($bitEnd - $mapping['start']);
				$newRange = [$convertedStart, $convertedEnd];
				debugOut(" mapped to ", json_encode($newRange), "\n");

				$newRanges[] = $newRange;

			}
			debugOut("\n");
		}
		return $newRanges;
	}

	// Get ranges for part 2.
	$ranges = [];
	for ($i = 0; $i < count($seeds); $i++) {
		$ranges[] = [(int)$seeds[$i], ($seeds[$i] + $seeds[$i + 1])];
		$i++;
	}

	debugOut("== Start\n");
	foreach ($ranges as $range) {
		debugOut(json_encode($range), "\n");
	}

	foreach (array_keys($maps) as $name) {
		$ranges = getRangesForStep($ranges, $name);
	}

	$part2 = min(array_column($ranges, 0));
	echo 'Part 2: ', $part2, "\n";
