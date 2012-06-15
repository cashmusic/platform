## Setup and requirements

One of our goals is for this to run in as many places as possible, so we've worked 
hard to keep the requirements as minimal as possible:

 * PHP 5.2.7+
 * PDO and MySQL OR SQLite
 * mod_rewrite (for admin)
 * fopen wrappers OR cURL   


**Installation:** Just fork/clone this repo, cd into the new /DIY folder, and run: 

	make install

choosing SQLite will get you up and running fastest. Next just point Apache or MAMP 
at the /DIY directory and view http://localhost/ in a browser.
  
There's a web installer for non-coders [here](https://github.com/cashmusic/DIY/downloads).
The idea is that we make it easy to deploy on any shared server. 