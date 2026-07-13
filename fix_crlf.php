<?php
$f = 'start.sh';
file_put_contents($f, str_replace("\r\n", "\n", file_get_contents($f)));
$f = 'Dockerfile';
file_put_contents($f, str_replace("\r\n", "\n", file_get_contents($f)));
echo "Done.";
 