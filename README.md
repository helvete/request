# request

## Info ##
request is a simple PHP library/script which allows requesting remote APIs.

Its key feature is, that connection settings lie in an easily editable file, that is supplied as the only execution parameter. It's been done this way to allow easy API scope changes while preserving previously stored data.

Initially prepared for JSON string interchange, but adapted to be able to process non-json strings, just supply --non-json runtime parameter. --non-json and --exact-return parameters can be combined.

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
**METHOD** and **URL** accept only the first non-comment entry, while
**HEADERS** and **REQUEST_STRING** accept unlimited count of lines

## Usage ##

Just run
```
./src/api_request /home/user/path/to/options/file
```
from within the repository directory

## Data visualisation ##

Example request with its response will look similarly:

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
Bye!
```

Note: There is also a possibility for the script to run in less verbose mode returning exactly only the response string to be able to redirect the output. To do so, just add --exact-return parameter.

```
./src/api_request /home/user/path/to/options/file --exact-return --non-json
```

## Possible improvements ##

* Separating the library and the script parts
* Learn it to handle more HTTP methods than GET and POST
* Make possible to auto-calculate request string size and include Content-length header
