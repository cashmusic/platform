# Request / response format
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

	<?php 
		$sample_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'getevent',
				'id' => 43
			)
		);

		$sample_request->resonse:
		{
			"status_code":404, // http-style status code
			"status_uid":"calendar_getevent_404", // uid of the response (request + status)
			"status_message":"Not Found", // http-style status message
			"contextual_message":"Event not found", // more specific error message
			"request_type":"calendar", // echo of the request type
			"action":"getevent", // echo of the request action
			"payload":false, // contents of the response, or false if error
			"api_version":2, // version number
			"timestamp":1335416260 // request timestamp
		}
	?>