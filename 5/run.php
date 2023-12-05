#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLineGroups();

	$seeds = explode(" ", array_shift($input)[0]);
	array_shift($seeds);

	$maps = [];
	foreach ($input as $section) {
		$title = explode(" ", array_shift($section))[0];
		$maps[$title] = [];
		foreach ($section as $mapping) {
			[$dst, $src, $range] = explode(" ", $mapping);
			$maps[$title][] = ['dst' => $dst, 'src' => $src, 'range' => $range];
		}
	}

	function convert($maps, $type, $value) {
		debugOut("Converting {$value} {$type} => ");

		foreach ($maps[$type] as $mapping) {
			if ($value >= $mapping['src'] && $value < ($mapping['src'] + $mapping['range'])) {
				$diff = $value - $mapping['src'];
				$value = $mapping['dst'] + $diff;
				break;
			}
		}

		debugOut("{$value}\n");
		return $value;
	}

	$part1 = PHP_INT_MAX;
	foreach ($seeds as $seed) {
		$location = convert($maps, 'humidity-to-location', convert($maps, 'temperature-to-humidity', convert($maps, 'light-to-temperature', convert($maps, 'water-to-light', convert($maps, 'fertilizer-to-water', convert($maps, 'soil-to-fertilizer', convert($maps, 'seed-to-soil', $seed)))))));

		$part1 = min($part1, $location);
	}

	echo 'Part 1: ', $part1, "\n";
