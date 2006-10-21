<?php	
	/*
	  my custom template
	*/
?>

<?php writeDocType();?>
<html>
<?php writeHeader();?>
	<body>
		<div id="container">
		
<?php if($area != '_gallery') { ?>
			<div id="header">
<?php writeTitle();?>
<?php writeMenu();?>
			</div>
<?php }?>

			<div id="data<?=$area?>">
		
<?php if($area == '') { ?>
				<div id="sidepane">
<?php writeIntroDiv();?>
<?php writeFeedDiv();?>
<!-- my Google Reader list -->
<script type="text/javascript" src="http://www.google.com/reader/ui/publisher.js"></script>
<script type="text/javascript" src="http://www.google.com/reader/public/javascript/user/17487146216998869073/state/com.google/starred?n=5&callback=GRC_p(%7Bc%3A'blue'%2Ct%3A'Lately%2C%20I%20liked%20reading...'%2Cs%3A'true'%7D)%3Bnew%20GRC"></script>
				</div>
<?php } ?>
		
				<div id="mainpane">
<?php if($area == '_gallery') { ?><a href="../../">nicolashoening.de</a><?php }?>
<?php writeData();?>
				</div>
		
			</div>
<?php writeFooter();?>
		</div>
	</body>
</html>



