# request

## Info ##
request is a simple PHP library with script that allow requesting remote APIs.

Its key feature is, that connection settings lie in an easily editable file, that is supplied as the first execution parameter. It's been done this way to allow easy API scope changes while preserving previously stored data.

Initially prepared for JSON string interchange, but adapted to be able to process non-json strings.

## Options file structure ##
Use an example options file as a sample.

```
METHOD
	POST
HEADERS
	Accept-Charset: utf8
	Content-Type: application/json
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
**METHOD** and **URL** accept only the last non-comment entry, while

**HEADERS** and **REQUEST_STRING** accept unlimited count of lines

## Usage ##

```
Usage:
	./rq [-h|--help] [-n|--non-json] [-x|--exact-response]
		[-l|--no-follow-redirect] <options file>
Parameters:
	-h|--help
		Print this help
	-n|--non-json
		Script will not expect JSON response payload and will not attempt to
		format it on standard out
	-x|--exact-response
		Response will not be formatted. Only the actual response is printed.
		Useful for commands chaining
	-l|--no-follow-redirects
		Default behaviour is to follow any location redirects. Provide this flag
		in order for the client not to follow redirects
	<options-file>
		Provided location of options file. See ./post-request-example for clues
Note:
	Short versions of -x and -n parameters can be combined like -xn or -nx.
```

## Data visualisation ##

Example request with its response will look similarly (without -x and -n params):

```
REQUEST:
{
	"jsonrpc": "2.0",
	"id": "666",
	"method": "user.login",
	"params": {
		"user": "user@server.com",
		"password": "666666"
	}
}
RESPONSE [401]:
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

## Possible improvements ##

* Make possible to auto-calculate request string size and include Content-length header
* Better getopt handling
