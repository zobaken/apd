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
#### Project
~~~
@project <shortname> <version> <Title> 
~~~
#### Section
~~~
@section <Section-name> <Section title> 
~~~
#### Call entry
~~~
@call <Method> <Path> <Title> 
~~~
#### Request parameter
~~~
@request <Type> <Name> <Title> 
~~~
If parameter is optional then append `|null` to its type, like `string|null`. 

If type is `object` then a list of nested fields can be included:
~~~
@request object data Request object
int id User id
string email User email
~~~
#### Response field
~~~
@response <Type> <Name> <Title> 
~~~
#### Register object
You can define an object and use it later then. See example below.
## Example
This is a simple example.
~~~php
/**
 * @project test 1.0 Test project
 *
 * Project description.
 *
 * @section profile Register and login section
 *
 * Section description.
 *
 * @register Profile User profile object
 * int id User id
 * string email User email
 * string|null about About user
 * string|null profile_image Profile image
 * object|null address { User address
 *   string city City
 *   string street Street
 *   string number House number
 * }
 *
 * Registered class description.
 *
 */

/**
 * @call PUT /profile/register Register a new user
 *
 * Call entry description
 *
 * @request string token Security token
 * @request object profile Profile fields
 * string email User email
 * string password User password
 * string about="To be filled" About user
 * string profile_image=http://someserver/default.png About user

 * @response Profile data User profile
 */
~~~
* [JSON Output](examples/example.json)
* [Markdown Output](examples/example.md)