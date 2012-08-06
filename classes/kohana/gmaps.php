<?php

Class Kohana_Gmaps {

	/**
	 * The URL to the Google Maps RESTfull API
	 */
	const URL = 'http://maps.googleapis.com/maps/api/';

	/**
	 * A list of supported Google Maps web services
	 */
	const APIS = array('geocode', 'distancematrix');

	/**
	 * Use Geocoding to determine the well formatted, fully qualified address of
	 * the passed location string.
	 *
	 * @link https://developers.google.com/maps/documentation/geocoding/
	 *
	 * @param  string $location The location to geocode
	 * @return string
	 */
	public static function geocode($location)
	{

	}

	/**
	 * Determine the distance and travel time between two locations.
	 *
	 * @param  string $origin      The start point
	 * @param  string $destination The end point
	 * @return array
	 */
	public static function distance($origin, $destination)
	{

	}

	/**
	 * Makes a HTTP request to the API specified
	 */
	protected static function make_request($API, $parameters)
	{

	}

}
