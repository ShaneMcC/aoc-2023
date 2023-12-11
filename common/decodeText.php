<?php

	// This looks like it should be considered to be 4x6, but some letters use
	// all 5 columns (Y), whereas the rest do not.
	$encodedChars[5][6] = ['011001001010010111101001010010' => 'A',
	                       '111001001011100100101001011100' => 'B',
	                       '011001001010000100001001001100' => 'C',
	                       '111001001010010100101001011100' => 'D',
	                       '111101000011100100001000011110' => 'E',
	                       '111101000011100100001000010000' => 'F',
	                       '011001001010000101101001001110' => 'G',
	                       '100101001011110100101001010010' => 'H',
	                       '011100010000100001000010001110' => 'I',
	                       '001100001000010000101001001100' => 'J',
	                       '100101010011000101001010010010' => 'K',
	                       '100001000010000100001000011110' => 'L',
	                       '' => 'M',
	                       '' => 'N',
	                       '011001001010010100101001001100' => 'O',
	                       '111001001010010111001000010000' => 'P',
	                       '' => 'Q',
	                       '111001001010010111001010010010' => 'R',
	                       '011101000010000011000001011100' => 'S',
	                       '' => 'T',
	                       '100101001010010100101001001100' => 'U',
	                       '' => 'V',
	                       '' => 'W',
	                       '' => 'X',
	                       '100011000101010001000010000100' => 'Y',
	                       '111100001000100010001000011110' => 'Z',
	                       '000000000000000000000000000000' => ' ',
	                      ];

	// Guesses from https://github.com/SizableShrimp/AdventOfCode2022/blob/main/src/util/java/me/sizableshrimp/adventofcode2022/helper/LetterParser.java
	$encodedChars[5][6]['111000100001000010000100011100'] = 'I';
	$encodedChars[5][6]['100101111011110100101001010010'] = 'M';
	$encodedChars[5][6]['100101101010110100101001010010'] = 'N';
	$encodedChars[5][6]['011001001010010100101010001010'] = 'Q';
	$encodedChars[5][6]['011101000001100000100001011100'] = 'S';

	// Unofficial characters found in custom inputs in the past.
	// 5-Wide I
	$encodedChars[5][6]['111110010000100001000010011111'] = 'I';

	// askalski: https://www.reddit.com/r/adventofcode/comments/5h9sfd/2016_day_8_tampering_detected/
	$encodedChars[5][6]['100101001011010101101001010010'] = 'N';
	$encodedChars[5][6]['111110010000100001000010000100'] = 'T';
	$encodedChars[5][6]['011100010000100001000010000100'] = 'T'; // 3-Wide of T just in-case
	$encodedChars[5][6]['100011000110001010100101000100'] = 'V';

	// p_tseng: https://www.reddit.com/r/adventofcode/comments/5h571u/2016_day_8_generate_an_input/day4ctx/
	$encodedChars[5][6]['100011100110101101011001110001'] = 'N';

	// 4x6 version. Remove last column of empty spaces, and any letters that
	// are too wide.
	$encodedChars[4][6] = [];
	foreach ($encodedChars[5][6] as $code => $char) {
		$c4x6 = preg_replace('/(.{4})./', '$1', $code);
		$c4x60 = preg_replace('/(.{4})./', '$10', $code);
		if ($c4x60 != $code) { // Remove any characters using the 5th column.
			$encodedChars[4][6][$c4x6] = $char;
		}
	}

	/**
	 * Decode some ascii text in AoC font.
	 *
	 * @param $image The image input
	 * @param $width How wide is each character
	 * @param $height How tall is each character
	 * @param $gap How much gap between each character
	 * @return string The decoded text
	 */
	function decodeText($image, $width = 5, $height = 6, $gap = 0): string {
		global $encodedChars;

		$text = '';
		$charCount = ceil((is_array($image[0]) ? count($image[0]) : strlen($image[0])) / ($width + $gap));
		$chars = [];

		if (!isset($encodedChars[$width][$height])) { return str_repeat('?', $charCount);  }
		$encChars = $encodedChars[$width][$height];

		foreach ($image as $row) {
			for ($i = 0; $i < $charCount; $i++) {
				$c = is_array($row) ? implode('', array_slice($row, ($i * ($width + $gap)), $width)) : substr($row, ($i * ($width + $gap)), $width);
				$c = str_pad(preg_replace(['/(█|[^.\s0])/', '/[.\s0]/'], [1, 0], $c), $width, '0');
				$chars[$i][] = $c;
			}
		}

		foreach ($chars as $c) {
			$id = implode('', $c);
			if (isDebug() && !isset($encChars[$id])) { echo 'Unknown Letter: ', $id, "\n"; }
			$text .= isset($encChars[$id]) ? $encChars[$id] : '?';
		}

		return $text;
	}
