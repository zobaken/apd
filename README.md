# Simple PHP API documentation generator
## What is this?
This is a simple library that helps to create api documentation directly
 in PHP code comment blocks.
## Installation
You are required to use composer:
~~~
composer require zobaken/apd ~0.1
~~~
## Usage
To get parsed structure:
~~~php
use Apd\Parser;
...
$parser = new Parser();
$parser->path('path to your php files');
$endpoints = $parser->parse();
~~~
In result `$endpoints` will contain an array of `\Apd\Structure\Endpoint`.
## Command line
This parser can be used generate simple Markdown or HTML output:
~~~
php vendor/bin/apd-markdown.php path-to-files > output.md
php vendor/bin/apd-html.php path-to-files > output.html
~~~
## Syntax
Optional description can be placed after any tag.
#### Endpoint
~~~
@endpoint <Enpoint path> <Endpoint title> 
~~~
Endpoint path defaults to `api`.
#### Section
~~~
@endpoint <Section-name> <Section title> 
~~~
#### Api entry
~~~
@api <Method> <Path> <Title> 
~~~
#### Request parameter
~~~
@request <Type> <Name> <Title> 
~~~
If parameter is optional then append `|null` to its type, like `string|null`. 
#### Response field
~~~
@response <Type> <Name> <Title> 
~~~
If type is `object` or `array` then a list of nested fields can be included:
~~~
@response <Type> <Name> { <Title>
    @response <Type> <Name> <Title>
    ...
@response <Type> <Name> } 
~~~
## Example
This is a simple example.
~~~php
/**
 * @section profile Register and login section
 *
 * This is section description text.
 */

/**
 * @api PUT /register Register a new user
 *
 * This is api entry description
 *
 * @request string email User email
 * @request string password User password
 * @request string|null about About user
 * @response object data { User profile
 * @response   int id User id
 * @response   string email User email
 * @response   string|null about About user
 * @response object data }
 */
~~~
* [JSON Output](examples/example.json)
* [Markdown Output](examples/example.md)