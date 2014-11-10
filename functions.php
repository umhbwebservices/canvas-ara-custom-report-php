<?php

//Your Canvas Access Token
$access_token = "YOUR-CANVAS-ACCESS-TOKEN";

// check file freshness
function file_fresh ($file) {
	$diff=time()-filemtime($file);
	if (file_exists($file)) {
		if ($diff < 604800) {
			return TRUE;			
		}
		else {
			return FALSE;
		}
	}
	else {
		return FALSE;
	}
}

// list enrollments
function list_enrollments ( $CourseID ){
	global $access_token;
	
	//get it ready for later
	$enrolled=array();
	
	$url="https://umhb.instructure.com/api/v1/courses/".$CourseID."/enrollments?per_page=100&access_token=".$access_token;
	
	// Get cURL resource
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_CUSTOMREQUEST => 'GET',
	    CURLOPT_URL => $url,
	    CURLOPT_USERAGENT => 'Matt Loves cURLing Things'
	));
	// Send the request & save response to $resp
	$resp = json_decode(curl_exec($curl));
	
	//print_r($resp);
	
	// Close request to clear up some resources
	curl_close($curl);

	$counter=0;
	foreach ($resp as $enrollment) {
		if ($enrollment->type=="StudentEnrollment" && $enrollment->user->name != "Test Student") {
			$enrolled[$enrollment->user->sis_user_id]=array('name'=>$enrollment->user->name,'id'=>$enrollment->user->sis_user_id,'participations'=>0);
		}
		$counter++;
	}	
	return $enrolled;
}

// count_participations
function count_participations ( $CourseID, $enrolled, $start_date, $end_date ){
	global $access_token;
	
	//echo "Total Enrollments for course: ".count($enrolled);
	
	$counter=0;
	foreach ($enrolled as $student) {
		
		$url = "https://umhb.instructure.com/api/v1/courses/".$CourseID."/analytics/users/sis_user_id:".$student['id']."/activity";
		
		// Get cURL resource
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_CUSTOMREQUEST => 'GET',
		    CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $access_token), 
		    CURLOPT_URL => $url,
		    CURLOPT_USERAGENT => 'Matt Loves cURLing Things'
		));
		// Send the request & save response to $resp
		$resp = json_decode(curl_exec($curl));
		// Close request to clear up some resources
		curl_close($curl);
		
		foreach ($resp->participations as $singleparicipation) {
			$participation_time=strtotime($singleparicipation->created_at);
			
			//only count participations in the specified date range
			if ($participation_time >= strtotime($start_date) && $participation_time <= strtotime($end_date)) {
				$enrolled[$student['id']]['participations']=$enrolled[$student['id']]['participations']+1;
				$counter++;
			}	
		}
	}
	return $enrolled;

}


/*
 * 
 * CSV Importer Yo!
 * 
 */

class CsvImporter 
{ 
    private $fp; 
    private $parse_header; 
    private $header; 
    private $delimiter; 
    private $length; 
    //-------------------------------------------------------------------- 
    function __construct($file_name, $parse_header=false, $delimiter=",", $length=8000) 
    { 
        $this->fp = fopen($file_name, "r"); 
        $this->parse_header = $parse_header; 
        $this->delimiter = $delimiter; 
        $this->length = $length; 
        $this->lines = $lines; 

        if ($this->parse_header) 
        { 
           $this->header = fgetcsv($this->fp, $this->length, $this->delimiter); 
        } 

    } 
    //-------------------------------------------------------------------- 
    function __destruct() 
    { 
        if ($this->fp) 
        { 
            fclose($this->fp); 
        } 
    } 
    //-------------------------------------------------------------------- 
    function get($max_lines=0) 
    { 
        //if $max_lines is set to 0, then get all the data 

        $data = array(); 

        if ($max_lines > 0) 
            $line_count = 0; 
        else 
            $line_count = -1; // so loop limit is ignored 

        while ($line_count < $max_lines && ($row = fgetcsv($this->fp, $this->length, $this->delimiter)) !== FALSE) 
        { 
            if ($this->parse_header) 
            { 
                foreach ($this->header as $i => $heading_i) 
                { 
                    $row_new[$heading_i] = $row[$i]; 
                } 
                $data[] = $row_new; 
            } 
            else 
            { 
                $data[] = $row; 
            } 

            if ($max_lines > 0) 
                $line_count++; 
        } 
        return $data; 
    } 
    //-------------------------------------------------------------------- 

} 

?>