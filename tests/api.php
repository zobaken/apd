<?php

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
