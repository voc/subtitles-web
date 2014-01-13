<?php
	
	define('LIBRARY', 'Library/');
	include 'Library/Application.php';
	
	requires(
		'HTTP/Client'
	);
	
	define('TRACKER_API', 'https://tracker.fem.tu-ilmenau.de/api/v1/');
	
	define('AMARA_API', 'http://amara.org/api2/');
	
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
	
	$amara = [];
	$subtitles = [];
	$client = getClient();
	
	foreach (json_decode(file_get_contents('amara.json')) as $url => $link) {
		$id = substr($url, 5, 4);
		$amara[$id] = $link;
		
		preg_match('/videos\/(.*?)\/info/', $link, $matches);
			
		$video = $client->get(AMARA_API . 'partners/videos/' . $matches[1] . '/?format=json')
			->toArray();
		
		if (!isset($video['languages'])) {
			print_r($video);
			continue;
		}
		
		foreach ($video['languages'] as $language) {
			$subtitle = $client->get(AMARA_API . 'partners/videos/' . $matches[1] . '/languages/' . $language['code'] . '/?format=json')
				->toArray();
			
			if (!isset($subtitle['num_versions']) or $subtitle['num_versions'] < 1) {
				continue;
			}
			
			if (!isset($subtitles[$id])) {
				$subtitles[$id] = [];
			}
			
			$subtitles[$id][$language['code']] = $subtitle['subtitles_complete'];
		}
	}
	
	$talks = array_merge(getTalks('30c3'), getTalks('30c3-hd'));
	
	file_put_contents(
		'files/index.html',
		render(
			'index.tpl',
			[
				'amara' => $amara,
				'blacklist' => $blacklist,
				'subtitles' => $subtitles,
				'talks' => $talks
			]
		)
	);

  // wiki export
  /*
  file_put_contents(
		'files/wiki_export.html',
		render(
			'wiki_export.tpl',
			[
				'amara' => $amara,
				'blacklist' => $blacklist,
				'subtitles' => $subtitles,
				'talks' => $talks
			]
		)
  );
     */

?>
