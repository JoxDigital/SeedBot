<?php
/*
 * Plugin Name: SeedBot
 * Plugin URI:  https://github.com/qev254/SeedBot
 * Description: A simple plugin to add a SeedBot to our Wordpress Website.
 * Version:     1.0.0
 * Author:      joxdigital.com, Joshua O, Klevin K
 * Author URI:  https://www.joxdigital.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *  
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * You should have received a copy of the GNU General Public License
 * along with SeedBot. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 * 
*/

// Die if file is called directly
defined( 'WPINC' ) || die;

// Die if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Include the settings and shortcode files
require_once plugin_dir_path(__FILE__) . 'includes/seedbot-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/seedbot-shortcode.php';

// Set diagnostics On or Off 
update_option('seedbot_diagnostics', 'Off');

// Enqueue styles and scripts
function seedbot_enqueue_scripts() {
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style('seedbot-css', plugins_url('assets/css/seedbot.css', __FILE__));
    wp_enqueue_script('seedbot-js', plugins_url('assets/js/seedbot.js', __FILE__), array('jquery'), '1.0', true);

    // The seedbotlocal.js file has the local settings
    wp_enqueue_script('seedbot-local', plugins_url('assets/js/seedbot-local.js', __FILE__), array('jquery'), '1.0', true);
    // Enqueue this file and set options in an array variable
    $seedbot_settings = array(
        'seedbot_bot_name' => esc_attr(get_option('seedbot_bot_name')),
        'seedbot_initial_greeting' => esc_attr(get_option('seedbot_initial_greeting')),
        'seedbot_subsequent_greeting' => esc_attr(get_option('seedbot_subsequent_greeting')),
        'seedbotStatus' => esc_attr(get_option('seedBotStatus')),
        'seedbot_disclaimer_setting' => esc_attr(get_option('seedbot_disclaimer_setting')),
        'seedbot_max_tokens_setting' => esc_attr(get_option('seedbot_max_tokens_setting')),
        'seedbot_width_setting' => esc_attr(get_option('seedbot_width_setting')),
    );
    wp_localize_script('seedbot-local', 'seedbotSettings', $seedbot_settings);

    // Remove this line to avoid duplicate localization
    // wp_localize_script('seedbot-js', 'seedbot_params', array(
    //     'ajax_url' => admin_url('admin-ajax.php'),
    //     'api_key' => esc_attr(get_option('seedbot_api_key')),
    // ));
}
add_action('wp_enqueue_scripts', 'seedbot_enqueue_scripts');


// Handle Ajax requests
function seedbot_send_message() {
    // Get the save API key
    $api_key = esc_attr(get_option('seedbot_api_key'));
    // Get the saved model from the settings or default to gpt-3.5-turbo
    $model = esc_attr(get_option('gpt_model_choice', 'gpt-4'));
    // Max tokens - Ver 1.4.2
    $max_tokens = esc_attr(get_option('seedbot_max_tokens_setting', 150));
    // Send only clean text via the API
    $message = sanitize_text_field($_POST['message']);

    // Check API key and message
    if (!$api_key || !$message) {
        wp_send_json_error('Invalid API key or message');
    }

    // Send message to C- Ver 1.4.2hatGPT API
    $response = seedbot_call_api($api_key, $message);

    // Return response
    wp_send_json_success($response);
}

// Add link to seedbot options - setting page
function seedbot_plugin_action_links($links) {
    $settings_link = '<a href="../wp-admin/options-general.php?page=seedbot">' . __('Settings', 'seedbot') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_action('wp_ajax_seedbot_send_message', 'seedbot_send_message');
add_action('wp_ajax_nopriv_seedbot_send_message', 'seedbot_send_message');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'seedbot_plugin_action_links');

// Call the ChatGPT API

function seedbot_call_api($api_key, $message) {
    // Diagnostics = Ver 1.4.2
    $seedbot_diagnostics = esc_attr(get_option('seedbot_diagnostics', 'Off'));

    // The current ChatGPT API URL endpoint for gpt-3.5-turbo and gpt-4
    $api_url = 'https://api.openai.com/v1/chat/completions';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
    );

    // Select the OpenAI Model
    // Get the saved model from the settings or default to "gpt-4"
    $model = esc_attr(get_option('seedbot_model_choice', 'gpt-4'));
    // Max tokens - Ver 1.4.2
    $max_tokens = intval(esc_attr(get_option('seedbot_max_tokens_setting', '150')));

    $body = array(
        'model' => $model,
        'max_tokens' => $max_tokens,
        'temperature' => 0.5,

        'messages' => array(array('role' => 'user', 'content' => $message)),
    );

    $args = array(
        'headers' => $headers,
        'body' => json_encode($body),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 50, // Increase the timeout values to 15 seconds to wait just a bit longer for a response from the engine
    );

    $response = wp_remote_post($api_url, $args);

    // Handle any errors that are returned from the chat engine
    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message() . ' Please check Settings for a valid API key or your OpenAI account for additional information.';
    }

    // Return json_decode(wp_remote_retrieve_body($response), true);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['choices']) && !empty($response_body['choices'])) {
        // Handle the response from the chat engine
        return $response_body['choices'][0]['message']['content'];
    } else {
        // Handle any errors that are returned from the chat engine
        return 'Error: Unable to fetch response from ChatGPT API. Please check Settings for a valid API key or your OpenAI account for additional information.';
    }
}



function enqueue_greetings_script() {
    wp_enqueue_script('greetings', plugin_dir_url(__FILE__) . 'assets/js/greetings.js', array('jquery'), null, true);

    $greetings = array(
        'initial_greeting' => esc_attr(get_option('seedbot_initial_greeting', 'Hello! How can I help you today?')),
        'subsequent_greeting' => esc_attr(get_option('seedbot_subsequent_greeting', 'Hello again! How can I help you?')),
    );

    wp_localize_script('greetings', 'greetings_data', $greetings);
}
add_action('wp_enqueue_scripts', 'enqueue_greetings_script');