# TaskTimeTerminate

> A Tool to record timings for tasks per category and remembering to terminate work sessions.

## About
See the projects Readme [here](https://github.com/KIMB-technologies/TaskTimeTerminate/).
This is the version and update API used by the clients.

## Version API

The main endpoint is `https://kimb-technologies.github.io/TaskTimeTerminate/version.json`.
Each TTT-Client will fetch this file regularly and check for new version. Always when a new version is found, the
client will show a message to the users and ask to update.

> The new update system will be implemented in #30!

### `version.json`
The file contains one JSON like object, besides general information the file contains an
array `versions`, containing an object for each version/ release. A client will check, if the file
has changed and then check what version it currently runs (using `tag` or `count`).
Afterwards it will sort all versions by `count` and determine newer version by a higher
value for `count`. Each version is tagged with `stable`, such that the client 
is able to determine, if a new version is a stable release.

The client now may show a message to the user and ask to update.
The client should show the `name` and `description` to the user.

####  Description format
A description is a single string, the characters `(`, `)` and `;` have a special meaning and 
must not be printed to the user.
Each aspect (e.g. one bullet point, new feature) may be described by text and this text has to be terminated with `;`.
So multiple aspects are separated by `;`, the last `;` at the end of the description may be omitted.
If aspect consists of subitems, the text of this aspect may contain some text wrapped by `()`. The text inside
of `()` should be parsed like a top-level description string.
Empty description strings are valid and have to be handled like non existent descriptions.

Examples:
```
"This is an example description; And it shows two aspects"
"This is an example description; And it shows two aspects;"

- This is an example description
- And it shows two aspects
```

```
"This is an example description"
"This is an example description;"

This is an example description
```

```
"This is an example description; And it shows two aspects (While the second; Also has; sub aspects)"
"This is an example description; And it shows two aspects (While the second; Also has; sub aspects;);"

- This is an example description
- And it shows two aspects
	- While the second
	- Also has
	- sub aspects
```

#### Updating
If the user or client starts an update the chosen release may be downloaded by the client 
using the link in `link`. The filetype and if the client always fetches the newest version or
incrementally updates version by version is given by the implementation details of each client.

#### General file format
```json 
{
	"name" : "string, The name of the project this file is for",
	"repository" : "string, The link to the github repo",	
	"author" : "string, The authors name",
	"last-updated" : "int, A unix timestamp when this file was changed last",
	"this-file" : "string, The link to this file (the link to use in clients)",
	"versions" : [
		{
			"tag" : "string, The versions git tag",
			"description" : "string, A description of this version (see `Description format`)",
			"link" : "string, The link to download the release (filetype depends on update mechanism)",
			"name" : "string, The name of the release",
			"count" : "int, Unique count for this release (higher count means more recent release)",
			"stable" : "bool, Is this a stable release?"
		}, 
		... 
	]
}
```
