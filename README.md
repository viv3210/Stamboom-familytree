# familytree
 Code to create family tree from GEDCOM file to HTML

Files: 
- stamboomdb\createfile.php
   Runs on IIS with php
      Current version: PHP Version 7.3.25
   Takes *.ged file as input
   Guesses name for familytree file
   Outputs to *.sql file which needs to be imported
- stamboom\index.php
    php file to show stamboom (familytree) button plus other info
- stamboom\stamboom.php
    creates html5 to draw stamboom (familytree)
	gets data from sql database
- stamboom\stamboom.js
    draws the stamboom (familytree) based on the information put in the web page created by stamboom.php