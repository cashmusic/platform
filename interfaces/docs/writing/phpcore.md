All functionality of the platform is accessed through a consistent request/response model at the
heart of the PHP Core. No direct function calls should be made — instead data should be accessed 
and set through a secure and standard request/response model.

The request/response model lets us build consistency from PHP to API and into elements and 
connections. It mimics a REST-style API and standardizes calls and responses across the methods.

Every request is made with a specific type and action, plus any required or optional 
parameters. It's response will contain an http-esque **status code**, a **uid** containing 
type/action/code, a human-readable **status message**, a more detailed **contextual message**, 
an echo of the **request type** and **action**, a **payload** with the full response data 
or false if the request failed, the **api version**, and a **timestamp**.

Initiating a PHP Request looks like this: 

<script src="https://gist.github.com/jessevondoom/1b8cb605f999bd8ecadd.js"></script>

An example of a failed response object:

<script src="https://gist.github.com/jessevondoom/b8f3c7ba595c7ff3f861.js"></script>

Or on success:

<script src="https://gist.github.com/jessevondoom/280ded1684f165e94c85.js"></script>

The payload is returned as an associative array. Most basic data requests will include 
creation and modification dates which are standard and automated in the system. Requests to
create new resources will return an id number on success. 

All core files are located in the repo at **/framework/classes/core** with requests divided
by type and organized into individual plant classes at **/framework/classes/plants**. Most 
new functionality is defined at the plant level, with the core classes used to route requests, 
to plants, abstract database connections, etc.

Each plant includes a routing table for requests that points to internal functions and defines
the authentication context under which they're allowed. See below for a complete list of 
requests exposed by the core.