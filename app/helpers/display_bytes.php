<?php

function display_bytes(float $bytes): string
{
    $units = ["b", "kb", "mb", "gb", "tb"];
	$new_unit = "b";
	$n_units = count($units);

	for ($i = 0; $i < $n_units; $i++) {
		$new_bytes = $bytes / 1024;
		if ($new_bytes < 1) {
			break;
		}

		$bytes = $new_bytes;
		
		$u = $i + 1;
		if ($u >= $n_units) 
		    $u = $u - $n_units; 
		   
		$new_unit = $units[$u];
	}

    $new_unit = strtoupper($new_unit);
    $formatted = number_format($bytes, 2, ",", " ");
    $formatted .= " $new_unit";

	return $formatted;
}
