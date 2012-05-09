<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>php_klout</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <style type="text/css">
      .name {
        width: 150px;
        margin: 10px;
        float: left;
      }
      .value {
        width: 400px;
        margin: 10px;
        float: left;
      }
      .row {
        width: 100%;
        float: left;
      }
    </style>
  </head>

  <body>


<?php if(!empty($ksid)) { ?>

	<?php if(isset($twname)) { ?>
		<div class="row">
			<div class="value">
				<p>We're going to lookup the twitter profile of <b><?php echo $twname;?></b></p>
			</div>
		</div>
	<?php } ?>
	<div class="row">
		<div class="name">
			Klout ID
		</div>
		<div class="value">
			<?php echo $ksid;?>
		</div>
	</div>
	<div class="row">
		<div class="name">
			Klout Score
		</div>
		<div class="value">
			<?php echo number_format($score, 2, '.', '');?>
		</div>
	</div>
	<div class="row">
		<div class="name">
			Score Changes
		</div>
		<div class="value">
			Change today: <?php echo number_format($delta['dayChange'], 2, '.', '');?><br>
			Change Last week: <?php echo number_format($delta['weekChange'], 2, '.', '');?><br>
			Change last month:<?php echo number_format($delta['monthChange'], 2, '.', '');?><br>
		</div>
	</div>
	<div class="row">
		<div class="name">
			Topics
		</div>
		<div class="value"><ul>
			<?php 
				for($i = 0;$i < sizeof($topics);++$i){
					echo '<li>' . $topics[$i]['displayName'] . '<BR>';
			} ?></ul>
		</div>
	</div>
	<div class="row">
		<div class="name">
			People who influence this user<br>
			In total, there are <?php echo $inflrc;?> people who are influences
		</div>
		<div class="value"><ul>
			<?php 
				for($i = 0;$i < sizeof($inflr);++$i){
					$payload = $inflr[$i]['entity']['payload'];
					echo '<li>' . $payload['kloutId'] . ' &lt;- ' . number_format($payload['score']['score'], 2, '.', '') . '<BR>';
			} ?></ul>
		</div>
	</div>
	<div class="row">
		<div class="name">
			People who are influenced by this user<br>
			In total, there are <?php echo $inflec;?> people who are influenced
		</div>
		<div class="value"><ul>
			<?php 
				for($i = 0;$i < sizeof($infle);++$i){
					$payload = $infle[$i]['entity']['payload'];
					echo '<li>' . $payload['kloutId'] . ' &lt;- ' . number_format($payload['score']['score'], 2, '.', '') . '<BR>';
			} ?></ul>
		</div>
	</div>
<?php } else { ?>
	<div class="row">
		<div class="value"><ul>
			<p>Sorry but we couldn't locate the user you're searching for.</p>
		</div>
	</div>
<?php } ?>

  </body>
</html>
