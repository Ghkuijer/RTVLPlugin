<?php	
require("../../../wp-blog-header.php");
include('RTVLStreamController.class.php');
$uploads = wp_upload_dir();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RTV Lansingerland Livestream</title>
<link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/images/favicon.ico">
<link href="RTVLStyle.css" rel="stylesheet" type="text/css" />
<link href="RTVLstreamCSS.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
<script language="javascript" type="text/javascript" src="RTVLJS.js"></script></head>
<body style="background-color: #F4F4F4; width:750px;">    
	<div class="headerstream"> 		
		<img class="logostream" alt="RTV Lansingerland" src="images/logo-fm.png" />	
	</div>    
	<div class="linkerkolomaudiostream">        
		<div class="programmanaam">        	
			<strong>Met nu</strong> 
			<br /> 
			<?php $controller = new RTVLStreamController(); echo $controller->getProgramPlaying()->getFeature('naam'); ?>        
		</div>         
		<div class="programmabanner">			
			<?php				
				$src = $uploads['url']."/programmas/".$controller->getProgramPlaying()->getId().".jpg";
				if(!file_exists($src)) 
					$src = $uploads['url']."/programmas/default.jpg";			
			?>        	
			<img src="<?php echo $src; ?>" />        
		</div>        
		<div class="flashaudiostream">
			<object id='StrobeMediaPlayback' width="330" height="30" id="stream" align="middle" name='StrobeMediaPlayback' type='application/x-shockwave-flash' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' >
				<param name='movie' value='swfs/StrobeMediaPlayback.swf' />
				<param name='quality' value='normal' />
				<param name='bgcolor' value='#000000' />
				<param name='allowfullscreen' value='false' />
				<param name='flashvars' value= '&src=rtmp://85.214.244.159/live/audiostream&autoHideControlBar=false&streamType=live&autoPlay=true'/>
				<embed src='swfs/StrobeMediaPlayback.swf' width='330' height='30' id='StrobeMediaPlayback' quality='high' bgcolor='#000000' name='StrobeMediaPlayback' allowfullscreen='false' pluginspage='http://www.adobe.com/go/getflashplayer' flashvars='&src=rtmp://85.214.244.159/live/audiostream&autoHideControlBar=false&streamType=live&autoPlay=true' type='application/x-shockwave-flash'></embed>
			</object>
		</div>       
		<div class="playlist">
			Op dit moment op Lansingerland FM:
			<br />
			<div id="reloadnp">
				Loading...
			</div>
		</div>
		<div class="players">
        	Problemen met deze speler? Probeer de stream voor 
            <a href="http://www.rtvlansingerland.nl/streams/lfm.m3u">Windows Media Player</a>, 
            <a href="http://www.rtvlansingerland.nl/streams/lfm.m3u">Winamp</a> of
            <a href="http://www.rtvlansingerland.nl/streams/lfm.pls">iTunes</a>.
       </div>
       <a class ="redbutton" style="width:65px; margin-left:30px; margin-top: 10px;" href="webcamstream.php">Webcam</a>
       <a class ="redbutton" style="width:65px; margin-left:30px; margin-top: 10px" href="/">Nieuws</a>
    </div>  	
    <div class="rechterkolomaudiostream">
    	<div class="post-info" style="width:300px; text-align:center"> Advertentie </div>
        <div class="banneraudiostream"> 
            <?php echo do_shortcode('[wp_bannerize group="livestream" random="1" limit="1"]'); ?>
        </div>
    </div>
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