# Test project

Version: **1.0**

Project description.
## Data types
### Profile
#### User profile object

  Registered class description.

|Type|Name|Description|
|---|---|---|
|_int_|__id__|User id|
|_string_|__email__|User email|
|_string_|__about__|About user|
|_string_|__profile_image__|Profile image|
|_object_|__address__|User address|
|_string_|address/__city__|City|
|_string_|address/__street__|Street|
|_string_|address/__number__|House number|

## Register and login section

Section description.

### Register a new user
*PUT* `/profile/register`

  Call entry description

#### Request parameters
|Type|Name|Description|Required|Default value|
|---|---|---|---|---|
|_string_|__token__|Security token|_required_||
|_object_|__profile__|Profile fields|_required_||
|_string_|profile/__email__|User email|_required_||
|_string_|profile/__password__|User password|_required_||
|_string_|profile/__about__|About user|_optional_|To be filled|
|_string_|profile/__profile_image__|About user|_optional_|http://someserver/default.png|

#### Response fields
|Type|Name|Description|
|---|---|---|
|_Profile_|__data__|User profile|
