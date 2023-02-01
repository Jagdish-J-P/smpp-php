<?php

/**
 * This library makes use of APC cache to make hosts as down in a web
 * environment. If you are running from the CLI or on a system without APC
 * installed, then these null functions will step in and act like cache
 * misses.
 */
if (!function_exists('apc_fetch')) {
	function apc_fetch($key)
	{
		return FALSE;
	}
	function apc_store($key, $var, $ttl = 0)
	{
		return FALSE;
	}
}


if (!function_exists('printDebug')) {
	// Simple debug callback
	function printDebug($str)
	{
		echo date('Ymd H:i:s ') . $str . "\r\n";
	}
}
