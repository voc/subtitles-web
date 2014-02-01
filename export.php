<?php
	
	define('LIBRARY', 'Library/');
	include 'Library/Application.php';
	
	requires(
		'HTTP/Client'
	);
	
	define('TRACKER_API', 'https://tracker.fem.tu-ilmenau.de/api/v1/');
	define('AMARA_API', 'http://amara.org/api2/');
	define('PUBLIC_WIKI_API', 'https://events.ccc.de/congress/2013/wiki/api.php');
	
	$blacklist = [
		5711,
		5712,
		5714,
		5591,
		5465,
		5715,
		5612
	];
		
	function getClient() {
		static $client;
		
		if ($client === null) {
			$client = new HTTP_Client();
			$client->setUserAgent('FeM-Subtitle-Export/0.1 (http://fem.tu-ilmenau.de)');
		}
		
		return $client;
	}
	
	function h($string, $double = true) {
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8', $double);
	}
	
	function render($_template, array $_data) {
		extract($_data, EXTR_PREFIX_SAME, '_');
		
		ob_start();
		include 'templates/' . $_template;
		$result = ob_get_clean();
		
		return $result;
	}
	
	function getTalks($project) {
		$client = getClient();
		$response = $client->get(TRACKER_API . $project . '/tickets/fahrplan.json');
		
		return $response->toArray();
	}
	function getAllTalks() {
		return array_merge(getTalks('30c3'), getTalks('30c3-hd'));
	}
	function getAmaraData($event_id) {
		global $client, $amara_ids;
		if ( !isset($amara_ids[$event_id]) ) {
			return NULL;
		}	
		$amara_id = $amara_ids[$event_id];	
		$video = $client->get(AMARA_API . 'partners/videos/' . $amara_id. '/?format=json')
			->toArray();
		if (!isset($video['languages'])) {
			print_r($video);
			return $video;
		}
		
		$removed_empty_lang = false;	
		foreach ($video['languages'] as $key => $language) {
			$subtitle = $client->get(AMARA_API . 'partners/videos/' . $amara_id . '/languages/' . $language['code'] . '/?format=json')
				->toArray();
			if(!isset($subtitle['num_versions']) or $subtitle['num_versions'] < 1) {
				unset ( $video['languages'][$key] );
				$removed_empty_lang = true;
			}
			else {
				$video['languages'][$key] = array_merge($language, $subtitle);			
			}
		}
		if ( $removed_empty_lang ) {
			// removing values from list implicity converts it to a dictonary
			// convert back to list
			$video['languages'] = array_values($video['languages']);
		}
		return $video;
	}

	$amara_ids = [];
	$filenames = [];
	$client = getClient();
	
	foreach (json_decode(file_get_contents('amara.json')) as $filename => $link) {
		$event_id = substr($filename, 5, 4);
		$filenames[$event_id] = substr($filename, 0, -15);
		
		preg_match('/videos\/(.*?)\/info/', $link, $matches);
		$amara_ids[$event_id] = $matches[1];

	}

	$wiki_status = [];
	$wiki_text = json_decode(file_get_contents(PUBLIC_WIKI_API . "?action=parse&page=Projects:Subtitles/status&format=json&section=2&prop=text&disablepp=1"))
		->parse->text->{'*'};
	$wiki_doc = simplexml_load_string('<root>' . $wiki_text . '</root>');
	foreach ( $wiki_doc->table->tr AS $row ) {
		$event_id = trim($row->td[0]);
		$status = trim($row->td[4]); 
		$status_other = trim($row->td[5]);
		
		$wiki_status[$event_id] = $status;  
	}
	unset($wiki_text);
	unset($wiki_doc);

	$tracker_data = getAllTalks();
	$talks = [];
	
	foreach ($tracker_data AS $talk) {
		$event_id = $talk['fahrplan_id']; 
		if (in_array($event_id, $blacklist)) {
    			continue;
  		}	

		echo "  processing " . $event_id . ": " . $talk['title'];
		$talk['filename'] = $filenames[$event_id];
		
		$ticket_id = $talk['id'];
		unset($talk['id']);
		$talk['ticket_id'] = $ticket_id;
		
		if (isset($wiki_status[$event_id])) {
			$talk['wiki_status'] = $wiki_status[$event_id];
		} else {
			$talk['wiki_status'] = "unknown";
			echo " NOT IN WIKI! ";
		}
		$wiki_status[$event_id];
		$amara = getAmaraData($event_id);
		$talk['amara'] = $amara; 
		// todo give warning when completed not marked in wiki
		
		echo ".\n";
		$talks[$event_id] = $talk;

		//echo json_encode($talk, JSON_PRETTY_PRINT);
	}
	//*/	
	file_put_contents('data.json', str_replace('\/', '/', json_encode($talks, JSON_PRETTY_PRINT)));
?>
