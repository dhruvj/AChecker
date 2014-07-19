<?php

/* * ********************************************************************* */
/* AChecker                                                             */
/* * ********************************************************************* */
/* Copyright (c) 2008 - 2011                                            */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/* * ********************************************************************* */
// $Id$

define('AC_INCLUDE_PATH', '../include/');

include(AC_INCLUDE_PATH . 'vitals.inc.php');
include_once(AC_INCLUDE_PATH . 'classes/Utility.class.php');
include_once(AC_INCLUDE_PATH . 'classes/DAO/GuidelinesDAO.class.php');
include_once(AC_INCLUDE_PATH. "classes/HTMLRpt.class.php");
include_once(AC_INCLUDE_PATH. "classes/HTMLByGuidelineRpt.class.php");
include_once(AC_INCLUDE_PATH. "classes/AccessibilityValidator.class.php");
include_once(AC_INCLUDE_PATH . 'classes/DAO/ChecksDAO.class.php');
include_once(AC_INCLUDE_PATH . 'classes/DAO/UserLinksDAO.class.php');
include_once(AC_INCLUDE_PATH . 'classes/Decision.class.php');
include_once(AC_INCLUDE_PATH . 'classes/Crawler.class.php');

if (!isset($_REQUEST['urls']))
    $msg->addError('EMPTY_URI');

$urls = $_REQUEST["urls"];


if (!isset($_REQUEST['gid']))
    $msg->addError('EMPTY_GID');
else {
    $_gids = $_REQUEST['gid'];
}
//input form
include ("checker_input_form.php");
$generalReport = 1;
include("checker_results.php");
// display footer
include(AC_INCLUDE_PATH.'footer.inc.php');

?>
