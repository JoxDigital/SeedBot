jQuery(document).ready(function ($) {

    // This is /assets/seedbot.js

    // Logging for Diagnostics
    var seedbot_diagnostics = localStorage.getItem('seedbot_diagnostics') || 'Off';
    localStorage.setItem('seedbot_diagnostics', seedbot_diagnostics); // Set if not set

    var messageInput = $('#seedbot-message');
    var conversation = $('#seedbot-conversation');
    var submitButton = $('#seedbot-submit');

    // Set bot width with the default Narrow or from setting Wide
    var seedbot_width_setting = localStorage.getItem('seedbot_width_setting') || 'Narrow';

    var seedbotChatBot = $('#seedbot-chatbot');
    if (seedbot_width_setting === 'Wide') {
        seedbotChatBot.addClass('wide');
    } else {
        seedbotChatBot.removeClass('wide');
    }

    // Diagnostics
    if (seedbot_diagnostics === 'On') {
        console.log(messageInput);
        console.log(conversation);
        console.log(submitButton);
        console.log(seedbotChatBot);
        console.log('seedbot_width_setting: ' + seedbot_width_setting);
    }

    var seedbotOpenButton = $('#seedbot-open-btn');
    // Use 'open' for an open chatbot or 'closed' for a closed chatbot
    var seedbot_start_status = 'closed';
    
    // Initially hide the chatbot
    seedbotChatBot.hide();
    seedbotOpenButton.show();

    var seedbotContainer = $('<div></div>').addClass('seedbot-container');
    var seedbotCollapseBtn = $('<button></button>').addClass('seedbot-collapse-btn').addClass('dashicons dashicons-format-chat'); // Add a collapse button
    var seedbotCollapsed = $('<div></div>').addClass('seedbot-collapsed'); // Add a collapsed chatbot icon dashicons-format-chat f125

    // Support variable greetings based on setting
    var initialGreeting = localStorage.getItem('seedbot_initial_greeting') || 'Hello! How can I help you today?';
    localStorage.setItem('seedbot_initial_greeting', initialGreeting);
    var subsequentGreeting = localStorage.getItem('seedbot_subsequent_greeting') || 'Hello again! How can I help you?';
    localStorage.setItem('seedbot_subsequent_greeting', subsequentGreeting);
    // Handle disclaimer
    var seedbot_disclaimer_setting = localStorage.getItem('seedbot_disclaimer_setting') || 'Yes';

    // Append the collapse button and collapsed chatbot icon to the chatbot container
    seedbotContainer.append(seedbotCollapseBtn);
    seedbotContainer.append(seedbotCollapsed);

    // Add initial greeting to the chatbot
    conversation.append(seedbotContainer);

    function initializeChatbot() {
        var seedbot_diagnostics = localStorage.getItem('seedbot_diagnostics') || 'Off';
        var isFirstTime = !localStorage.getItem('seedbotChatbotOpened');
        var initialGreeting;
        // Remove any legacy conversations that might be store in local storage for increased privacy - Ver 1.4.2
        localStorage.removeItem('seedbot_conversation');
  
        if (isFirstTime) {
            initialGreeting = localStorage.getItem('seedbot_initial_greeting') || 'Hello! How can I help you today?';

            // Logging for Diagnostics - Ver 1.4.2
            if (seedbot_diagnostics === 'On') {
                console.log("initialGreeting" . initialGreeting);
            }

            // Don't append the greeting if it's already in the conversation
            if (conversation.text().includes(initialGreeting)) {
                return;
            }

            appendMessage(initialGreeting, 'bot', 'initial-greeting');
            localStorage.setItem('seedbotChatbotOpened', 'true');
            // Save the conversation after the initial greeting is appended
            sessionStorage.setItem('seedbot_conversation', conversation.html());

        } else {
            
            initialGreeting = localStorage.getItem('seedbot_subsequent_greeting') || 'Hello again! How can I help you?';

            // Logging for Diagnostics - Ver 1.4.2
            if (seedbot_diagnostics === 'On') {
                console.log("initialGreeting" . initialGreeting);
            }

            // Don't append the greeting if it's already in the conversation
            if (conversation.text().includes(initialGreeting)) {
                return;
            }
            
            appendMessage(initialGreeting, 'bot', 'initial-greeting');
            localStorage.setItem('seedbotChatbotOpened', 'true');
        }
    }


    // Add chatbot header, body, and other elements - Ver 1.1.0
    var seedbotHeader = $('<div></div>').addClass('seedbot-header');
    seedbotChatBot.append(seedbotHeader);

    seedbotHeader.append(seedbotCollapseBtn);
    seedbotHeader.append(seedbotCollapsed);

    // Attach the click event listeners for the collapse button and collapsed chatbot icon
    seedbotCollapseBtn.on('click', toggleChatbot);
    seedbotCollapsed.on('click', toggleChatbot);
    seedbotCollapseBtn.on('click', toggleChatbot);

    function appendMessage(message, sender, cssClass) {
    var messageElement = $('<div></div>').addClass('chat-message');
    var textElement = $('<span></span>').text(message);

    // Add initial greetings if first time
    if (cssClass) {
        textElement.addClass(cssClass);
    }

    if (sender === 'user') {
        messageElement.addClass('user-message');
        textElement.addClass('user-text');
    } else if (sender === 'bot') {
        messageElement.addClass('bot-message');
        textElement.addClass('bot-text');
    } else {
        messageElement.addClass('error-message');
        textElement.addClass('error-text');
    }

    messageElement.append(textElement);
    conversation.append(messageElement);

    // Add space between user input and bot response
    if (sender === 'user' || sender === 'bot') {
        var spaceElement = $('<div></div>').addClass('message-space');
        conversation.append(spaceElement);
    }

    
    // conversation.scrollTop(conversation[0].scrollHeight);
    conversation[0].scrollTop = conversation[0].scrollHeight;

    // Save the conversation locally between bot sessions
    sessionStorage.setItem('seedbot_conversation', conversation.html());

    }

    function showTypingIndicator() {
        var typingIndicator = $('<div></div>').addClass('typing-indicator');
        var dot1 = $('<span>.</span>').addClass('typing-dot');
        var dot2 = $('<span>.</span>').addClass('typing-dot');
        var dot3 = $('<span>.</span>').addClass('typing-dot');
        
        typingIndicator.append(dot1, dot2, dot3);
        conversation.append(typingIndicator);
        conversation.scrollTop(conversation[0].scrollHeight);
    }

    function removeTypingIndicator() {
        $('.typing-indicator').remove();
    }

    submitButton.on('click', function () {
        var message = messageInput.val().trim();
        var seedbot_disclaimer_setting = localStorage.getItem('seedbot_disclaimer_setting') || 'Yes';

        if (!message) {
            return;
        }
            
        messageInput.val('');
        appendMessage(message, 'user');

        $.ajax({
            url: seedbot_params.ajax_url,
            method: 'POST',
            data: {
                action: 'seedbot_send_message',
                message: message,
            },
            beforeSend: function () {
                showTypingIndicator();
                submitButton.prop('disabled', true);
            },
            success: function (response) {
                removeTypingIndicator();
                if (response.success) {
                    let botResponse = response.data;
                    const prefix_a = "As an AI language model, ";
                    const prefix_b = "I am an AI language model and ";

                    if (botResponse.startsWith(prefix_a) && seedbot_disclaimer_setting === 'No') {
                        botResponse = botResponse.slice(prefix_a.length);
                    } else if (botResponse.startsWith(prefix_b) && seedbot_disclaimer_setting === 'No') {
                        botResponse = botResponse.slice(prefix_b.length);
                    }
                                    
                    appendMessage(botResponse, 'bot');
                } else {
                    appendMessage('Error: ' + response.data, 'error');
                }
            },
            error: function () {
                removeTypingIndicator();
                appendMessage('Error: Unable to send message', 'error');
            },
            complete: function () {
                removeTypingIndicator();
                submitButton.prop('disabled', false);
            },
        });
    });

    messageInput.on('keydown', function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            submitButton.click();
        }
    });

    // Add the toggleChatbot() function
    function toggleChatbot() {
        if (seedbotChatBot.is(':visible')) {
            seedbotChatBot.hide();
            seedbotOpenButton.show();
            localStorage.setItem('seedbotStatus', 'closed');
            // Clear the conversation when the chatbot is closed
            // Keep the conversation when the chatbot is closed
            // sessionStorage.removeItem('seedbot_conversation');
        } else {
            seedbotChatBot.show();
            seedbotCollapseBtn.hide();
            localStorage.setItem('seedbotStatus', 'open');
            loadConversation();
            scrollToBottom();
        }
    }

    // Add this function to maintain the chatbot status across page refreshes and sessions - Ver 1.1.0 and updated for Ver 1.4.1
    function loadSeedbotStatus() {
        const seedbotStatus = localStorage.getItem('seedbotStatus');
        // const seedbotStatus = localStorage.getItem('chatgpt_start_status');
        
        // If the chatbot status is not set in local storage, use chatgpt_start_status
        if (seedbotStatus === null) {
            if (seedbot_start_status === 'closed') {
                seedbotChatBot.hide();
                seedbotCollapseBtn.show();
            } else {
                seedbotChatBot.show();
                seedbotCollapseBtn.hide();
                // Load the conversation when the chatbot is shown on page load
                loadConversation();
                scrollToBottom();
            }
        } else if (seedbotStatus === 'closed') {
            if (seedbotChatBot.is(':visible')) {
                seedbotChatBot.hide();
                seedbotCollapseBtn.show();
            }
        } else if (seedbotStatus === 'open') {
            if (seedbotChatBot.is(':hidden')) {
                seedbotChatBot.show();
                seedbotCollapseBtn.hide();
                loadConversation();
                scrollToBottom();
            }
        }
    }

    // Add this function to scroll to the bottom of the conversation - Ver 1.2.1
    function scrollToBottom() {
        setTimeout(() => {
            // Logging for Diagnostics - Ver 1.4.2
            if (seedbot_diagnostics === 'On') {
                console.log("Scrolling to bottom");
                console.log("Scroll height: " + conversation[0].scrollHeight);
            }
            conversation.scrollTop(conversation[0].scrollHeight);
        }, 100);  // delay of 100 milliseconds    
    }
   
    // Load conversation from local storage if available 
    function loadConversation() {
        var storedConversation = sessionStorage.getItem('seedbot_conversation');
        if (storedConversation) {
            conversation.append(storedConversation);
            // Use setTimeout to ensure scrollToBottom is called after the conversation is rendered
            setTimeout(scrollToBottom, 0);
        } else {
            initializeChatbot();
        }
    }

    // Call the loadSeedbotStatus function here
    loadSeedbotStatus(); 

    // Load the conversation when the chatbot is shown on page load 
    // Let the convesation stay persistent in session storage for increased privacy
    // loadConversation();

});