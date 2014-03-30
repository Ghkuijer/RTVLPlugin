<?php
require('../../../wp-blog-header.php');
include('RTVLStreamController.class.php');
$uploads = wp_upload_dir();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>


<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lansingerland FM webcamstream</title>
<link href="RTVLStyle.css" rel="stylesheet" type="text/css" />
<link href="RTVLstreamCSS.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/images/favicon.ico">
<script language="javascript" type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
<script language="javascript" type="text/javascript" src="RTVLJS.js"></script>

</head>

<body style="background-color: #F4F4F4; width:750px;">
    <div class="headerstream"> 
		<img class="logostream" alt="RTV Lansingerland" src="images/logo-fm.png">
	</div>
	<div class="programmaInfo">
		<div class="programmaInfoContainer">
			<div class="programmanaam">
				<strong>Met nu</strong> <br /> <?php $controller = new RTVLStreamController(); echo $controller->getProgramPlaying()->getFeature('naam'); ?>
			</div>
			 <div class="programmabanner">
				<?php
					$src = $uploads['url']."/programmas/".$controller->getProgramPlaying()->getId().".jpg";
					if(!file_exists($src)) $src = $uploads['url']."/programmas/default.jpg";
				?>
				<img src="<?php echo $src; ?>" />
			</div>
		</div>
		<div class="webcamNowPlaying">
			<!-- <strong>Op dit moment op Lansingerland FM:</strong><br />
			<div id="reloadnp">+ evt. playlist</div> -->
		</div>
	</div>
    <div class="flashwebcamstream" background="images/WEBCAM_HOLDER.png" width="567" height="313" alt="WEBCAM_HOLDER"> 
	<?php $controller = new RTVLStreamController(); 
		if($controller->getProgramPlaying()->getFeature('cam') == '1') { 
			$source = "rtmp://85.214.244.159/live/livestream&autoHideControlBar=true&streamType=live&autoPlay=true";
			$height = "313";
		} else {
			$source = "rtmp://85.214.244.159/live/audiostream&autoHideControlBar=false&streamType=live&autoPlay=true";
			$height = "30";
		}
	?>
		<object width='567' height='<?php echo $height; ?>' id='StrobeMediaPlayback' name='StrobeMediaPlayback' type='application/x-shockwave-flash' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' >
		<param name='movie' value='swfs/StrobeMediaPlayback.swf' /> 
		<param name='quality' value='high' /> 
		<param name='background' value='images/WEBCAM_HOLDER.png' /> 
		<param name='allowfullscreen' value='true' /> 
		<param name='flashvars' value= '&src=<?php echo $source; ?>&autoHideControlBar=true&streamType=live&autoPlay=true'/>
		<embed src='swfs/StrobeMediaPlayback.swf' width='567' height='<?php echo $height; ?>' id='StrobeMediaPlayback' quality='high' background='images/WEBCAM_HOLDER.png' name='StrobeMediaPlayback' allowfullscreen='true' pluginspage='http://www.adobe.com/go/getflashplayer' flashvars='&src=<?php echo $source; ?>' type='application/x-shockwave-flash'> </embed>
		</object>
    </div>
 	<a class ="redbutton" style="width:65px;  margin-left:30px; margin-top: 20px; " href="audiostream.php">Terug</a>
    <a class ="redbutton" style="width:65px;  margin-left:30px; " href="/">Nieuws</a>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-48890867-1', 'rtvlansingerland.nl');
  ga('send', 'pageview');

</script>
</body>
</html>