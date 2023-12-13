<?php
	/* Some of these are not memory efficient, so don't bother caring. */
	ini_set('memory_limit', '-1');
	if (!defined('START_TIME')) { define('START_TIME', microtime(true)); }

	/*
	 * To make code easier to read, sometimes we move "fluff" code to a separate
	 * file, include it if it exists.
	 *
	 * "Fluff" code is code that doesn't really serve to find the actual
	 * solution, but may instead do nice things with the output.
	 */
	if (file_exists(realpath(dirname($_SERVER['PHP_SELF'])) . '/fluff.php')) {
		require_once(realpath(dirname($_SERVER['PHP_SELF'])) . '/fluff.php');
	}

	/**
	 * Get the answer for this day if known.
	 *
	 * @return string Answer string
	 */
	function getAnswer($part): string {
		$file = realpath(dirname($_SERVER['PHP_SELF'])) . '/answers.txt';

		$answers = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (isset($answers[$part - 1])) {
			return $answers[$part - 1];
		}

		return '';
	}

	/**
	 * Get the answers to both parts of the day.
	 *
	 * @return string[] Array of answers
	 */
	function getAnswers(): array {
		return [1 => getAnswer(1), 2 => getAnswer(2)];
	}

	/**
	 * Get the file to read input from.
	 * This will return php://stdin if we have something passed on stdin,
	 * else it will return the file passed on the cli as --file if present, if
	 * no file specified on the CLI then test mode uses 'test.txt' otherwise
	 * fallback to 'input.txt'
	 *
	 * @return string Filename to read from.
	 */
	function getInputFilename(): string {
		global $__CLIOPTS;

		if (getenv("TIMED") !== FALSE) {
			return realpath(dirname($_SERVER['PHP_SELF'])) . '/input.txt';
		} else {
			if (function_exists('posix_isatty') && !posix_isatty(STDIN)) {
				return 'php://stdin';
			} else if (isset($__CLIOPTS['file']) && file_exists($__CLIOPTS['file'])) {
				return $__CLIOPTS['file'];
			}

			$default = realpath(dirname($_SERVER['PHP_SELF'])) . '/' . basename(isTest() ? 'test.txt' : 'input.txt');
			if (file_exists($default)) {
				return $default;
			}
		}

		die('No valid input found.');
	}

	/**
	 * Get the input as line groups.
	 * Each group is separated by a blank line in the source file.
	 * 	 *
	 * @return array[] File as array of array of lines.
	 */
	function getInputLineGroups(): array {
		$groups = [];
		$group = [];
		foreach (explode("\n", getInputContent()) as $line) {
			if (empty($line)) {
				if (count($group) > 0) { $groups[] = $group; }
				$group = [];
			} else {
				$group[] = $line;
			}
		}
		if (count($group) > 0) { $groups[] = $group; }

		return $groups;
	}


	/**
	 * Get the input as a map.
	 *
	 * @return string[][] File as a grid[$y][$x].
	 */
	function getInputMap(): array {
		$map = [];
		foreach (getInputLines() as $row) { $map[] = str_split($row); }
		return $map;
	}

	/**
	 * Get the input as a sparse map.
	 *
	 * @param $remove (Default: ['.', ' ']) Array of characters to remove.
	 * @return string[][] File as a sparse grid[$y][$x].
	 */
	function getInputSparseMap($remove = ['.', ' ']): array {
		return sparseMap(getInputMap(), $remove);
	}

	/**
	 * Get the input as an array of lines.
	 *
	 * @return string[] File as an array of lines. Empty lines are ignored.
	 */
	function getInputLines(): array {
		return file(getInputFilename(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	}

	/**
	 * Get the input as a single string.
	 *
	 * @return string Whole file as a single string.
	 */
	function getInputContent(): string {
		return file_get_contents(getInputFilename());
	}

	/**
	 * Get the first line from the input.
	 *
	 * @return string First line of input.
	 */
	function getInputLine(): string {
		$lines = getInputLines();
		return isset($lines[0]) ? trim($lines[0]) : '';
	}

	/**
	 * Are we running in debug mode?
	 *
	 * Debug mode usually results in more output.
	 *
	 * @return bool True for debug mode, else false.
	 */
	function isDebug(): bool {
		global $__CLIOPTS;

		return isset($__CLIOPTS['d']) || isset($__CLIOPTS['debug']);
	}

	/**
	 * Echo something if we are running in debug mode.
	 */
	function debugOut() {
		if (isDebug()) {
			foreach (func_get_args() as $arg) { echo $arg; }
		}
	}

	/**
	 * Are we running in test mode?
	 *
	 * Test mode reads from test.txt not input.txt by default.
	 *
	 * @return bool True for test mode, else false.
	 */
	function isTest(): bool {
		global $__CLIOPTS;

		return isset($__CLIOPTS['t']) || isset($__CLIOPTS['test']);
	}

	/**
	 * array_sum on multi-dimensional arrays.
	 *
	 * @param $array Array to sum.
	 * @return int Sum of all vaules in array.
	 */
	function multi_array_sum($array): int {
		$result = 0;
		foreach ($array as $a) { $result += (is_array($a) ? multi_array_sum($a) : $a); }
		return $result;
	}

	/**
	 * Calculate manhattan distance between 2 points.
	 *
	 * @param $x1 Point 1, X location.
	 * @param $y1 Point 1, Y location.
	 * @param $x2 Point 2, X location.
	 * @param $y2 Point 2, Y location.
	 * @return int Manhattan distance.
	 */
	function manhattan($x1, $y1, $x2, $y2): int {
		return abs($x1 - $x2) + abs($y1 - $y2);
	}

	/**
	 * Generator to provide X/Y coordinates.
	 * X is returned as the Key, Y as the value
	 *
	 * @param $startx Starting X value
	 * @param $starty Starting Y value
	 * @param $endx Ending X value
	 * @param $endy Ending Y value
	 * @param $inclusive (Default: true) are endx/endy inclusive?
	 * @return Generator of $x => $y pairs
	 */
	function yieldXY($startx, $starty, $endx, $endy, $inclusive = true) {
		for ($x = $startx; $x <= ($inclusive ? $endx : $endx - 1); $x++) {
			for ($y = $starty; $y <= ($inclusive ? $endy : $endy - 1); $y++) {
				yield $x => $y;
			}
		}
	}

	/**
	 * Generator to provide each cell of a grid.
	 *
	 * @param $grid Grid to look at.
	 * @return Generator<array> of [$x, $y, $cell] items
	 */
	function cells($grid) {
		foreach ($grid as $y => $row) {
			foreach ($row as $x => $cell) {
				yield [$x, $y, $cell];
			}
		}
	}

	/**
	 * Find all cells that have content that match $wanted
	 *
	 * @param $grid Grid to look at.
	 * @param $wanted Cell content to look for.
	 * @return array<int, int[]> Matching cells.
	 */
	function findCells($grid, $wanted): array {
		$cells = [];
		foreach (cells($grid) as [$x, $y, $cell]) {
			if ($cell == $wanted) {
				$cells[] = [$x, $y];
			}
		}
		return $cells;
	}

	/**
	 * Generator to provide adjacent cells of a point.
	 *
	 * @param $grid Grid to look at
	 * @param $x X point
	 * @param $y Y point
	 * @param $diagonal (Default: false) Include diagonals?
	 * @param $self (Default: false) Include self?
	 * @return array<int, int[]> Array of all existing adjacent cells
	 */
	function getAdjacentCells($grid, $x, $y, $diagonal = false, $self = false): array {
		$adjacent = [];

		if ($diagonal && isset($grid[$y - 1][$x - 1])) { $adjacent[] = [$x - 1, $y - 1]; }
		if (isset($grid[$y - 1][$x])) { $adjacent[] = [$x, $y - 1]; }
		if ($diagonal && isset($grid[$y - 1][$x + 1])) { $adjacent[] = [$x + 1, $y - 1]; }
		if (isset($grid[$y][$x - 1])) { $adjacent[] = [$x - 1, $y]; }
		if ($self && isset($grid[$y][$x])) { $adjacent[] = [$x, $y]; }
		if (isset($grid[$y][$x + 1])) { $adjacent[] = [$x + 1, $y]; }
		if ($diagonal && isset($grid[$y + 1][$x - 1])) { $adjacent[] = [$x - 1, $y + 1]; }
		if (isset($grid[$y + 1][$x])) { $adjacent[] = [$x, $y + 1]; }
		if ($diagonal && isset($grid[$y + 1][$x + 1])) { $adjacent[] = [$x + 1, $y + 1]; }

		return $adjacent;
	}

	/**
	 * Generator to provide all adjacent cells of a point. This won't check if
	 * the cell exists in the grid. (Useful for sparse grids)
	 *
	 * @param $grid Grid to look at
	 * @param $x X point
	 * @param $y Y point
	 * @param $diagonal (Default: false) Include diagonals?
	 * @param $self (Default: false) Include self?
	 * @return array<int, int[]> Array of all possible adjacent cells.
	 */
	function getAllAdjacentCells($grid, $x, $y, $diagonal = false, $self = false) {
		$adjacent = [];

		if ($diagonal) { $adjacent[] = [$x - 1, $y - 1]; }
		$adjacent[] = [$x, $y - 1];
		if ($diagonal) { $adjacent[] = [$x + 1, $y - 1]; }
		$adjacent[] = [$x - 1, $y];
		if ($self) { $adjacent[] = [$x, $y]; }
		$adjacent[] = [$x + 1, $y];
		if ($diagonal) { $adjacent[] = [$x - 1, $y + 1]; }
		$adjacent[] = [$x, $y + 1];
		if ($diagonal) { $adjacent[] = [$x + 1, $y + 1]; }

		return $adjacent;
	}

	/**
	 * Count cells in a given grid of a given type.
	 *
	 * @param $grid Grid to look at
	 * @param $matching Value to check for
	 * @return int count of $matching in $grid
	 */
	function countCells($grid, $matching = '#'): int {
		$count = 0;

		foreach ($grid as $row) {
			$acv = array_count_values($row);
			$count += isset($acv[$matching]) ? $acv[$matching] : 0;
		}

		return $count;
	}

	/**
	 * Get the bounding box of a standard $grid[$y][$x] array.
	 * $grid may be sparsely populated.
	 *
	 * @param $grid Input grid.
	 * @return int[] Array of [$minX, $minY, $maxX, $maxY]
	 */
	function getBoundingBox($map, $padding = 0): array {
		$minX = $minY = $maxX = $maxY = 0;
		foreach ($map as $y => $row) {
			$minY = min($minY, $y);
			$maxY = max($maxY, $y);
			foreach ($row as $x => $cell) {
				$minX = min($minX, $x);
				$maxX = max($maxX, $x);
			}
		}

		return [$minX - $padding, $minY - $padding, $maxX + $padding, $maxY + $padding];
	}

	/**
	 * Draw a map on the screen.
	 *
	 * @param  $map Map to draw
	 * @param  $border (Default: false) Include a border
	 * @param  $title (Default: '') If we are drawing a border, should we also
	 *                draw a title box?
	 */
	function drawMap($map, $border = false, $title = '') {
		[$minX, $minY, $maxX, $maxY] = getBoundingBox($map);
		$width = ($maxX - $minX) + 1;

		if ($border) {
			echo "\n";

			if (!empty($title)) {
				$titleWidth = max($width, strlen($title));
				$titlePadding = ($titleWidth - strlen($title)) / 2;
				echo '┍', str_repeat('━', $titleWidth), '┑', "\n";
				echo '│', sprintf("%".floor($titlePadding)."s%s%".ceil($titlePadding).'s', '', $title, ''), '│', "\n";
				echo '┕', str_repeat('━', $titleWidth), '┙', "\n";
				echo "\n";
			}

			echo '┍', str_repeat('━', $width), '┑', "\n";
		}
		foreach ($map as $row) {
			if ($border) { echo '│'; }
			echo is_array($row) ? implode('', $row) : $row;
			if ($border) { echo '│'; }
			echo "\n";
		}
		if ($border) {  echo '┕', str_repeat('━', $width), '┙', "\n"; }
	}

	/**
	 * Draw a sparse map on the screen.
	 *
	 * @param  $map Map to draw
	 * @param  $empty (Default: '.') What character to use for the sparse cells
	 * @param  $border (Default: false) Include a border
	 * @param  $title (Default: '') If we are drawing a border, should we also
	 *                draw a title box?
	 */
	function drawSparseMap($map, $empty = '.', $border = false, $title = '') {
		[$minX, $minY, $maxX, $maxY] = getBoundingBox($map);
		$width = ($maxX - $minX) + 1;

		if ($border) {
			echo "\n";

			if (!empty($title)) {
				$titleWidth = max($width, strlen($title));
				$titlePadding = ($titleWidth - strlen($title)) / 2;
				echo '┍', str_repeat('━', $titleWidth), '┑', "\n";
				echo '│', sprintf("%".floor($titlePadding)."s%s%".ceil($titlePadding).'s', '', $title, ''), '│', "\n";
				echo '┕', str_repeat('━', $titleWidth), '┙', "\n";
				echo "\n";
			}

			echo '┍', str_repeat('━', $width), '┑', "\n";
		}
		for ($y = $minY; $y <= $maxY; $y++) {
			if ($border) { echo '│'; }
			for ($x = $minX; $x <= $maxX; $x++) {
				echo isset($map[$y][$x]) ? (strlen($map[$y][$x]) > 1 ? strlen($map[$y][$x]) : $map[$y][$x]) : $empty;
			}
			if ($border) { echo '│'; }
			echo "\n";
		}
		if ($border) {  echo '┕', str_repeat('━', $width), '┙', "\n"; }
	}

	/**
	 * Convert a sparse map into a non-sparse map
	 *
	 * @param $map Sparse map to convert
	 * @return string[] non-sparse version of $map
	 */
	function desparseMap($map): array {
		[$minX, $minY, $maxX, $maxY] = getBoundingBox($map);

		$newMap = [];
		for ($y = $minY; $y < $maxY; $y++) {
			$newMap[$y] = [];
			for ($x = $minX; $x < $maxX; $x++) {
				$newMap[$y][$x] = isset($map[$y][$x]) ? $map[$y][$x] : ' ';
			}
		}

		return $newMap;
	}

	/**
	 * Convert a non-sparse into a sparse map
	 *
	 * @param $map Sparse map to convert
	 * @param $remove (Default: ['.', ' ']) Array of characters to remove.
	 * @return string[] sparse version of $map
	 */
	function sparseMap($map, $remove = ['.', ' ']): array {
		[$minX, $minY, $maxX, $maxY] = getBoundingBox($map);

		$newMap = [];
		for ($y = $minY; $y <= $maxY; $y++) {
			for ($x = $minX; $x <= $maxX; $x++) {
				if (isset($map[$y][$x]) && !in_array($map[$y][$x], $remove)) {
					if (!isset($newMap[$y])) { $newMap[$y] = []; }
					$newMap[$y][$x] = $map[$y][$x];
				}
			}
		}

		return $newMap;
	}

	/**
	 * Get all the permutations of an array of items.
	 * (From: http://stackoverflow.com/a/13194803/310353)
	 *
	 * @param $items Items to get permutations of.
	 * @param $perms Ignore this param, used for recursion when caclulating permutations.
	 * @return string[] All permutations of $items;
	 */
	function getPermutations($items, $perms = []): array {
		if (empty($items)) {
			$return = [$perms];
		} else {
			$return = [];
			for ($i = count($items) - 1; $i >= 0; --$i) {
				$newitems = $items;
				$newperms = $perms;
				[$foo] = array_splice($newitems, $i, 1);
				array_unshift($newperms, $foo);
				$return = array_merge($return, getPermutations($newitems, $newperms));
			}
		}
		return $return;
	}

	/**
	 * Get all the possible combinations of $count numbers that add up to $sum
	 *
	 * @param $count Amount of values required in sum.
	 * @param $sum Sum we need to add up to
	 * @return Generator<string[]> for all possible combinations.
	 */
	function getCombinations($count, $sum) {
	    if ($count == 1) {
			yield array($sum);
	    } else {
	        foreach (range(0, $sum) as $i) {
	            foreach (getCombinations($count - 1, $sum - $i) as $j) {
	                yield array_merge(array($i), $j);
	            }
	        }
		}
	}

	/**
	 * Get all the Sets of the given array.
	 *
	 * @param $array Array to get sets from.
	 * @param $maxLength Ignore sets larger than this size
	 * @param $minLength Ignore sets smaller than this size
	 * @return array[] Array of sets.
	 */
	function getAllSets($array, $maxlength = PHP_INT_MAX, $minlength = 0): array {
		$result = array(array());

		foreach ($array as $element) {
			foreach ($result as $combination) {
				$set = array_merge($combination, array($element));
				if (count($set) <= $maxlength) {
					$result[] = $set;
				}
			}
		}

		return $minlength <= 0 ? $result : array_filter($result, function ($a) use ($minlength) { return count($a) >= $minlength; });
	}

	/**
	 * modulus function that calculates the modulus of a number wrapping
	 * negative results backwards if required.
	 *
	 * @param $num Number
	 * @param $mod Modulus
	 * @return int Answer.
	 */
	function wrapmod($num, $mod): int {
		return (($num % $mod) + $mod) % $mod;
	}

	/**
	 * Sort an array using the given method, and return the result of the sort.
	 *
	 * @param $method Method to use for sorting (eg, 'arsort')
	 * @param $array Array to sort
	 * @param $extra (Default: null) Some of the sorting functions take an extra
	 *               param. (Flags or a function or so.)
	 * @return array $array, but sorted.
	 */
	function sorted($method, $array, $extra = null): array {
		call_user_func_array($method, ($extra == null) ? [&$array] : [&$array, $extra]);
		return $array;
	}

	/**
	 * Check if a string starts with another.
	 *
	 * @param $haystack Haystack to search
	 * @param $needle Needle to search for
	 * @return bool True if $haystack starts with $needle.
	 */
	function startsWith($haystack, $needle): bool {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	/**
	 * Check if a string ends with another.
	 *
	 * @param $haystack Haystack to search
	 * @param $needle Needle to search for
	 * @return bool True if $haystack ends with $needle.
	 */
	function endsWith($haystack, $needle): bool {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	/**
	 * preg_match that returns just the matches.
	 *
	 * @return false|string[] Matches array, or empty array if no matches.
	 */
	function preg_match_return($pattern, $subject, $flags = 0, $offset = 0)/*: false|array*/ {
		if (preg_match($pattern, $subject, $m, $flags, $offset)) {
			return $m;
		} else {
			return FALSE;
		}
	}

	// LCM and GCD from https://stackoverflow.com/questions/147515/least-common-multiple-for-3-or-more-numbers
	/**
	 * Greatest Common Devisor between 2 values
	 *
	 * @param int $a Value A
	 * @param int $b Value B
	 * @return int GCD for A and B.
	 */
	function gcd($a, $b): int {
		$t = 0;
		while ($b != 0){
			$t = $b;
			$b = $a % $b;
			$a = $t;
		}

		return $a;
	}

	/**
	 * Least Common Multiple between 2 values
	 *
	 * @param int $a Value A
	 * @param int $b Value B
	 * @return int LCM for A and B.
	 */
	function lcm($a, $b): int {
		return ($a * $b / gcd($a, $b));
	}


	/**
	 * Do a binary search.
	 *
	 * @param mixed $low Low point to start looking
	 * @param mixed $high High point to start looking.
	 * @param mixed $test Function to check if we have found what we want. Should return <-1|0|1> for <check lower|yes|check higher>
	 * @return int|false Position in list item was found, or false.
	 */
	function doBinarySearch($low, $high, $test)/*: int|false*/ {
		while ($low <= $high) {
			$mid = floor(($low + $high) / 2);

			$testResult = call_user_func($test, $mid);
			if ($testResult === 0) {
				return $mid;
			} else if ($testResult < 0) {
				$high = $mid - 1;
			} else if ($testResult > 0) {
				$low = $mid + 1;
			}
		}

		return false;
	}

	if (!function_exists('str_contains')) {
		function str_contains($haystack, $needle) {
			return stripos($haystack, $needle) !== false;
		}
	}

	/**
	 * Compute and cache a result.
	 *
	 * @param string $key Key to cache with. For example `$key = json_encode([__FILE__, __LINE__, func_get_args()]);`
	 * @param callable $function
	 * @return mixed Result of calling $function
	 */
	function storeCachedResult($key, $function) {
		static $_CACHE = [];

		if (!array_key_exists($key, $_CACHE)) {
			$_CACHE[$key] = is_callable($function) ? $function() : $function;
		}

		return $_CACHE[$key];
	}

	// Remove unneeded stuff when timing.
	if (getenv("TIMED") === FALSE) {
		/**
		 * Get an ascii Wreath as a string.
		 * (Credit to 'jgs' for the original wreath ascii)
		 *
		 * @param $colour Colourise the wreath.
		 * @return string The wreath
		 */
		function getWreath($colour = true): string {
				$canColour = $colour && (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

				if ($canColour) {
					$name = "\033[0m";
					$wreath = "\033[0;32m";
					$bow = "\033[1;31m";
					$berry = "\033[1;31m";
					$reset = "\033[0m";
				} else {
					$reset = $berry = $bow = $wreath = $name = '';
				}

				return <<<WREATH
$wreath           ,...., $reset
$wreath        ,;;:{$berry}o$wreath;;;{$berry}o$wreath;;, $reset
$wreath      ,;;{$berry}o$wreath;'''''';;;;, $reset
$wreath     ,;:;;        ;;{$berry}o$wreath;, $reset
$wreath     ;{$berry}o$wreath;;          ;;;; $reset
$wreath     ;;{$berry}o$wreath;          ;;{$berry}o$wreath; $reset
$wreath     ';;;,  {$bow}_  _$wreath  ,;;;' $reset
$wreath      ';{$berry}o$wreath;;$bow/_\/_\\$wreath;;{$berry}o$wreath;' $reset
$name      $wreath  ';;$bow\_\/_/$wreath;;' $reset
$bow           '//\\\' $reset
$bow           //  \\\ $name     Advent of Code 2023 $reset
$bow          |/    \| $name    - ShaneMcC $reset
$reset

WREATH;
		}

		/**
		 * Get an ascii Tree as a string.
		 * (Credit to 'jgs' for the original tree ascii, this was modified to be
		 * taller)
		 *
		 * @param $colour Colourise the tree.
		 * @return string The tree
		 */
		function getTree($colour = true): string {
				$canColour = $colour && (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

				if ($canColour) {
					$name = "\033[0m";
					$reset = "\033[0m";

					$star = "\033[1;33m";
					$tree = "\033[0;32m";
					$snow = "\033[1;37m";
					$box = "\033[1;30m";
					$led1 = "\033[1;31m";
					$led2 = "\033[1;34m";
					$led3 = "\033[1;35m";
					$led4 = "\033[1;36m";
				} else {
					$reset = $box = $star = $tree = $snow = $led1 = $led2 = $led3 = $led4 = $name = '';
				}

				return <<<TREE
$star             ' $reset
$star           - * - $reset
$tree            /.\ $reset
$tree           /..$led1'$tree\ $reset
$tree          /.$led2'$tree..$led4'$tree\ $reset
$tree          /$led1'$tree.$led3'$tree..\ $reset
$tree         /.$led2'$tree..$led1'$tree.$led4'$tree\ $reset
$tree        /.$led3'$tree..$led2'$tree.$led4'$tree.$led3'$tree\ $reset
$name       $tree /.$led4'$tree..$led1'$tree..$led1'$tree.\ $reset
$snow "'""""$tree/$led1'$tree.$led2'$tree...$led1'$tree..$led3'$tree.\\$snow""'"'" $reset
$tree      /$led2'$tree..$led1'$tree$led4'$tree..$led2'$tree.$led1'$tree.$led2'$tree.\ $name Advent of Code 2023 $reset
$tree      ^^^^^^{$box}[_]$tree^^^^^^ $name - ShaneMcC $reset
$reset

TREE;
		}

		/**
		 * Get an ascii Santa as a string.
		 * (Credit to 'ldb' for the original Santa ascii, this was modified slightly)
		 *
		 * @param $colour Colourise the wreath.
		 * @return string The wreath
		 */
		function getSanta($colour = true): string {
				$canColour = $colour && (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

				if ($canColour) {
					$name = "\033[0m";
					$beard = "\033[1;37m";
					$trim = "\033[1;37m";
					$hat = "\033[1;31m";
					$suit = "\033[1;31m";
					$hands = "\033[1;33m";
					$eyes = "\033[1;34m";
					$nose = "\033[1;31m";
					$buttons = "\033[1;37m";
					$belt = "\033[1;37m";
					$buckle = "\033[1;33m";
					$boots = "\033[1;37m";
					$reset = "\033[0m";
				} else {
					$name = $beard = $trim = $hat = $suit = $hands = $eyes = $nose = $buttons = $belt = $buckle = $boots = $reset = '';
				}

				return <<<SANTA
$hat             ,---.{$trim}_ $reset
$trim           _{$hat}/{$trim}_,_{$hat}\\{$trim}(_) $reset
$trim          (_,_,,_,) $reset
$beard           /$eyes o o {$beard}\ $reset
$beard          /'..{$nose}o{$beard}..'\ $reset
$suit      .--{$beard}(  ' , '  ){$suit}--. $reset
$suit     /  . {$beard}'-.....-'{$suit} .  \ $reset
$suit    (../{$belt}_____{$buttons} : {$belt}_____{$suit}\..) $reset
$hands    (_){$belt}(_____{$buckle}[-]{$belt}_____){$hands}(_) $reset
$suit        |     |     | $reset
$suit        ({$boots}.-.{$suit} / \\ {$boots}.-.{$suit}) $name Advent of Code 2023 $reset
$boots        (___)   (___) $name - ShaneMcC $reset
$reset

SANTA;
		}

		/**
		 * Get an ascii Present as a string.
		 * (Credit to 'jgs' for the original present ascii)
		 *
		 * @param $colour Colourise the present.
		 * @return string The Present
		 */
		function getPresent($colour = true): string {
				$canColour = $colour && (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

				if ($canColour) {
					$name = "\033[0m";
					$box = "\033[0;32m";
					$bow = "\033[1;31m";
					$reset = "\033[0m";
				} else {
					$reset = $box = $bow = $name = '';
				}

				return <<<PRESENT
$bow           .__. $reset
$bow         .(\\\\//). $reset
$bow        .(\\\\()//). $reset
$box    .----{$bow}(\\)\/(/){$box}----. $reset
$box    |{$bow}     ///\\\\\ {$box}    | $reset
$box    |{$bow}    ///||\\\\\ {$box}   | $reset
$box    |{$bow}   //`||||`\\\\ {$box}  | $reset
$box    |{$bow}      ||||{$box}      | $reset
$box    |{$bow}      ||||{$box}      | $reset
$box    |{$bow}      ||||{$box}      | $reset
$box    |{$bow}      ||||{$box}      |$name Advent of Code 2023 $reset
$box    '------{$bow}===={$box}------'$name - ShaneMcC $reset
$reset

PRESENT;
		}


		/**
		 * Get an ascii Snowman as a string.
		 * (Credit to 'jgs' for the original snowman ascii)
		 *
		 * @param $colour Colourise the snowman.
		 * @return string The snowman
		 */
		function getSnowman($colour = true): string {
				$canColour = $colour && (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

				if ($canColour) {
					$name = "\033[0m";
					$snow = "\033[1;37m";
					$hat = "\033[1;31m";
					$eyes = "\033[1;33m";
					$nose = "\033[1;31m";
					$arms = "\033[0;33m";
					$buttons = "\033[1;30m";
					$reset = "\033[0m";
				} else {
					$reset = $name = $snow = $hat = $eyes = $nose = $arms = $buttons = '';
				}

				return <<<SNOWMAN
$snow       *     *   $reset
$snow         $hat    ___  $snow *  $reset
$snow       * $hat  _|___|_  $snow    *  $reset
$snow  *      $hat '={$snow}/{$eyes}a a{$snow}\\{$hat}=' $snow  *  $reset
$snow      *     \\ {$nose}~{$snow} /        *  $reset
$snow    * $arms _\\__{$snow}/ '-' \\{$arms}__/_  $reset
$snow      $arms  /  {$snow}\ $buttons o $snow /{$arms}  \\  $reset
$snow  *       / '---' \\   *  $reset
$snow         |  $buttons  o  $snow  |      *  $reset
$snow   ---.---\\ $buttons  o $snow  /-----.---  $reset
$snow           '-----'`   $name Advent of Code 2023 $reset
$snow                      $name - ShaneMcC $reset
$snow $reset

SNOWMAN;
		}

		/**
		 * Output one of the ascii headers.
		 *
		 * @param $colour Colourise the header.
		 * @return void echos A random header
		 */
		function getAsciiHeader($colour = true): void {
			switch (rand(0,4)) {
				case 0: echo getPresent($colour); break;
				case 1: echo getSanta($colour); break;
				case 2: echo getWreath($colour); break;
				case 3: echo getTree($colour); break;
				case 4: echo getSnowman($colour); break;
			}
		}

		try {
			$__CLI['short'] = "hdtw" . (isset($__CLI['short']) && is_array($__CLI['short']) ? implode('', $__CLI['short']) : '');
			$__CLI['long'] = array_merge(['help', 'file:', 'debug', 'test', 'timed'], (isset($__CLI['long']) && is_array($__CLI['long']) ? $__CLI['long'] : []));
			$__CLIOPTS = @getopt($__CLI['short'], $__CLI['long']);
			if (isset($__CLIOPTS['h']) || isset($__CLIOPTS['help'])) {
				echo getAsciiHeader(), "\n";
				echo 'Usage: ', $_SERVER['argv'][0], ' [options]', "\n";
				echo '', "\n";
				echo 'Valid options:', "\n";
				echo '  -h, --help               Show this help output', "\n";
				echo '  -t, --test               Enable test mode (default to reading input from test.txt not input.txt)', "\n";
				echo '  -d, --debug              Enable debug mode', "\n";
				echo '      --file <file>        Read input from <file>', "\n";
				echo '      --timed              Show calculated run time.', "\n";
				if (isset($__CLI['extrahelp']) && is_array($__CLI['extrahelp'])) {
					echo '', "\n";
					echo 'Additional script-specific options:', "\n";
					foreach ($__CLI['extrahelp'] as $line) { echo $line, "\n"; }
				}
				echo '', "\n";
				echo 'Input will be read from STDIN in preference to either <file> or the default files.', "\n";
				die();
			}
		} catch (Exception $e) { /* Do nothing. */ }
		if (!isset($__CLIOPTS['w']) && !isset($__NOHEADER)) { echo getAsciiHeader(), "\n"; }

		if (isset($__CLIOPTS['timed'])) {
			register_shutdown_function(function() {
				$endTime = microtime(true);
				$time = $endTime - START_TIME;
				$m = floor($time / 60);
				$s = $time - ($m * 60);
				echo "\n", 'PHP Time: ', sprintf('%dm%.3fs', $m, $s), "\n";
			});
		}
	}
