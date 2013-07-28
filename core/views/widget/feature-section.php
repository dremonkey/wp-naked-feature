<?php
/**
 * Feature Section template
 */
?>

<?php 

if ($style) {
    $path = dirname(__FILE__) . '/styles/' . $style . '.inc';
    if (file_exists($path)) include ($path);
    else echo "$path does not exist.";
}