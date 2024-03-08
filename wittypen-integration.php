<?php
/*
 * Plugin Name:       Wittypen
 * Plugin URI:        https://wittypen.com/integrations/wordpress
 * Description:       1-click publishing from Wittypen to WordPress
 * Version:           1.0.0
 * Requires at least: 4.8
 * Requires PHP:      5.6
 * Author:            Wittypen Team
 * Author URI:        https://wittypen.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://wittypen.com/integrations/wordpress
 * Text Domain:       wittypen
 */

register_activation_hook(__FILE__, 'wittypen_generate_api_key');

function wittypen_generate_api_key()
{
    $api_key = wp_generate_password(24, false); // Generate a 24-character API key
    update_option('wittypen_api_key', $api_key); // Store the API key in the database
    update_option('wittypen_api_key_user_id', get_current_user_id()); // Store the current user ID in the database
}

add_action('rest_api_init', function () {
    register_rest_route('wittypen/v1', '/publish', array(
        'methods' => 'POST',
        'callback' => 'wittypen_publish_content',
        'permission_callback' => 'wittypen_authenticate_request'
    ));
});

add_action('admin_menu', 'wittypen_create_menu');

function wittypen_create_menu()
{
    add_menu_page(
        'Wittypen',
        'Wittypen',
        'manage_options',
        'wittypen',
        'wittypen_display_api_key',
        'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAwIiBoZWlnaHQ9IjUwOCIgdmlld0JveD0iMCAwIDUwMCA1MDgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik00NC44MTY1IDIzNC4zQzMxLjI1MjQgMjMwLjA5OSAzLjgwMTI1IDIzOS40NzEgMC4yNDg3NDcgMjEwLjA2M0MtMi4zMzQ4OSAxODkuNzAzIDE1Ljc1MDYgMTkwLjAyNyAyOS4zMTQ3IDE5MS42NDNDNDcuNzIzMSAxOTMuNTgyIDcyLjI2NzggMTg5LjA1NyA3Ny4xMTE5IDIxNC4yNjRDODAuNjY0NyAyMzEuNzE1IDY1LjQ4NTYgMjM2LjU2MiA0NC44MTY1IDIzNC4zWiIgZmlsbD0iYmxhY2siLz4KPHBhdGggZD0iTTI3My4xNDYgMzkuMTE2QzI3MC41NjIgNTQuOTUxIDI3NC4xMTUgNzUuOTU2NCAyNTAuNTM5IDc2LjYwMjdDMjIzLjQxMSA3Ny4yNDg5IDIyNy42MSA1NC42Mjc4IDIyNy42MSAzNy41MDAyQzIyNy45MzIgMjEuNjY1MiAyMjQuNzAzIDAuMzM2NTIgMjQ4LjYwMSAwLjAxMzM1ODFDMjc1LjQwNyAtMC42MzI5NjcgMjcwLjg4NSAyMi4zMTE2IDI3My4xNDYgMzkuMTE2WiIgZmlsbD0iYmxhY2siLz4KPHBhdGggZD0iTTE0NC45MyA5OC4yNDU3QzE0NC42MDcgMTEyLjc4OCAxMzcuODI1IDEyNC40MjIgMTI2LjE5OSAxMjIuNDgzQzEwMS4wMDggMTE3Ljk1OCA4Ny43NjczIDk3LjI3NiA4MC4wMTY2IDc0Ljk3NzdDNzUuODE4MiA2My4wMjA5IDg2LjE1MjYgNTAuNzQwOCA5Ny43Nzg5IDUyLjY3OTdDMTIyLjk3IDU3LjIwNCAxMzMuNjI3IDc5LjUwMjMgMTQ0LjkzIDk4LjI0NTdaIiBmaWxsPSJibGFjayIvPgo8cGF0aCBkPSJNNDU0LjY0MSAyMzQuOTVDNDM0LjI5NCAyMzYuODg5IDQxOC4xNDcgMjMzLjAxMSA0MjEuNyAyMTQuOTE0QzQyNi4yMjEgMTkwLjAzIDQ1MC43NjUgMTkzLjU4NSA0NjkuNDk3IDE5MS4zMjNDNDg0LjAyOSAxODkuNzA3IDUwMS43OTIgMTkwLjk5OSA0OTkuODU0IDIwOS43NDNDNDk2LjMwMiAyMzkuNDc0IDQ2OC4yMDUgMjI5Ljc3OSA0NTQuNjQxIDIzNC45NVoiIGZpbGw9ImJsYWNrIi8+CjxwYXRoIGQ9Ik00MTkuNzYyIDc2LjkyNUM0MTAuMDczIDk1LjY2ODQgMzk5LjQxNiAxMTcuOTY3IDM3My45MDMgMTIyLjQ5MUMzNjIuMjc2IDEyNC40MyAzNTIuMjY1IDExMi40NzMgMzU1LjgxNyAxMDAuMTkyQzM2Mi41OTkgNzguNTQwNCAzNzYuNDg2IDU5Ljc5NzIgMzk5LjA5MyA1My4zMzRDNDExLjA0MyA0OS43NzkyIDQxOC43OTMgNjAuNzY2NyA0MTkuNzYyIDc2LjkyNVoiIGZpbGw9ImJsYWNrIi8+CjxwYXRoIGQ9Ik0zMzkuNjc2IDE0OS4zMjFDMzM2LjQ0NyAxNDYuNDEzIDMzNS44MDEgMTQ1Ljc2NiAzMzkuNjc2IDE0OS4zMjFDMjkzLjQ5NCAxMDguMjggMjIyLjEyMSAxMDUuMDQ3IDE3MS40MTcgMTM4Ljk3OUMxMTguMTI5IDE3NC41MjggOTkuMzk4MSAyNDEuNzQ1IDEyMi42NTEgMzAwLjU2MUMxMzAuNzI1IDMyMC41OTcgMTQyLjk5NyAzMzkuMDE3IDE1My45NzcgMzU3LjQzN0MxNjMuMDIgMzcyLjk0OSAxNzIuMzg2IDM4OC43ODQgMTgxLjQyOCA0MDQuMjk2QzE5NC45OTMgNDI3LjU2NCAyMDguNTU3IDQ1MC44MzEgMjIyLjEyMSA0NzMuNzc2QzIyOC41OCA0ODUuMDg3IDIzNS4zNjIgNDk2LjM5OCAyNDEuODIxIDUwNy4zODVDMjQxLjgyMSA0NTEuMTU1IDI0MS44MjEgMzk0LjYwMSAyNDEuODIxIDMzOC4zNzFDMjQxLjgyMSAzMjQuNzk4IDI0MS44MjEgMzExLjIyNSAyNDEuODIxIDI5Ny4zMjlDMjI5LjU0OSAyOTQuMDk3IDIyMC44MjkgMjgzLjExIDIyMC44MjkgMjY5Ljg2QzIyMC44MjkgMjU0LjAyNSAyMzMuNDI0IDI0MS40MjIgMjQ5LjI0OSAyNDEuNDIyQzI2NS4wNzQgMjQxLjQyMiAyNzcuNjY5IDI1NC4wMjUgMjc3LjY2OSAyNjkuODZDMjc3LjY2OSAyODMuMTEgMjY4LjYyNiAyOTQuMDk3IDI1Ni4zNTQgMjk3LjMyOUMyNTYuMzU0IDMxMC45MDIgMjU2LjM1NCAzMjQuNDc1IDI1Ni4zNTQgMzM4LjM3MUMyNTYuMzU0IDM5NC42MDEgMjU2LjM1NCA0NTEuMTU1IDI1Ni4zNTQgNTA3LjM4NUMyODAuODk5IDQ2NS42OTcgMzA1LjEyIDQyNC4wMDkgMzI5LjY2NSAzODIuNjQ0QzM0Ny4xMDQgMzUyLjkxMyAzNjkuMDY1IDMyMi44NTkgMzc5LjcyMyAyOTAuMjJDMzk2LjUxNiAyMzguODM3IDM3OS43MjMgMTg0Ljg2OSAzMzkuNjc2IDE0OS4zMjFaTTE1Ni4yMzggMjcwLjUwNkMxNDcuNTE5IDIyNC4yOTQgMTU5Ljc5IDE5Mi45NDggMTkzLjM3OCAxNzAuMDAzQzIyNC43MDUgMTQ4LjY3NCAyNTcuNjQ2IDE0OC4wMjggMzAwLjkyMiAxNzMuMjM1QzIyMS40NzUgMTY4LjcxIDE4MS43NTIgMjA4LjEzNiAxNTYuMjM4IDI3MC41MDZaIiBmaWxsPSJibGFjayIvPgo8L3N2Zz4K'
    );
}

function wittypen_script()
{
    echo <<<EOT
    <script>
        function copyApiKey() {
            var apiKey = document.getElementById('wittypen-api-key').innerHTML;
            navigator.clipboard.writeText(apiKey);

            document.querySelector('#wittypen-api-key-copy-success-msg').classList.remove('hidden');

            setTimeout(() => {
                document.querySelector('#wittypen-api-key-copy-success-msg').classList.add('hidden');
            }, 3000);
        }
    </script>
    EOT;
}

function wittypen_display_api_key()
{
    echo '<script src="https://cdn.tailwindcss.com"></script>';

    wittypen_script();

    $api_key = get_option('wittypen_api_key');
    $logo_url = plugins_url('assets/wittypen-logo.svg', __FILE__);

    echo <<<EOT
    <div class="my-8 w-full space-y-8 lg:mx-12 lg:w-1/3 pr-[10px]">
    <img src="$logo_url" alt="wittypen-logo" class="mx-auto w-1/3" />
    <div class="space-y-2 rounded-lg border bg-white p-4 shadow-lg">
        <p class="inline-block font-bold">API Key</p>
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4">
        <p id="wittypen-api-key">$api_key</p>
        <span class="cursor-pointer hover:opacity-50" onclick="copyApiKey()">
            <svg class="w-4 fill-gray-500" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 115.77 122.88"><path d="M89.62 13.96v7.73h12.2v.02c3.85.01 7.34 1.57 9.86 4.1 2.5 2.51 4.06 5.98 4.07 9.82h.02v73.3h-.02c-.01 3.84-1.57 7.33-4.1 9.86-2.51 2.5-5.98 4.06-9.82 4.07v.02H40.1v-.02c-3.84-.01-7.34-1.57-9.86-4.1-2.5-2.51-4.06-5.98-4.07-9.82h-.02V92.51h-12.2v-.02c-3.84-.01-7.34-1.57-9.86-4.1-2.5-2.51-4.06-5.98-4.07-9.82H0V13.95h.02c.01-3.85 1.58-7.34 4.1-9.86C6.63 1.59 10.1.03 13.94.02V0H75.67v.02c3.85.01 7.34 1.57 9.86 4.1 2.5 2.51 4.06 5.98 4.07 9.82h.02v.02zm-10.58 7.73v-7.75h.02c0-.91-.39-1.75-1.01-2.37-.61-.61-1.46-1-2.37-1v.02H13.95v-.02c-.91 0-1.75.39-2.37 1.01-.61.61-1 1.46-1 2.37h.02v64.62h-.02c0 .91.39 1.75 1.01 2.37.61.61 1.46 1 2.37 1v-.02h12.2V35.64h.02c.01-3.85 1.58-7.34 4.1-9.86 2.51-2.5 5.98-4.06 9.82-4.07v-.02H79.04zm26.14 87.23V35.63h.02c0-.91-.39-1.75-1.01-2.37-.61-.61-1.46-1-2.37-1v.02H40.09v-.02c-.91 0-1.75.39-2.37 1.01-.61.61-1 1.46-1 2.37h.02v73.3h-.02c0 .91.39 1.75 1.01 2.37.61.61 1.46 1 2.37 1v-.02H101.83v.02c.91 0 1.75-.39 2.37-1.01.61-.61 1-1.46 1-2.37h-.02v-.01z" style="fill-rule:evenodd;clip-rule:evenodd" /></svg
        ></span>
        </div>
        <p id="wittypen-api-key-copy-success-msg" class="hidden text-green-600">API key copied</p>
    </div>
    <div class="space-y-1 rounded-lg border border-yellow-400 bg-yellow-50 p-4 text-yellow-800">
        <p class="font-bold">Important: Keep this plugin installed and activated</p>
        <p>Do not deactivate or delete this plugin, otherwise the publishing feature won't work.</p>
    </div>
    <a class="inline-block text-gray-500 underline hover:opacity-70" href="https://wittypen.com/contact-us" target="_blank">Contact us for any queries</a>
    </div>
    EOT;
}

function wittypen_publish_content(WP_REST_Request $request) {
    $content = $request->get_param('content');
    $title = $request->get_param('title');
    $status = $request->get_param('status');

    $post_id = wp_insert_post(array(
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => $status,
    ));

    if ($post_id) {
        return new WP_REST_Response(array('success' => true, 'post_id' => $post_id), 200);
    }

    return new WP_Error('publish_error', 'Failed to publish content', array('status' => 500));
}

function get_authorization_header() {
    $headers = null;
	
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();

        $requestHeaders = array_combine(
            array_map('ucwords', array_keys($requestHeaders)),
            array_values($requestHeaders)
        );

        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
	
    return $headers;
}

function get_bearer_token($headers) {
    if (! empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
	
    return null;
}

function wittypen_authenticate_request() {
	$authorization_header = get_authorization_header();

    if (empty($authorization_header)) {
        // No Authorization header was provided, return an error
        return new WP_Error('rest_forbidden', 'You did not provide an API key.', array('status' => 403));
    }

    // Get the API key from the Authorization header
    $api_key = get_bearer_token($authorization_header);

    if ($api_key !== get_option('wittypen_api_key')) {
        // The provided API key does not match the stored API key, return an error
        return new WP_Error('rest_forbidden', 'Invalid API key.', array('status' => 403));
    }

    // The API key is valid, set the current user to the user associated with the API key
    wp_set_current_user(get_option('wittypen_api_key_user_id'));

    return true;
}
