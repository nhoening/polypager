<?php writeDocType();?>
<html>
<?php writeHeader();?>
	<body>
		<div id="container">

			<div id="header">
<?php writeTitle();?>
<?php writeMenu();?>
			</div>

			<div id="data<?=$area?>">
		
<?php if($area == '') { ?>
				<div id="sidepane">
<?php writeIntroDiv();?>
<?php writeFeedDiv();?>
				</div>
<?php } ?>
		
				<div id="mainpane">
<?php writeData();?>
				</div>
		
			</div>
<?php writeFooter();?>
		</div>
	</body>
</html>
