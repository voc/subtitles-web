<!DOCTYPE>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>30C3 Subtitles</title>
	<meta charset="utf-8">
	<link href="css/default.css" rel="stylesheet" type="text/css">
</head>
<body>
	<div id="container">
		<div class="rocket"></div>
		<div id="header">
			<div class="logo">
				<a href="./">
				<img src="img/logo.svg"></a>
			</div>
			<div class="headline">30C3: Subtitles</div>
      <div class="links">
        <a href="https://events.ccc.de/congress/2013/wiki/Projects:Subtitles">More info/FAQ</a> |
        <a href="https://events.ccc.de/congress/2013/wiki/Projects:Subtitles/status">Detailed Status</a>
      </div>
		</div>
		<div id="content">
			<div class="text clearfix">
				<table class="subtitles">
					<thead>
						<th>VID</th>
						<th>Title</th>
						<th></th>
						<th></th>
						<th></th>
					</thead>
					<tbody>
						<?php foreach ($talks as $talk):
							if (in_array($talk['fahrplan_id'], $blacklist)) {
								continue;
							} ?>
							<tr>
								<td><?= h($talk['fahrplan_id']); ?></td>
								<td><?= h($talk['title']); ?></td>
								<td><a href="http://subtitles.pads.ccc.de/<?= h($talk['fahrplan_id']); ?>">Pad</a></td>
								<td>
									<?php if (isset($amara[$talk['fahrplan_id']])): ?>
										<a href="<?php echo $amara[$talk['fahrplan_id']]; ?>">Video on amara.org</a>
									<?php else: ?>
										–
									<?php endif; ?>
								</td>
								<td>
									<?php if (isset($subtitles[$talk['fahrplan_id']])): ?>
										<?php foreach ($subtitles[$talk['fahrplan_id']] as $language => $complete): ?>
											<span class="language<?= ($complete)? ' complete' : ''; ?>"><?= $language . (($complete)? '✓' : '✗'); ?></span>
										<?php endforeach; ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<div id="footer">
			<div class="links">
				<a href="http://streaming.media.ccc.de/wiki/">Wiki-Mirror</a>
				|
				<a href="http://streaming.media.ccc.de/congress/2013/Fahrplan/schedule.html">Fahrplan-Mirror</a>
				|
				<a href="https://twitter.com/c3streaming">Twitter</a>
			</div>
			<div class="voc_logo">
				<a href="http://ccc.de">
					<img alt="CCC Video Operations Center" src="img/voc_logo.svg">
				</a>
			</div>
			<div class="fem_logo">
				<a href="http://fem.tu-ilmenau.de">
					<img alt="Forschungsgemeinschaft elektronische Medien" src="img/fem_logo.svg">
				</a>
			</div>
		</div>
	</div>
</body>
</html>
