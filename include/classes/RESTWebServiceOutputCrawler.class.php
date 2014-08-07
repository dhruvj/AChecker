<?php
/************************************************************************/
/* AChecker                                                             */
/************************************************************************/
/* Copyright (c) 2008 - 2011                                            */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id$

/**
* RESTWebServiceOutputCrawler
* Class to generate error report in REST format 
* @access	public
* @author	Dhruv Jagetiya
* @package checker
*/
if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");

include_once(AC_INCLUDE_PATH.'classes/DAO/ChecksDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/UserLinksDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelinesDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/UserDecisionsDAO.class.php');

class RESTWebServiceOutputCrawler {

	// all private
	var $userLinkID;                // parameter. user_links.user_link_id
	var $graph;
	var $output;                    // final web service output
    var $rootURL;
	
	// REST templates
	var $rest_main =
'<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE resultset[
<!ELEMENT resultset (summary,result)>
<!ELEMENT summary (rootURL, NumOfURL,sessionID)>
<!ELEMENT rootURL (#PCDATA)>
<!ELEMENT NumOfURL (#PCDATA)>
<!ELEMENT sessionID (#PCDATA)>
<!ELEMENT result (level)*>
<!ELEMENT level (URLcount, levelNum, URLs)>
<!ELEMENT URLcount (#PCDATA)>
<!ELEMENT levelNum (#PCDATA)>
<!ELEMENT URLs (URL)*>
<!ELEMENT URL (id, belongsTo, value)>
<!ELEMENT id (#PCDATA)>
<!ELEMENT belongsTo (#PCDATA)>
<!ELEMENT value (#PCDATA)>

]>
<resultset>
  <summary>
    
    <rootURL>{ROOTURL}</rootURL>
    <NumOfURL>{NUMOFURL}</NumOfURL>
    <sessionID>{SESSIONID}</sessionID>
    
  </summary>

  <result>
        {LEVEL}
  </result>
</resultset>
';
	
	
	var $rest_level = 
'
        <level>
            <URLcount>{URLCOUNT}</URLcount>
            <levelNum>{LEVELNUM}</levelNum>
            <URLs>
                {URL}
            </URLs>
        </level>
';
    var $rest_url = 
'
        <URL>
            <id>{ID}</id>
            <belongsTo>{BELONGSTO}</belongsTo>
            <value>{VALUE}</value>
        </URL>
';
	
	
	/**
	* public
	* $rootURL: root url from which crawler began
	* $graph: crawler's output
	* $guideline_array: an array of guideline ids
	*/
	function RESTWebServiceOutputCrawler($rootURL, $graph, $userLinkID) {
        $this->rootURL = $rootURL;
		$this->graph = $graph;
		$this->userLinkID = $userLinkID;
		
		$this->generateRESTRpt();
	}
	
	/**
	* private
	* main process to generate report in rest format
	*/
	private function generateRESTRpt() {
		$result = "";
        $levels = "";
        $count = 0;
        $urlCount = 0;
        foreach($this->graph as $level) {
            $levelNum = $count++;
            $urls = "";
            foreach ($level as $url) {
                ++$urlCount;
                $urls .= str_replace(
                                    array(
                                        '{ID}', 
                                        '{BELONGSTO}', 
                                        '{VALUE}'
                                    ), 
                                    array(
                                        $url[1], 
                                        $url[2], 
                                        $url[0]
                                    ), 
                                    $this->rest_url
                                );
            }
            $levels .= str_replace(array(
                                        '{URLCOUNT}',
                                        '{LEVELNUM}', 
                                        '{URL}'
                                    ), 
                                    array(
                                        count($level),
                                        $levelNum,
                                        $urls
                                    ),
                                    $this->rest_level); 
        }
        $userLinksDAO = new UserLinksDAO();
		$row = $userLinksDAO->getByUserLinkID($this->userLinkID);
		$sessionID = $row['last_sessionID'];
        
        $result = str_replace(
                                array(
                                    '{ROOTURL}',
                                    '{NUMOFURL}',
                                    '{LEVEL}',
                                    '{SESSIONID}'), 
                                array(
                                    $this->rootURL, 
                                    $urlCount, 
                                    $levels,
                                    $sessionID
                                    ), 
                                $this->rest_main
                            );
        $this->output = $result;
	}
	
	/** 
	* public
	* return final web service output
	* parameters: none
	* author: Dhruv Jagetiya
	*/
	public function getWebServiceOutput() {
		return $this->output;
	}
	

}
?>