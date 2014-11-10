<?php
require 'functions.php';

$samba_share="/path/to/smb/share/";
$file=$samba_share."distance_crs.csv";

//Comma separated emails to send report to
$send_to="me@university.edu,you@university.edu";

// Set start date to the beginning of last week
$start_date = date('Y-m-d',mktime(0, 0, 0, date('n'), date('j')-6, date('Y')) - ((date('N'))*3600*24));     
// Set end date to the end of last week
$end_date = date('Y-m-d',mktime(23, 59, 59, date('n'), date('j'), date('Y')) - ((date('N'))*3600*24));

$month=date('n');
$yearminus=date('Y')-1;
$term=array();

/*
 * This is all about how we name courses. Our academic year runs from August-August, so the Fall 2014 term is actually in 
 * the calendar year 2014. The Spring 2014 term is in the calendar year 2015... confusing, but many schools do it this way.
 * So, you'll see below that we're setting "T" for any term that should be included in the current weekly email report.
 * Our CSV file includes all current and future Distance Education courses.
 *
 *   Here's the header and a single line from our CSV file:
 *   "course_id","short_name","long_name","term_id"
 *   "2013FA-BMKT3311-01","BMKT 3311 01","BMKT 3311 01 - Principles of Marketing","2013FA"
 */

	switch ($month) {
		case 1:
			$term[$yearminus.'SP']="T";
			break;
		case 2:
			$term[$yearminus.'SP']="T";
			break;
		case 3:
			$term[$yearminus.'SP']="T";
			break;
		case 4:
			$term[$yearminus.'SP']="T";
			break;
		case 5:
			$term[$yearminus.'SP']="T";
			$term[$yearminus.'MA']="T";
			break;
		case 6:
			$term[$yearminus.'S1']="T";
			break;
		case 7:
			$term[$yearminus.'S2']="T";
			break;
		case 8:
			$term[date('Y').'FA']="T";
			break;
		case 9:
			$term[date('Y').'FA']="T";
			break;
		case 10:
			$term[date('Y').'FA']="T";
			break;
		case 11:
			$term[date('Y').'FA']="T";
			break;
		case 12:
			$term[date('Y').'FA']="T";
			break;
	}


$importer = new CsvImporter($file,true); 
$data = $importer->get(); 

$output="Academic Related Activities for ".$start_date." through ". $end_date."\n\n";

foreach ($data as $course) {

 if ($term[$course['term_id']]=="T") {
 	$course_id='sis_course_id:'.$course['course_id'];
	$output .= $course['term_id'] . " -- " . $course['long_name'] ."\n";
	$part=count_participations($course_id,list_enrollments($course_id),$start_date,$end_date);
	foreach ($part as $participation) {
		if (strpos($course['term_id'],'FA') !== false || strpos($course['term_id'],'SP') !== false) { $num_participations=1; }
		else { $num_participations=2; }
		
		if ($participation['participations'] < $num_participations ) {
			$output .= $participation['name']."\t".$participation['id']."\t".$participation['participations']."\n";
		}
	}
	$output .= "\n\n";
 }
	
}

// Send the email
$headers = 'From: noreply@umhb.edu' . "\r\n" .
    'Reply-To: noreply@umhb.edu' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail ($send_to,"Academic Related Activities for ".$start_date." through ". $end_date,$output,$headers);


?>