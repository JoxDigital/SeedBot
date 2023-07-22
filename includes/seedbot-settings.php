<?php
/**
 * SeedBot GPT-4 for WordPress - Settings Page
 * 
 * This is the /includes/seedbot-settings.php file
 *
 * This file contains the code for the SeedBot GPT-4 settings page.
 * It allows users to configure the API key and other parameters
 * required to access the ChatGPT API from their own account.
 *
 * @package seedbot
 */

function seedbotbot_settings_page() {
    // Add a submenu page to the Settings main menu
    add_options_page('SeedBot GPT-4 Settings', 'SeedBot GPT-4', 'manage_options', 'seedbot', 'seedbot__settings_page_html');
}
// Admin menu hook
add_action('admin_menu', 'seedbot_settings_page');

// Seedbot Settings page HTML - Ver 1.0.0
function seedbot_settings_page_html() {
    // Check if current user can manage options
   if (!current_user_can('manage_options')) {
        return;
    }
    
    // Create the API Model Tab in settings page
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'api_model';

    if (isset($_GET['settings-updated'])) {
        // add_settings_error( string $setting, string $code, string $message, string $type = 'error' )
        add_settings_error('seedbot_messages', 'seedbot_message', 'Settings Saved', 'updated');
    }
   
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-format-chat"></span> <?php echo esc_html(get_admin_page_title()); ?></h1>

        <!-- Create Message Box -->
        <div id="message-box-container"></div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const seedbotSettingsForm = document.getElementById('seedbot-settings-form');
                const seedbotStartStatusInput = document.getElementById('seedBotStatus');
                const reminderCount = localStorage.getItem('reminderCount') || 0;

                if (reminderCount < 5) {
                    const messageBox = document.createElement('div');
                    messageBox.id = 'rateReviewMessageBox';
                    messageBox.innerHTML = `
                    <div id="rateReviewMessageBox" style="background-color: white; border: 1px solid black; padding: 10px; position: relative;">
                        <div class="message-content" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>If you and your visitors are enjoying having this chatbot on your site, please consider rating and reviewing this plugin. Thank you!</span>
                            <button id="closeMessageBox" class="dashicons dashicons-dismiss" style="background: none; border: none; cursor: pointer; outline: none; padding: 0; margin-left: 10px;"></button>
                            
                        </div>
                    </div>
                    `;

                    document.querySelector('#message-box-container').insertAdjacentElement('beforeend', messageBox);

                    document.getElementById('closeMessageBox').addEventListener('click', function() {
                        messageBox.style.display = 'none';
                        localStorage.setItem('reminderCount', parseInt(reminderCount, 10) + 1);
                    });
                }
            });
        </script>
    
    <script>
    jQuery(document).ready(function($) {
        var seedbotSettingsForm = document.getElementById('seedbot-settings-form');

        if (seedbotSettingsForm) {

            seedbotSettingsForm.addEventListener('submit', function() {

                // THIS IS WHERE NIMEFIKA
                const seedbotNameInput = document.getElementById('seedbot_bot_name');
                const seedbotInitialGreetingInput = document.getElementById('seedbot_initial_greeting');
                const seedbotSubsequentGreetingInput = document.getElementById('seedbot_subsequent_greeting');
                const seedbotStartStatusInput = document.getElementById('seedBotStatus');
                const seedbotDisclaimerSettingInput = document.getElementById('seedbot_disclaimer_setting');
                // New options for max tokens and width - Ver 1.4.2
                const seedbotMaxTokensSettingInput = document.getElementById('seedbot_max_tokens_setting');
                const seedbotWidthSettingInput = document.getElementById('seedbot_width_setting');

                // Update the local storage with the input values, if inputs exist
                if(seedbotNameInput) localStorage.setItem('seedbot_bot_name', seedbotNameInput.value);
                if(seedbotInitialGreetingInput) localStorage.setItem('seedbot_initial_greeting', seedbotInitialGreetingInput.value);
                if(seedbotSubsequentGreetingInput) localStorage.setItem('seedbot_subsequent_greeting', seedbotSubsequentGreetingInput.value);
                if(seedbotStartStatusInput) localStorage.setItem('seedBotStatus', seedbotStartStatusInput.value);
                if(seedbotDisclaimerSettingInput) localStorage.setItem('seedbot_disclaimer_setting', seedbotDisclaimerSettingInput.value);
                if(seedbotMaxTokensSettingInput) localStorage.setItem('seedbot_max_tokens_setting', seedbotMaxTokensSettingInput.value);
                if(seedbotWidthSettingInput) localStorage.setItem('seedbot_width_setting', seedbotWidthSettingInput.value);
            });
        }
    });
</script>

	// Create settings tabs
	
        <h2 class="nav-tab-wrapper">
            <a href="?page=seedbot&tab=api_model" class="nav-tab <?php echo $active_tab == 'api_model' ? 'nav-tab-active' : ''; ?>">API/Model</a>
            <a href="?page=seedbot&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>

            <a href="?page=seedbot&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>">Support</a>
        </h2>

        <!-- CHECKPOINT Initiate Current Tabs Settings Form -->
        <form id="seedbot-settings-form" action="options.php" method="post">
            <?php
            if ($active_tab == 'settings') {
                settings_fields('seedbot_settings');
                do_settings_sections('seedbot_settings');
            } elseif ($active_tab == 'api_model') {
                settings_fields('seedbot_api_model');
                do_settings_sections('seedbot_api_model');
            } elseif ($active_tab == 'support') {
                settings_fields('seedbot_support');
                do_settings_sections('seedbot_support');
            }
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    </body>
    </html>
    <?php

}


// Register settings
function seedbot_settings_init() {

    // The API settings tab
    register_setting('seedbot_api_model', 'seedbot_api_key');
    register_setting('seedbot_api_model', 'gpt_model_choice');
    register_setting('seedbot_api_model', 'seedbot_max_tokens_setting');

    add_settings_section(
        'seedbot_api_model_section',
        'API/Model Settings',
        'seedbot_api_model_section_callback',
        'seedbot_api_model'
    );

    add_settings_field(
        'seedbot_api_key',
        'OpenAI API Key',
        'seedbot_api_key_callback',
        'seedbot_api_model',
        'seedbot_api_model_section'
    );

    add_settings_field(
        'gpt_model_choice',
        'ChatBot Model Choice',
        'seedbot_model_choice_callback',
        'seedbot_api_model',
        'seedbot_api_model_section'
    );
    
    // Setting to adjust in small increments the number of Max Tokens - Ver 1.4.2
    add_settings_field(
        'seedbot_max_tokens_setting',
        'Maximum Tokens Setting',
        'seedbot_max_tokens_setting_callback',
        'seedbot_api_model',
        'seedbot_api_model_section'
    );


    // Settings settings tab
    register_setting('seedbot_settings', 'seedbot_bot_name');
    register_setting('seedbot_settings', 'seedBotStatus');
    register_setting('seedbot_settings', 'seedbot_initial_greeting');
    register_setting('seedbot_settings', 'seedbot_subsequent_greeting');
    // Option to remove the OpenAI disclaimer - Ver 1.4.1
    register_setting('seedbot_settings', 'seedbot_disclaimer_setting');
    // Option to select narrow or wide chatboat - Ver 1.4.2
    register_setting('seedbot_settings', 'seedbot_width_setting');

    add_settings_section(
        'seedbot_settings_section',
        'Settings',
        'seedbot_settings_section_callback',
        'seedbot_settings'
    );

    add_settings_field(
        'seedbot_bot_name',
        'Bot Name',
        'seedbot_bot_name_callback',
        'seedbot_settings',
        'seedbot_settings_section'
    );

    add_settings_field(
        'seedBotStatus',
        'Start Status',
        'seedbotChatBotStatus_callback',
        'seedbot_settings',
        'seedbot_settings_section'
    );

    add_settings_field(
        'seedbot_initial_greeting',
        'Initial Greeting',
        'seedbot_initial_greeting_callback',
        'seedbot_settings',
        'seedbot_settings_section'
    );

    add_settings_field(
        'seedbot_subsequent_greeting',
        'Subsequent Greeting',
        'seedbot_subsequent_greeting_callback',
        'seedbot_settings',
        'seedbot_settings_section'
    );

    // Option to remove the OpenAI disclaimer - Ver 1.4.1
    add_settings_field(
        'seedbot_disclaimer_setting',
        'Include "As an AI language model" disclaimer',
        'seedbot_disclaimer_setting_callback',
        'seedbot_settings',
        'seedbot_settings_section'
    );

    // Option to change the width of the bot from narrow to wide - Ver 1.4.2
    add_settings_field(
        'seedbot_width_setting',
        'Chatbot Width Setting',
        'seedbot_width_setting_callback',
        'seedbot_settings',
        'seedbot_settings_section'
    );

    // Premium settings tab - Ver 1.3.0
    register_setting('seedbot_premium', 'seedbot_premium_key');

    add_settings_section(
        'seedbot_premium_section',
        'Premium Settings',
        'seedbot_premium_section_callback',
        'seedbot_premium'
    );

    add_settings_field(
        'seedbot_premium_key',
        'Premium Options',
        'seedbot_premium_key_callback',
        'seedbot_premium',
        'seedbot_premium_section'
    );

    // Support settings tab - Ver 1.3.0
    register_setting('seedbot_support', 'seedbot_support_key');

    add_settings_section(
        'seedbot_support_section',
        'Support',
        'seedbot_support_section_callback',
        'seedbot_support'
    );
        
}

add_action('admin_init', 'seedbot_settings_init');

// API/Model settings section callback - Ver 1.3.0
function seedbot_api_model_section_callback($args) {
    ?>
    <p>Configure settings for the SeedBot plugin by adding your API key and selection the GPT model of your choice.</p>
    <p>This plugin requires an API key from OpenAI to function. You can obtain an API key by signing up at <a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a>.</p>
    <p>More information about ChatGPT models and their capability can be found at <a href="https://platform.openai.com/docs/models/overview" taget="_blank">https://platform.openai.com/docs/models/overview</a>.</p>
    <p>Enter your OpenAI API key below and select the OpenAI model of your choice.</p>
    <?php
}

// Settings section callback - Ver 1.3.0
function seedbot_settings_section_callback($args) {
    ?>
    <p>Configure settings for the SeedBot plugin, including the bot name, start status, and greetings.</p>
    <?php
}

// Premium settings section callback - Ver 1.3.0
function seedbot_premium_section_callback($args) {
    ?>
    <p>Enter your premium key here.</p>
    <?php
}

// Support settings section callback - Ver 1.3.0
function seedbot_support_section_callback($args) {
    ?>
    <div>
	<h3>Description</h3>
    <p>SeedBot for WordPress is a plugin that allows you to effortlessly integrate OpenAI&#8217;s ChatGPT API into your website, providing a powerful, AI-driven chatbot for enhanced user experience and personalized support.</p>
    <p>ChatGPT is a conversational AI platform that uses natural language processing and machine learning algorithms to interact with users in a human-like manner. It is designed to answer questions, provide suggestions, and engage in conversations with users. ChatGPT is important because it can provide assistance and support to people who need it, especially in situations where human support is not available or is limited. It can also be used to automate customer service, reduce response times, and improve customer satisfaction. Moreover, ChatGPT can be used in various fields such as healthcare, education, finance, and many more.</p>
    <p>SeedBot leverages the OpenAI platform using the gpt-4 model brings it to life within your WordPress Website.</p>
    <p><b>Important Note:</b> This plugin requires an API key from OpenAI to function correctly. You can obtain an API key by signing up at <a href="https://platform.openai.com/account/api-keys" rel="nofollow ugc" target="_blank">https://platform.openai.com/account/api-keys</a>.<p>
    <h3>Features</h3>
    <ul style="list-style-type: disc; list-style-position: inside; padding-left: 1em;">
    <li>Easy setup and integration with OpenAI&#8217;s ChatGPT API</li>
    <li>Support for gpt-3.5-turbo</li>
    <li>Support for gpt-4 </li>
    <li>Floating chatbot interface with customizable appearance</li>
    <li>User-friendly settings page for managing API key and other parameters</li>
    <li>Collapsible chatbot interface when not in use</li>
    <li>Initial greeting message for first-time users</li>
    <li>Shortcode to embed the chatbot on any page or post</li>
    <li>Setting to determine if chatbot should start opened or closed</li>
    <li>Chatbot maintains state when navigating between pages</li>
    <li>Chatbot name and initial and subsequent greetings are configurable</li>
    </ul>
    <h3>Getting Started</h3>
    <ol>
    <li>Obtain your API key by signign up at <a href="https://platform.openai.com/account/api-keys" rel="nofollow ugc" target="_blank">https://platform.openai.com/account/api-keys</a>.</li>
    <li>Install and activate the SeedBot plugin.</li>
    <li>Navigate to the settings page (Settings &gt; API/Model) and enter your API key.</li>
    <li>Customize the chatbot appearance and other parameters as needed.</li>
    <li>Add the chatbot to any page or post using the provided shortcode: [chatbot_chatgpt]</li>
    </ol>
    <p>Now your website visitors can enjoy a seamless and personalized chat experience powered by OpenAI&#8217;s ChatGPT API.</p>
    <h2>Installation</h2>
	<ol>
    <li>Upload the &#8216;chatbot-chatgpt&#8217; folder to the &#8216;/wp-content/plugins/&#8217; directory.</li>
    <li>Activate the plugin through the &#8216;Plugins&#8217; menu in WordPress.</li>
    <li>Go to the &#8216;Settings &gt; Chatbot ChatGPT&#8217; page and enter your OpenAI API key.</li>
    <li>Customize the chatbot appearance and other parameters as needed.</li>
    <li>Add the chatbot to any page or post using the provided shortcode: [chatbot_chatgpt]</li>
    </ol>
    </div>
    <?php
}

// API key field callback
function seedbot_api_key_callback($args) {
    $api_key = esc_attr(get_option('seedbot_api_key'));
    ?>
    <input type="text" id="seedbot_api_key" name="seedbot_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text">
    <?php
}

// Model choice
function seedbot_model_choice_callback($args) {
    // Get the saved gpt_model_choice value or default to "gpt-4"
    $model_choice = esc_attr(get_option('gpt_model_choice', 'gpt-4'));
    ?>
    <select id="gpt_model_choice" name="gpt_model_choice">
        <!-- Add option for gpt-3.5-turbo -->
        <option value="<?php echo esc_attr( 'gpt-4' ); ?>" <?php selected( $model_choice, 'gpt-4' ); ?>><?php echo esc_html( 'gpt-4' ); ?></option>
        <option value="<?php echo esc_attr( 'gpt-3.5-turbo' ); ?>" <?php selected( $model_choice, 'gpt-3.5-turbo' ); ?>><?php echo esc_html( 'gpt-3.5-turbo' ); ?></option>
    </select>
    <?php
}

// Chatbot ChatGPT Name
function seedbot_bot_name_callback($args) {
    $bot_name = esc_attr(get_option('seedbot_bot_name', 'SeedBot'));
    ?>
    <input type="text" id="seedbot_bot_name" name="seedbot_bot_name" value="<?php echo esc_attr( $bot_name ); ?>" class="regular-text">
    <?php
}

function seedbotChatBotStatus_callback($args) {
    $start_status = esc_attr(get_option('seedBotStatus', 'closed'));
    ?>
    <select id="seedBotStatus" name="seedBotStatus">
        <option value="open" <?php selected( $start_status, 'open' ); ?>><?php echo esc_html( 'Open' ); ?></option>
        <option value="closed" <?php selected( $start_status, 'closed' ); ?>><?php echo esc_html( 'Closed' ); ?></option>
    </select>
    <?php
}

function seedbot_initial_greeting_callback($args) {
    $initial_greeting = esc_attr(get_option('seedbot_initial_greeting', 'Hello! How can I help you today?'));
    ?>
    <textarea id="seedbot_initial_greeting" name="seedbot_initial_greeting" rows="2" cols="50"><?php echo esc_textarea( $initial_greeting ); ?></textarea>
    <?php
}

function seedbot_subsequent_greeting_callback($args) {
    $subsequent_greeting = esc_attr(get_option('seedbot_subsequent_greeting', 'Hello again! How can I help you?'));
    ?>
    <textarea id="seedbot_subsequent_greeting" name="seedbot_subsequent_greeting" rows="2" cols="50"><?php echo esc_textarea( $subsequent_greeting ); ?></textarea>
    <?php
}

// Option to remove OpenAI disclaimer - Ver 1.4.1
function seedbot_disclaimer_setting_callback($args) {
    $chatgpt_disclaimer_setting = esc_attr(get_option('seedbot_disclaimer_setting', 'Yes'));
    ?>
    <select id="seedbot_disclaimer_setting" name="seedbot_disclaimer_setting">
        <option value="Yes" <?php selected( $chatgpt_disclaimer_setting, 'Yes' ); ?>><?php echo esc_html( 'Yes' ); ?></option>
        <option value="No" <?php selected( $chatgpt_disclaimer_setting, 'No' ); ?>><?php echo esc_html( 'No' ); ?></option>
    </select>
    <?php    
}

// Max Tokens choice - Ver 1.4.2
function seedbot_max_tokens_setting_callback($args) {
    // Get the saved seedbot_max_tokens_setting or default to 150
    $max_tokens = esc_attr(get_option('seedbot_max_tokens_setting', '150'));
    ?>
    <select id="seedbot_max_tokens_setting" name="seedbot_max_tokens_setting">
        <option value="<?php echo esc_attr( '100' ); ?>" <?php selected( $max_tokens, '100' ); ?>><?php echo esc_html( '100' ); ?></option>
        <option value="<?php echo esc_attr( '150' ); ?>" <?php selected( $max_tokens, '150' ); ?>><?php echo esc_html( '150' ); ?></option>
        <option value="<?php echo esc_attr( '200' ); ?>" <?php selected( $max_tokens, '200' ); ?>><?php echo esc_html( '200' ); ?></option>
        <option value="<?php echo esc_attr( '250' ); ?>" <?php selected( $max_tokens, '250' ); ?>><?php echo esc_html( '250' ); ?></option>
        <option value="<?php echo esc_attr( '300' ); ?>" <?php selected( $max_tokens, '300' ); ?>><?php echo esc_html( '300' ); ?></option>
        <option value="<?php echo esc_attr( '350' ); ?>" <?php selected( $max_tokens, '350' ); ?>><?php echo esc_html( '350' ); ?></option>
        <option value="<?php echo esc_attr( '400' ); ?>" <?php selected( $max_tokens, '400' ); ?>><?php echo esc_html( '400' ); ?></option>
        <option value="<?php echo esc_attr( '450' ); ?>" <?php selected( $max_tokens, '450' ); ?>><?php echo esc_html( '450' ); ?></option>
        <option value="<?php echo esc_attr( '500' ); ?>" <?php selected( $max_tokens, '500' ); ?>><?php echo esc_html( '500' ); ?></option>
    </select>
    <?php
}

// Option for narrow or wide chatbot - Ver 1.4.2
function seedbot_width_setting_callback($args) {
    $chatgpt_width = esc_attr(get_option('seedbot_width_setting', 'Narrow'));
    ?>
    <select id="seedbot_width_setting" name = "seedbot_width_setting">
        <option value="Narrow" <?php selected( $chatgpt_width, 'Narrow' ); ?>><?php echo esc_html( 'Narrow' ); ?></option>
        <option value="Wide" <?php selected( $chatgpt_width, 'Wide' ); ?>><?php echo esc_html( 'Wide' ); ?></option>
    </select>
    <?php
}

// Premium Key - Ver 1.3.0
function seedbot_premium_key_callback($args) {
    $premium_key = esc_attr(get_option('seedbot_premium_key'));
    ?>
    <input type="text" id="seedbot_premium_key" name="seedbot_premium_key" value="<?php echo esc_attr( $premium_key ); ?>" class="regular-text">
    <?php
}
