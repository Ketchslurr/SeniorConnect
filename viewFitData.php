<?php
$data = json_decode(file_get_contents('fit_debug.json'), true);
echo '<pre>';
print_r($data);
echo '</pre>';