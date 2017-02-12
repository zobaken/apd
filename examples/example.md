
Endpoint: `/api`

## Register and login section

This is section description text.
Second line of description.

### Register a new user
*PUT* `/api/register`

  This is api entry description

#### Request parameters
|Type|Name|Description|Required|
|---|---|---|---|
|_string_|__email__|User email|yes|
|_string_|__password__|User password|yes|
|_string_|__about__|About user||

#### Response fields
|Type|Name|Description|Required|
|---|---|---|---|
|_object_|__data__|User profile|yes|
|_int_|- __id__|User id|yes|
|_string_|- __email__|User email|yes|
|_string_|- __about__|About user||
