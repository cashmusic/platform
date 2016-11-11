Connections are basically API wrappers — an abstracted way for us to connect to third
party services, categorize their offering, and define the data we need to store to make
each one work. (Tokens, settings, etc.)

Defining a connection happens in two different files:

 1. A definition JSON in **/framework/settings/connections**
 2. A Seed Class in **/framework/classes/seeds**

The connection is defined in JSON and should look something like this:

 <script src="https://gist.github.com/jessevondoom/2908c44b88db934aeec5.js"></script>

It needs a name, description, unique type, and you must define the classname of the seed
used with the connection. There's an option for scope. This lets us narrow the connection
type so we can show connections contextually in the admin app and beyond. (Currently, the
scope types have been chosen fairly arbitrarily, though we need to define them better going
forward.) Lastly, there's an array listing compatibility — does the connection work in 
single-user mode, multi-user mode, or both?

Note that you can define different data needed for single-user and multi-user versions
of the connection. (We frown on storing keys/secrets in multi-user mode.)

#### The Seed class
In the larger scheme of the platform, Plants handle requests while Seeds handle specific 
functionality — mostly in the form of connecting to third party APIs. Because seeds are 
supposed to be flexible, they're pretty arbitrary in structure, but you'll notice a bunch
of common functions between similar seeds — check seeds for S3 and Google Drive. 

We'll be defining this more concretely on a scope-by-scope basis soon, but for now please 
look for similar services and pattern after them. We want as much uniformity as we can get 
at the seed level so we can abstract as much as possible at the plant level.

Any connection supporting an OAuth style redirect (generally in multi-user mode) will need
to have these two functions defined:

 - getRedirectMarkup($data=false)
 - handleRedirectReturn($data=false)

The getRedirectMarkup will handle any logic needed to present the user with a redirect 
link, and the handleRedirectReturn will deal with the returned token and complete the 
connection process.

Any data like application-level keys that need to be stored to initiate OAuth requests 
can be stored in **/framework/settings/connections.json**. See template at 
/framework/settings/_connections.json for a quick example.
