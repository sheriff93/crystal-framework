<?php
/**
 * Crystal Framework - index file
 * 
 * @author Krystian Biela <1bitam1@gmail.com>
 * @version 0.8
 * @copyright Copyright (c) Krystian Biela 2013
 */

//phpinfo();
require_once('./core/indexRouter.php');



//$mt = microtime();

$ir = new indexRouter;
$ir -> controller();

//print_r($ir);

//echo(microtime() - $mt);








