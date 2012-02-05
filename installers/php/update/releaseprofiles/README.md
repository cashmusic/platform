# Release profiles

Every release pushed to latest_stable should increase the $version number in 
CASHRequest.php and get a profile in this directory. We're basically leaving a 
trail to follow in the update process so we'll know what kind of update it is. 

A small bugfix update should still increase the version number — think of it 
more as a build number than a true "version." 

Updates can be as simple as checking deltas in the file hashes and pulling down 
any new files, overwriting the old. The may also involve schema changes or 
other data migration that would require an actual update script. To accommodate 
the possibilities we're generating release profiles — JSON signatures for each 
version. 

## JSON structure

Each release profile is stored in a JSON file named release_n.json. Each of these 
files should contain: 

 - version (int)
 - releasedate (timestamp int)
 - schemachange (bool)
 - scriptneeded (bool)
 - blobs (array) 

Like so:

```json
{
	"version":1,
	"releasedate":1328310374,
	"schemachange":false,
	"scriptneeded":false,
	"blobs":{
		"framework/php/cashmusic.php":"f0a42ebcc73dd0b151df99589b466d1a1ad00e97",
		...
	}
}
````

The blobs array is taken from the github blobs array generated in their v2 api 
(ex: https://github.com/api/v2/json/blob/all/cashmusic/DIY/latest_stable) We're 
only storing the /interfaces and /framework blobs because they're all that's 
relevant to the install/update process. 