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
include_once(AC_INCLUDE_PATH . 'classes/DAO/ChecksDAO.class.php');
include_once(AC_INCLUDE_PATH . 'classes/DAO/UserLinksDAO.class.php');
include_once(AC_INCLUDE_PATH . 'classes/Decision.class.php');
include_once(AC_INCLUDE_PATH . 'classes/Crawler.class.php');

if (!isset($_REQUEST['urls']))
    $msg->addError('EMPTY_URI');

$urls = $_REQUEST["urls"];

$guidelinesDAO = new GuidelinesDAO();

if (!isset($_REQUEST['gid']))
    $msg->addError('EMPTY_GID');

if (!$msg->containsErrors()) {
    $_SESSION['input_form']['gids'] = $_gids;
} //else echo "yp"; todo!!
//print_r($_gids); todo!!
if (isset($_REQUEST["enable_html_validation"])) {
    include_once(AC_INCLUDE_PATH . "classes/HTMLValidator.class.php");
    $_SESSION['input_form']['enable_html_validation'] = true;
}
if (isset($_REQUEST["enable_css_validation"])) {
    include_once(AC_INCLUDE_PATH . "classes/CSSValidator.class.php");
    $_SESSION['input_form']['enable_css_validation'] = true;
}

?>
