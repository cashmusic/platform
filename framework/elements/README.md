# CASH Music Elements
Elements bundle unique workflows in the CASH Music platform. Think of them as apps accessing
the core the same way apps access APIs on a phone. 

Each element has a PHP object that follows a set pattern, mustache templates for markup, an
image for thumbs, a LICENSE, and an accompanying JSON definition file. 


## JSON Metadata
The JSON definition file (app.json) sets high level metadata for the element and defines the
options for the element that can be set in the admin. The general metadata like update date, 
version, author name, URL, license type, and the details needed to describe the element.

## JSON Option Definitions
The options define what can be set in the element admin, what options will be present and 
expected, and provide default values, etc. 

The allowed types for options are:
  
  - select
    - values (required)
  - boolean
  - number
  - text
  - markup
  
And every option can also contain:

  - required
  - default
  - displaysize
  - helptext
  - placeholder


## JSON Example
Here's a faked example of a properly formatted app.json file with explanation in the object
properties themselves. Everything above is shown.

```
{
	"type":"element",
	"subtype":"uniquetypename",
	"lastupdated":"Jan 1, 2014",
	"version":1,
	"author":"CASH Music",
	"url":"http://cashmusic.org/",
	"license":"AGPL",
	"details":{
		"en":{
			"name":"Element Name",
			"description":"Short description of the element.",
			"longdescription":"Longer, more detailed description of the element.",
			"instructions":"Instructions that explain how to set up element.",
		}
	},
	"options":{
		"main":{
			"group_label":{
				"en":"Main settings"
			},
			"description":{
				"en":"Instructions or clarifications that should appear with this group."
			},
			"data":{
				"email_list_id":{
					"label":{
						"en":"A dropdown populated automatically by CASH platform core"
					},
					"helptext":{
						"en":"Helptext will be displayed during initial setup and always available on the edit page."
					},
					"type":"select",
					"values":"people_lists",
					"required":true,
					"displaysize":"small"
				},
				"text_dropdown":{
					"label":{
						"en":"A dropdown list of prepopulated choices"
					},
					"helptext":{
						"en":"Helptext will be displayed during initial setup and always available on the edit page."
					},
					"type":"select",
					"values":{
						"1":"Display text for first value",
						"two":"Display text for second value",
						"The third value":"The third value"
					},
					"required":true,
					"displaysize":"small"
				},
				"boolean":{
					"label":{
						"en":"A simple on/off checkbox"
					},
					"type":"boolean",
					"default":true,
					"displaysize":"small"
				},
				"number":{
					"label":{
						"en":"A valid numeric value"
					},
					"type":"number",
					"default":"3",
					"displaysize":"small"
				}
			}
		},
		"second group":{
			"group_label":{
				"en":"A second group of options"
			},
			"description":{
				"en":"Instructions or clarifications that should appear with this group."
			},
			"data":{
				"short_text":{
					"label":{
						"en":"A short bit of plain text"
					},
					"helptext":{
						"en":"Helptext will be displayed during initial setup and always available on the edit page."
					},
					"placeholder":{
						"en":"A placeholder for empty values."
					},
					"type":"text",
					"default":{
						"en":"This is a sentence or any other text."
					},
					"required":true,
					"displaysize":"medium"
				},
				"long_text":{
					"label":{
						"en":"Plain text, but intended for big stuff. Think textarea instead of text."
					},
					"type":"text",
					"default":{
						"en":"This could get big..."
					},
					"displaysize":"large"
				},
				"some_markup":{
					"label":{
						"en":"Some markup. This will be rendered instead of dumped, otherwise identical to text."
					},
					"type":"markup",
					"default":{
						"en":"Use all the HTML you want"
					},
					"displaysize":"medium"
				}
			}
		}
	}
}
```