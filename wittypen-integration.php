<?php
/*
Plugin Name: Wittypen Integration
Description: Generates an API key for connecting your WordPress website with Wittypen.
Version: 1.0
Author: Wittypen
*/

register_activation_hook(__FILE__, 'wittypen_generate_api_key');

function wittypen_generate_api_key()
{
    $api_key = wp_generate_password(24, false); // Generate a 24-character API key
    update_option('wittypen_api_key', $api_key); // Store the API key in the database
    update_option('wittypen_api_key_user_id', get_current_user_id()); // Store the current user ID in the database
}

add_action('admin_menu', 'wittypen_create_menu');

function wittypen_create_menu()
{
    add_menu_page('Wittypen API Key', 'Wittypen API Key', 'manage_options', 'wittypen-api-key', 'wittypen_display_api_key');
}

function wittypen_display_api_key()
{
    $api_key = get_option('wittypen_api_key');
    echo '<h1>Your Wittypen API Key</h1>';
    echo '<p>' . $api_key . '</p>';
}

function wittypen_authenticate_request($result)
{
    if (!empty($result)) {
        // Another authentication method is being used, return the result
        return $result;
    }

    // Get the Authorization header
    $authorization_header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

    if (empty($authorization_header)) {
        // No Authorization header was provided, return an error
        return new WP_Error('rest_forbidden', 'You did not provide an API key.', array('status' => 403));
    }

    // Remove "Bearer " from the start of the header
    $api_key = substr($authorization_header, 7);

    if ($api_key !== get_option('wittypen_api_key')) {
        // The provided API key does not match the stored API key, return an error
        return new WP_Error('rest_forbidden', 'Invalid API key.', array('status' => 403));
    }

    // The API key is valid, set the current user to the user associated with the API key
    wp_set_current_user(get_option('wittypen_api_key_user_id'));

    // Return null to indicate that authentication was successful
    return null;
}

add_filter('rest_authentication_errors', 'wittypen_authenticate_request');
