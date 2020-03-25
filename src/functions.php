<?php

/**
 * Simple "debug" tool
 */
function dd($mixed, $die = true) {
    printf('<pre>%s</pre>', var_export($mixed, true));
	if ($die) {
		die();
	}
}

function sToHuman($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}
