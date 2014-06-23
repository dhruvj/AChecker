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

if (!defined("AC_INCLUDE_PATH"))
    die("Error: AC_INCLUDE_PATH is not defined.");
include_once (AC_INCLUDE_PATH . "lib/simple_html_dom.php");
include_once(AC_INCLUDE_PATH . 'classes/Utility.class.php');

/**
 * Crawler
 * Class for crawling through webpages in a breadth first manner 
 * @access	public
 * @author	Dhruv Jagetiya
 * @package checker
 */
class Crawler {

    //private variables
    var $baseURL;       // Base Url from which crawling begins
    var $levelOfReview; //Maximum level of review
    var $totalLinks;    // Total number of links to be reviewed
    var $linksPerLevel; // Links to be considered per level
    var $linksPerPage;  // Links to be considered per page
    var $domainName;    // Domain of base url
    var $visited = array();    // Visited URLs, don't visit again!
    var $countLinks;

    /**
     * Constructor to initialize the crawling process
     * @param string $URL
     * @param number $levelOfReview
     * @param number $totalLinks
     * @param number $linksPerLevel
     * @param number $linksPerPage
     */

    function Crawler($URL, $levelOfReview, $totalLinks, $linksPerLevel, $linksPerPage) {
        $extractedURL = parse_url($URL);
        $base = $extractedURL['host'];
        // add www if not present
        if (strpos($base, "www.") !== 0) {
            $base = "www." . $base;
        }
        $URL = $extractedURL['scheme'] . "://" . $base . $extractedURL['path'] . ($extractedURL['query'] != '' ? ("?" . $extractedURL['query']) : "");
        $URL = rtrim($URL, "?");
        $this->baseURL = rtrim($URL, "/");
        $this->levelOfReview = $levelOfReview;
        if ($this->levelOfReview == "all") {
            $this->levelOfReview = 0;
        }
        $this->totalLinks = $totalLinks;
        $this->linksPerLevel = $linksPerLevel;
        $this->linksPerPage = $linksPerPage;
    }
    /**
     * Get the html of page
     * @param string $URL
     * @return string, html of the page, similar to file_get_contents function
     */
    function getHTMLfromURL($URL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    /**
     * Check if the content type returned by URL is text/html and Check if URL http code is 200 or not
     * @param string $URL
     * @return boolean
     */
    function isURLOk($URL) {
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $ContentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $HTTPCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if(strpos($ContentType, "text/html") !== false && $HTTPCode == "200") return true;
        else return false;
    }
    /**
     * convert relative url to absolute
     * @param string $rel
     * @param string $base
     * @return absolute url corresponding to given relative url
     */
    function relativeToAbsoluteURL($rel, $base) {
        /* return if already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '')
            return $rel;

        /* queries and anchors */
        if ($rel[0] == '#' || $rel[0] == '?')
            return $base;

        /* parse base URL and convert to local variables:
          $scheme, $host, $path */
        extract(parse_url($base));

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($rel[0] == '/')
            $path = '';

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
            
        }

        /* absolute URL is ready! */
        return $scheme . '://' . $abs;
    }

    /**
     * Checks if a URL is relative or not
     * @param String $URL
     * @return if relative url return true else false
     */
    function isRelativeURL($URL) {
        if (strpos($URL, 'http://') === 0 || strpos($URL, 'https://') === 0) {
            return false;
        }
        return true;
    }

    /**
     * @param string $URL
     * @return an array of all valid(connectable) uri present on a page
     */
    function getAllValidLinks($URL) {
        $extractedURL = parse_url($URL);
        $base = $extractedURL['host'];
        // add www if not present
        if (strpos($base, "www.") !== 0) {
            $base = "www." . $base;
        }
        $URL = $extractedURL['scheme'] . "://" . $base . $extractedURL['path'] . ($extractedURL['query'] != '' ? ("?" . $extractedURL['query']) : "");
        $validuri = array();
        // Create DOM from URL
        $html = str_get_dom($this->getHTMLfromURL($URL));
        // Find all a tags 
        foreach ($html->find('a') as $element) {
            $nextURL = $element->href;
            if ($this->isRelativeURL($nextURL) === true) {   // if it is a relative url then it will be from same domain
                $nextURL = $this->relativeToAbsoluteURL($nextURL, $URL);
                $nextURL = rtrim($nextURL, "?");
                $nextURL = rtrim($nextURL, "/");
                if($this->isURLOk($nextURL) === false) {
                    continue;
                }
                if (in_array($nextURL, $this->visited) === false) {    //don't add same URL again
                    array_push($validuri, $nextURL);
                    array_push($this->visited, $nextURL);
                }
            } else {
                $extractedNextURL = parse_url($nextURL);
                $baseNextURL = $extractedNextURL['host'];
                if (strpos($baseNextURL, "www.") !== 0) {
                    $baseNextURL = "www." . $baseNextURL;
                }
                $nextURL = $extractedNextURL['scheme'] . "://" . $baseNextURL . $extractedNextURL['path'] . ($extractedNextURL['query'] != '' ? ("?" . $extractedNextURL['query']) : "");
                $nextURL = rtrim($nextURL, "?");
                $nextURL = rtrim($nextURL, "/");
                if($this->isURLOk($nextURL) === false) {
                    continue;
                }
                if (strcmp($base, $baseNextURL) == 0) {  //check if it belongs to same domain
                    if (in_array($nextURL, $this->visited) === false) {    //don't add same URL again
                        array_push($validuri, $nextURL);
                        array_push($this->visited, $nextURL);
                    }
                }
            }
            // limit number of url by $this->linksPerPage And don't break when 0(infinity)
            if ((count($validuri) >= $this->linksPerPage) && ($this->linksPerPage != 0))
                break;
            if (($this->countLinks+count($validuri)) >= $this->totalLinks && $this->totalLinks != 0)
                break;
        }
        return $validuri;
    }

    /**
     * Crawling process
     */
    function initiate() {
        $result = array();
        $this->countLinks = 1;    //keep counting of links found, 1 for baseurl
        $idTracker = 0;
        $q = new SplQueue();
        $q->enqueue(array("level" => 0, "url" => $this->baseURL, "id" => $idTracker, "belongsTo" => NULL));
        array_push($this->visited, $this->baseURL);    // Mark the base URL visited
        while (!$q->isEmpty()) {
            $node = $q->dequeue();
            $result[$node["level"]][] = array($node["url"], $node["id"], $node["belongsTo"]);
            $nextLevel = ($node["level"] + 1);    // next level
            //limit the level of review by $this->levelOfReview except 0(infinity)
            if (($this->levelOfReview >= $nextLevel || ($this->levelOfReview == 0)) && ($this->totalLinks > $this->countLinks || $this->totalLinks == 0)) {
                $nextURLs = $this->getAllValidLinks($node["url"]);
                $count = 0;
                foreach ($nextURLs as $url) {
                    ++$idTracker;
                    $q->enqueue(array("level" => $nextLevel, "url" => $url, "id" => $idTracker, "belongsTo" => $node["id"]));
                    ++$count;
                    ++$this->countLinks;
                    if(count($result[$nextLevel]) + $count >= $this->linksPerLevel && $this->linksPerLevel != 0) break;
                    if ($this->countLinks >= $this->totalLinks && $this->totalLinks != 0)
                        break;
                }
            }
        }
        return $result;
    }
}