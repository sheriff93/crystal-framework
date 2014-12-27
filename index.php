<?php
/**
 * Crystal Framework - index file
 * 
 * @author Krystian Biela <1bitam1@gmail.com>
 * @version 0.8
 * @copyright Copyright (c) Krystian Biela 2013
 */

require_once('./core/indexRouter.php');




$ir = new indexRouter;
$ir -> controller();
