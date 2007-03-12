<?php	
	/*	this is the PolyPager default template. 
		It fits best to the picswap-skin
	
		For a overview of the writeX()-methods, see the FAQ on 
		http://polypager.nicolashoening.de?FAQ
		
	You can see here the use of the $area - variable. it can have three values:
	-'' for the normal public area
	-'_gallery' for the gallery
	-'_admin' for the page sin admin area
	it is used here to alter CSS IDs as well as to hide parts of the output
	with a simple if($area == 'x') {} - statement
	*/
?>

<?php writeDocType();?>
<html>
<?php writeHeader();?>
	<body>
        <div id="top"><a href="./"><div id="logo"></div></a></div>
        <div id="container">
			<div id="header">
<?php writeTitle();?>
<?php writeMenu();?>
			</div>

			<div id="data<?=$area?>">

<?php if($area == '') { ?>
				<div id="sidepane">

<?php writeSearchBox();?>
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
        <div id="bottom">&nbsp;</div>
	</body>
</html>
