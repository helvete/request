# request

## Info ##
request is a PHP library and a simple script that allow requesting remote APIs.

Its key feature is, that connection settings lie in an easily editable file, that is provided as the last execution parameter. It's been done this way to allow easy API scope changes while preserving previously stored data (headers, URLs and payloads).

JSON-centric by design, able to work w/ anything though

auto-base64 encode `Authorization: Basic user:password` headers

## Options file structure ##
Use an example options file as a sample.

```
METHOD
	post
HEADERS
	Accept-Charset: utf8
	Content-Type: application/json
	Authorization: Basic user:password
URL
	#http://example.com/?param1=A&param2=B

	https://example.com/api
REQUEST_STRING
{
	"jsonrpc": "2.0",
	"id": "666",
	"method": "user.login",
	"params": {
		"user": "user@server.com",
		"password": "666666"
	}
}
```
**METHOD** and **URL** accept only the last non-comment (non-empty) entry, while

**HEADERS** and **REQUEST_STRING** accept unlimited count of lines

## Usage ##

```
Usage:
	./rq [-h|--help] [-H|--headers] [-n|--non-json] [-x|--exact-response]
		[-l|--no-follow-redirect] [-a|--no-user-agent] <options file>
Parameters:
	-h|--help
		Print this help
	-H|--headers
		Print response headers
	-n|--non-json
		Script will not expect JSON response payload and will not attempt to
		format it on standard out
	-x|--exact-response
		Response will not be formatted. Only the actual response is printed.
		Useful for commands chaining
	-l|--no-follow-redirects
		Default behaviour is to follow any location redirects. Provide this flag
		in order for the client not to follow redirects
	-a|--no-user-agent
		User-agent header is being added by default. Provide this flag to omit it
	-p|--response-only
		Print only response, skip request. Useful for file uploads, etc.
	<options-file>
		Provided location of options file. See ./post-request-example for clues
Note:
	Short versions of -x and -n parameters can be combined like -xn or -nx.
```

## Data visualisation ##

Example request with its response will look similarly (without -x and -n params):

```
--> POST [https://example.com/api]:
{
	"jsonrpc": "2.0",
	"id": "666",
	"method": "user.login",
	"params": {
		"user": "user@server.com",
		"password": "666666"
	}
}
<-- [401]:
{
	"jsonrpc": "2.0",
	"error": {
		"code": -32011,
		"message": "User account validity expired",
		"data": {
			"email": "user@server.com"
		}
	},
	"id": "666"
}
Bye! (Request duration: 0.26s)
```

## Possible improvements aka TODOs ##

* Make possible to auto-calculate request string size and include Content-length header
* Better getopt handling!!
* Additional HTTP methods
* HTTP Response codes differentiated by colours
* Optional payload trimming(JSON)
* Allow request string to be provided via STDIN
* Make possible to define hostname and partial URI independent - ie. to test the same feature at various hosts
* Headers-only and headers-anong-body modes (Useful for OPTIONS, debugging, ...)
* Ability to supply some headers from commandline (auth, user-agent, ...)
* Add new script that would offer chain calls to various endpoints
