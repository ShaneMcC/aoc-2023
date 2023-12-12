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

	function getOptions($springs, $counts) {
		$key = json_encode([__FILE__, __LINE__, func_get_args()]);

		return storeCachedResult($key, function() use ($springs, $counts) {
			// If we have no more springs, then we are valid if there are also no more counts.
			if (empty($springs)) { return empty($counts) ? 1 : 0; }

			// If we have no more counts, we are valid if our springs has no more blocks;
			if (empty($counts)) { return str_contains($springs, '#') ? 0 : 1; }

			// If the total length of all our counts + gap between them is longer than our
			// remaining springs, then we are not valid.
			$springsLen = strlen($springs);
			if (array_sum($counts) + count($counts) - 1 > $springsLen) { return 0; }

			$firstChar = $springs[0];

			// For ?, just check it as both . and #
			if ($firstChar == '?') {
				return getOptions('.' . substr($springs, 1), $counts) + getOptions('#' . substr($springs, 1), $counts);
			}

			// Swallow ., and then move onto the next blocks.
			if ($firstChar == '.' ) {
				return getOptions(ltrim($springs, '.'), $counts);
			}

			// Blocks.
			if ($firstChar == '#') {
				$thisBlockLen = array_shift($counts);

				// If the next $thisBlockLen characters contain a '.', then it's not this block
				// so is invalid
				$thisBlockStr = substr($springs, 0, $thisBlockLen);
				if (str_contains($thisBlockStr, ".")) { return 0; }

				// If the start of the next block is a '#' then it is not valid.
				$nextBlockStr = substr($springs, $thisBlockLen);
				if (!empty($nextBlockStr) && $nextBlockStr[0] == '#') { return 0; }

				// Swallow the first character so that if it is a ? we avoid treating it as a '#'
				$nextBlockStr = substr($nextBlockStr, 1);

				// Move to the next block.
				return getOptions($nextBlockStr, $counts);
			}
		});
	}

	$part1 = 0;
	foreach ($entries as $e) {
		$part1 += getOptions($e['springs'], $e['counts']);
	}
	echo 'Part 1: ', $part1, "\n";

	$part2 = 0;
	foreach ($entries as $e) {
		$unfoldedSprings = implode('?', array_fill(0, 5, $e['springs']));
		$unfoldedCounts = explode(',', implode(',', array_fill(0, 5, implode(',', $e['counts']))));
		$part2 += getOptions($unfoldedSprings, $unfoldedCounts);
	}
	echo 'Part 2: ', $part2, "\n";
