
Endpoint: `/api`

## Register and login section

This is section description text.

### Register a new user
*PUT* `/api/register`

  This is api entry description

#### Request parameters
|Type|Name|Description|Required|Default value|
|---|---|---|---|---|
|_string_|__email__|User email|_required_||
|_string_|__password__|User password|_required_||
|_int_|__age__|User age|_optional_||
|_string_|__about__|About user|_optional_|To be filled|
|_string_|__profile_image__|About user|_optional_|http://someserver/default.png|

#### Response fields
|Type|Name|Description|
|---|---|---|
|_object_|__data__|User profile|
|_int_|data/__id__|User id|
|_string_|data/__email__|User email|
|_string_|data/__about__|About user|
