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
	./rq [-h] [-H] [-n] [-x] [-l] [-a] [-p] <options file>
Parameters:
	-h
		Print this help
	-H
		Print response headers
	-n
		Script will not expect JSON response payload and will not attempt to
		format it on standard out
	-x
		Response will not be formatted. Only the actual response is printed.
		Useful for commands chaining
	-l
		Default behaviour is to follow any location redirects. Provide this flag
		in order for the client not to follow redirects
	-a
		User-agent header is being added by default. Provide this flag to omit it
	-p
		Print only response, skip request. Useful for file uploads, etc.
	<options-file>
		Provided location of options file. See ./post-request-example for clues
```
* all parameters can be combined
* long versions no longer supported
* request payload can be provided via stdin; data provided via stdin take precedence over `REQUEST_STRING` block from within the options file

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
* Additional HTTP methods
* HTTP Response codes differentiated by colours
* Optional payload trimming(JSON)
* Make possible to define hostname and partial URI independent - ie. to test the same feature at various hosts
* Ability to supply some headers from commandline (auth, user-agent, ...)
* Add new script that would offer chain calls to various endpoints
