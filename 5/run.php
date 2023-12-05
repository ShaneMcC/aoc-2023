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
			$steps[] = ['start' => (int)$start, 'end' => $start + $range - 1, 'dest' => (int)$dest, 'delta' => ($dest - $start), 'original' => $mapping];
		}
		// Sort in order
		usort($steps, function($a, $b) { return $a['start'] <=> $b['start']; });

		// Add missing games as a mapping entry so that we have a complete set of mappings from 0 => PHP_INT_MAX;
		$newRanges = [];
		for ($i = 0; $i < PHP_INT_MAX; $i++) {
			$next = $steps[0];

			// If the next range is higher than us, add a mapping from here to there.
			if ($i < $next['start']) {
				$newRanges[] = ['start' => $i, 'end' => $next['start'] - 1, 'dest' => (int)$i, 'delta' => 0, 'computed' => true];
			}

			// Add the next range
			$newRanges[] = array_shift($steps);
			$i = $next['end'];

			// If there are no more ranges, add a final range.
			if (empty($steps)) {
				$newRanges[] = ['start' => $i + 1, 'end' => PHP_INT_MAX, 'dest' => (int)$i + 1, 'delta' => 0, 'computed' => true];
				$i = PHP_INT_MAX;
			}
		}

		$maps[$title] = $newRanges;
	}

	debugOut(json_encode($maps, JSON_PRETTY_PRINT), "\n");

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

				$convertedStart = $bitStart + $mapping['delta'];
				$convertedEnd = $bitEnd + $mapping['delta'];
				$newRange = [$convertedStart, $convertedEnd];
				debugOut(" mapped to ", json_encode($newRange), ' from ', json_encode($mapping['original'] ?? 'gap'), "\n");

				$newRanges[] = $newRange;

			}
			debugOut("\n");
		}
		return $newRanges;
	}

	function getLocations($seedRanges) {
		global $maps;

		debugOut("== Start\n");
		foreach ($seedRanges as $range) {
			debugOut(json_encode($range), "\n");
		}

		foreach (array_keys($maps) as $name) {
			$seedRanges = getRangesForStep($seedRanges, $name);
		}
		return $seedRanges;
	}

	// Get ranges for part 1;
	$ranges = [];
	for ($i = 0; $i < count($seeds); $i++) { $ranges[] = [(int)$seeds[$i], (int)$seeds[$i]]; }
	$part1 = min(array_column(getLocations($ranges), 0));
	echo 'Part 1: ', $part1, "\n";

	// Get ranges for part 2.
	$ranges = [];
	for ($i = 0; $i < count($seeds); $i += 2) { $ranges[] = [(int)$seeds[$i], ($seeds[$i] + $seeds[$i + 1])]; }
	$part2 = min(array_column(getLocations($ranges), 0));
	echo 'Part 2: ', $part2, "\n";
