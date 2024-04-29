<?php
/**
* @desc:this file is to return link image
* @param: image file route
* @return: image link properly 
* @author: Miguel p 
* @since: 26/05/2016
*/
require('../../config.php');
global $CFG;

$action = optional_param('action', null, PARAM_ALPHA);
$newdir = optional_param('tocreate', null, PARAM_TEXT);

$basedir = $CFG->dataroot;

if(!is_dir($basedir))
{
	mkdir($basedir, 0777, true);
}
$dirtocreate = $basedir.'/'.$newdir ;
if(!is_dir($dirtocreate))
{
	mkdir($dirtocreate, 0777, true);
}

$uploaddir = $dirtocreate.'/';

switch($action){
	case 'route':
		$route = optional_param('route', null, PARAM_TEXT);
	break;
}
$mime = strtolower( pathinfo($route, PATHINFO_EXTENSION) );
switch($mime){
	case 'jpg':
	case 'jpeg':
	case 'pjpeg':
		$type= 'image/jpeg';
		break;
	case 'png':
		$type= 'image/png';
		break;
	case 'gif':
		$type= 'image/gif';
		break;
}
header('Content-Type: '.$type);
$file=$uploaddir.$route;
$myfile = fopen($file, "r") or die("Unable to open file!");
echo fread($myfile,filesize($file));
fclose($myfile);