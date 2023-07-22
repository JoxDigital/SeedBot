<?php
/**
 * SeedBot GPT-4 Plugin for WordPress - Shortcode Registration
 * 
 * This is /includes/seedbot-shortcode.php
 *
 * This file contains the code for registering the shortcode used
 * to display the SeedBot chatbot on the website.
 *
 * @package seedbot
 */

function seedbot_shortcode() {
    // Retrieve the bot name - Ver 1.1.0
    // Add styling to the bot to ensure that it is not shown before it is needed Ver 1.2.0
    $bot_name = esc_attr(get_option('seedbot_bot_name', 'SeedBot'));
    ob_start();
    ?>
    <div id="seedbot" style="display: none;">
        <div id="seedbot-header">
            <div id="seedbotTitle" class="title"><?php echo $bot_name; ?></div>
        </div>
        <div id="seedbot-conversation"></div>
        <div id="seedbot-input">
            <input type="text" id="seedbot-message" placeholder="<?php echo esc_attr( 'Type your message...' ); ?>">
            <button id="seedbot-submit">Send</button>
        </div>
    </div>
    <button id="seedbot-open-btn" style="display: none;">
    <i class="dashicons dashicons-format-chat"></i>
    </button>
    <?php
    return ob_get_clean();
}
add_shortcode('seedbot', 'seedbot_shortcode');