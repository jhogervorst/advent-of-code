<?php

declare(strict_types=1);

$sum = 0;

$fp = fopen("input.txt", "r");
while ($line = fgets($fp)) {
	$line = trim($line);
	preg_match("/^[^\d]*(\d)/", $line, $m1);
	preg_match("/(\d)[^\d]*$/", $line, $m2);
	$value = (int) ($m1[1] . $m2[1]);
	$sum += $value;
	echo "{$value} - {$line}\n";
}

echo "Sum: {$sum}\n";
