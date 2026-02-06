<?php

/**
 * Version Helper
 * 
 * Provides functions to access application version information
 */

if (!function_exists('getAppVersion')) {
    /**
     * Get the application version
     * 
     * @return string
     */
    function getAppVersion()
    {
        return env('app.version', '1.0.0');
    }
}

if (!function_exists('getAppName')) {
    /**
     * Get the application name
     * 
     * @return string
     */
    function getAppName()
    {
        return env('app.name', 'CertMaster');
    }
}

if (!function_exists('getAppDescription')) {
    /**
     * Get the application description
     * 
     * @return string
     */
    function getAppDescription()
    {
        return env('app.description', 'SSL Certificate Management System');
    }
}

if (!function_exists('getAppInfo')) {
    /**
     * Get full application information
     * 
     * @return array
     */
    function getAppInfo()
    {
        return [
            'name' => getAppName(),
            'version' => getAppVersion(),
            'description' => getAppDescription(),
        ];
    }
}
