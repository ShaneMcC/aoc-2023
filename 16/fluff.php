<?php
	$__CLI['long'] = ['sleep:'];
	$__CLI['extrahelp'] = [];
	$__CLI['extrahelp'][] = '      --sleep <#>          Sleep time between output when drawing';

    function drawEnergised($map, $visited, $redraw = false) {
        global $__CLIOPTS;

        foreach ($visited as $e => $beams) {
            [$x, $y] = explode(",", $e);
            if ($map[$y][$x] == '.') {
                $cell = '?';

                $upDown = (isset($beams['up']) || isset($beams['down']));
                $leftRight = (isset($beams['left']) || isset($beams['right']));

                if ($upDown && $leftRight) { $cell = '╬'; }
                else if ($leftRight) { $cell = '═'; }
                else if ($upDown) { $cell = '║'; }

                $map[$y][$x] = "\033[1;31m" . $cell . "\033[0m";
            }
        }
        if ($redraw) { echo "\033[" . (count($map) + 7) . "A"; }
        drawMap($map, true, 'Energised');
		usleep($__CLIOPTS['sleep'] ?? 100);
    }
