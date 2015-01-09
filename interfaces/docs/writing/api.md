Our API is a standalone app located in the repo at **/interfaces/api/** and available
at the /api/ URL at cashmusic.org and locally for testing. Like the admin app, all 
configuration is handled by the constants.php file located in the app root, and all 
requests are routed through the controller.php script via mod_rewrite. 

The main API functionality is handled by the **APICore** class, located at 
**/interfaces/api/classes/APICore.php**.

The API returns JSON objects for any request. Simply requesting **/** at the **/api**
gives a basic hello with version number:

<script src="https://gist.github.com/jessevondoom/b51b3ec5bee653d46cff.js"></script>

A bad request returns a more standard format with status code and messages:

<script src="https://gist.github.com/jessevondoom/c01eaae218cb6129acbf.js"></script>

We'll detail succesful requests in the verbose and RESTful docs below.