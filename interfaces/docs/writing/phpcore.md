All functionality of the core platform is accessed through a single and consistent PHP-based API. 
No direct function calls should be made — instead data should be accessed and set through a secure 
and standard request / response model consistent with any HTTP GET/POST requests. 

For developers looking to build custom functionality we've built a request/response 
process as the primary interface to the PHP framework. It mimics a REST-style API 
and standardizes calls and responses across the methods...so the PHP interaction 
will be nearly identical to future REST or verbose interactions. 

Every request is made with a specific type and action, plus any required or optional 
parameters. It's response will contain an http-esque **status code**, a **uid** containing 
type/action/code, a human-readable **status message**, a more detailed **contextual message**, 
an echo of the **request type** and **action**, a **payload** with the full response data 
or false if the request failed, the **api version**, and a **timestamp**.

The PHP Request/Response looks like this: 

<script src="https://gist.github.com/jessevondoom/1b8cb605f999bd8ecadd.js"></script>