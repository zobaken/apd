<?php

/**
 * @section profile Register and login section
 *
 * This is section description text.
 *
 * @register object profile {
 * @register int id User id
 * @register string email User email
 * @register }
 */

/**
 * @api PUT /register Register a new user
 *
 * This is api entry description
 *
 * @request string email User email
 * @request string password User password
 * @request int|null age User age
 * @request string about="To be filled" About user
 * @request string profile_image=http://someserver/default.png About user
 * @response object data { User profile
 * @response   int id User id
 * @response   string email User email
 * @response   string|null about About user
 * @response object data }
 */
