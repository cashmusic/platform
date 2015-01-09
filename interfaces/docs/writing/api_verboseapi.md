The verbose API is a direct wrapper for all requests that allow the api_public or api_key 
access methods. At the moment those are mostly limited to list signups and initiating new
transactions, but we're working on a full authorization scheme to expand scope. 

The response object and payload are nearly identical to the return from the PHP core, 
except returned as JSON. Making a request that doesn't allow API access will give you a
forbidden status, but here's an example endpoint:

	/api/verbose/asset/getasset/id/2

The format is simple: /verbose/**plant**/**request**/**{parameter name}**/**{parameter value}**
 — it'll parse as many parameters as you throw at it and respond:

<script src="https://gist.github.com/jessevondoom/a3d384453bf053a2ca8e.js"></script>

More about authorization methods coming soon.