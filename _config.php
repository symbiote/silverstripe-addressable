<?php
/**
 * @package silverstripe-addressable
 */

// To add simple address fields to an object, use
// Object::add_extension('class', 'Addressable');

// To add automatic geocoding to an object with the Addressable extension,
// use:
// Object::add_extension('class', 'Geocodable');

// To add shortcode handler support to display Google Maps use:
ShortcodeParser::get()->register('google_map', array('Addressable', 'render_address_map'));