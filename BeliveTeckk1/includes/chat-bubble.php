<?php
require_once(__DIR__ . '/../config/config.php');
require_once __DIR__ . '/../classes/Database.php';
require_once(__DIR__ . '/../classes/Admin.php');

$db = Database::getInstance();
$admin = new Admin($db);
$settings = $admin->getSettings();
$services = $admin->getServices();
?>

<div id="ai-chat-bubble" class="fixed bottom-4 right-4 z-50">
    <!-- Chat Bubble Button -->
    <button id="chat-toggle" class="bg-red-500 hover:bg-blue-600 text-white rounded-full p-4 shadow-lg flex items-center justify-center">
        <i class="fas fa-robot text-2xl"></i>
    </button>

    <!-- Chat Window -->
    <div id="chat-window" class="hidden absolute bottom-16 right-0 w-96 bg-white rounded-lg shadow-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-red-500 to-blue-600 text-white rounded-t-lg">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-robot text-red-500"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Believe Teckk Assistant</h3>
                        <p class="text-xs text-red-100">Online</p>
                    </div>
                </div>
                <button id="close-chat" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div id="chat-messages" class="p-4 h-96 overflow-y-auto bg-gray-50">
            
        </div>
        <div class="p-4 border-t border-gray-200 bg-white rounded-b-lg">
            <div class="flex gap-2">
                <input type="text" id="user-message" class="flex-1 border border-gray-300 rounded-lg px-3 py-2" placeholder="Ask me anything about Believe Teckk...">
                <button id="send-message" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Site information object
    const siteInfo = {
        name: <?php echo json_encode($settings['site_name'] ?? 'Believe Teckk'); ?>,
        description: <?php echo json_encode($settings['site_description'] ?? 'Your Trusted Technology Partner'); ?>,
        contact: {
            phone: <?php echo json_encode($settings['contact_phone'] ?? ''); ?>,
            email: <?php echo json_encode($settings['contact_email'] ?? ''); ?>,
            address: <?php echo json_encode($settings['contact_address'] ?? ''); ?>
        },
        services: <?php echo json_encode(array_map(function($service) {
            return [
                'title' => $service['title'],
                'description' => $service['description']
            ];
        }, $services)); ?>
    };

    const chatToggle = document.getElementById('chat-toggle');
    const chatWindow = document.getElementById('chat-window');
    const closeChat = document.getElementById('close-chat');
    const sendMessage = document.getElementById('send-message');
    const userMessage = document.getElementById('user-message');
    const chatMessages = document.getElementById('chat-messages');

    function getAIResponse(message) {
        message = message.toLowerCase();
        
        // Enhanced response logic with more patterns
        if (message.includes('contact') || message.includes('reach') || message.includes('call') || message.includes('email')) {
            return `You can contact ${siteInfo.name} through:\n` +
                   `📞 Phone: ${siteInfo.contact.phone}\n` +
                   `📧 Email: ${siteInfo.contact.email}\n` +
                   `📍 Address: ${siteInfo.contact.address}\n\n` +
                   `We'll be happy to assist you with any inquiries!`;
        }
        
        if (message.includes('service') || message.includes('offer') || message.includes('do you do') || message.includes('help') || message.includes('work')) {
            let response = `At ${siteInfo.name}, we specialize in the following services:\n\n`;
            siteInfo.services.forEach(service => {
                response += `📌 ${service.title}\n${service.description}\n\n`;
            });
            response += `Would you like to know more about any specific service?`;
            return response;
        }
        
        if (message.includes('about') || message.includes('who') || message.includes('what is') || message.includes('company')) {
            return `Welcome to ${siteInfo.name}! 🚀\n\n${siteInfo.description}\n\nWe're committed to delivering exceptional technology solutions and services to our clients. Would you like to know about our specific services or how to get in touch?`;
        }

        if (message.includes('hello') || message.includes('hi') || message.includes('hey') || message.includes('good') || message.includes('morning') || message.includes('afternoon') || message.includes('evening')) {
            return `Hello! 👋 Welcome to ${siteInfo.name}!\n\nI'm your AI assistant, ready to help you with:\n` +
                   `• Information about our services\n` +
                   `• Contact details\n` +
                   `• Company information\n\n` +
                   `What would you like to know about?`;
        }

        if (message.includes('thank') || message.includes('thanks') || message.includes('appreciated')) {
            return `You're welcome! 😊 If you have any more questions, feel free to ask. We're here to help!`;
        }

        if (message.includes('bye') || message.includes('goodbye') || message.includes('see you') || message.includes('Goodbye') || message.includes('see you later')) {
            return `Thank you for chatting with us! 👋 If you need anything else, don't hesitate to come back. Have a great day!`;
        }

        if (message.includes('price') || message.includes('cost') || message.includes('pricing') || message.includes('package')) {
            return `For detailed pricing information, please contact our team at:\n` +
                   `📞 ${siteInfo.contact.phone}\n` +
                   `📧 ${siteInfo.contact.email}\n\n` +
                   `We offer customized solutions tailored to your specific needs and requirements.`;
        }

        if (message.includes('location') || message.includes('where') || message.includes('office') || message.includes('find')) {
            return `You can find us at:\n📍 ${siteInfo.contact.address}\n\nFeel free to visit us or contact us through:\n` +
                   `📞 ${siteInfo.contact.phone}\n` +
                   `📧 ${siteInfo.contact.email}`;
        }

        if (message.includes('career') || message.includes('job') || message.includes('position') || message.includes('vacancy') || message.includes('employment')) {
            return `🎯 Career Opportunities at ${siteInfo.name}!\n\n` +
                   `We're always looking for talented individuals to join our team. Here's what you need to know:\n\n` +
                   `• Check our current openings on the Careers page\n` +
                   `• Submit your resume and portfolio\n` +
                   `• We value creativity and innovation\n` +
                   `• Competitive compensation packages\n` +
                   `• Professional growth opportunities\n\n` +
                   `To apply or learn more about our open positions, visit our Careers page or contact us at:\n` +
                   `📧 ${siteInfo.contact.email}`;
        }

        if (message.includes('training') || message.includes('learn') || message.includes('course') || message.includes('program') || message.includes('workshop')) {
            return `📚 Training Programs at ${siteInfo.name}!\n\n` +
                   `We offer various training opportunities:\n\n` +
                   `• Professional development programs\n` +
                   `• Technical skills workshops\n` +
                   `• Industry-specific training\n` +
                   `• Hands-on learning experience\n` +
                   `• Certified instructors\n\n` +
                   `For more information about our training programs or to apply:\n` +
                   `📧 Email us: ${siteInfo.contact.email}\n` +
                   `📞 Call us: ${siteInfo.contact.phone}\n\n` +
                   `Would you like to know about specific training programs or application process?`;
        }

        if (message.includes('apply') || message.includes('application') || message.includes('submit') || message.includes('resume')) {
            return `📝 Application Process at ${siteInfo.name}:\n\n` +
                   `To apply for a position or training program:\n\n` +
                   `1. Visit our Careers/Training page\n` +
                   `2. Select the position/program you're interested in\n` +
                   `3. Submit your application with required documents\n` +
                   `4. Our team will review and contact qualified candidates\n\n` +
                   `For any questions about the application process:\n` +
                   `📧 ${siteInfo.contact.email}\n` +
                   `📞 ${siteInfo.contact.phone}`;
        }
        
        // Enhanced default response
        return `I'm here to help you learn more about ${siteInfo.name}! 🌟\n\n` +
               `You can ask me about:\n` +
               `• Our comprehensive services and solutions\n` +
               `• How to contact our team\n` +
               `• Company information and expertise\n` +
               `• Pricing and packages\n` +
               `• Office location and visits\n\n` +
               `What would you like to know more about?`;
    }

    function sendUserMessage() {
        const message = userMessage.value.trim();
        if (message) {
            appendMessage('user', message);
            userMessage.value = '';
            
            // Get AI response based on site information
            setTimeout(() => {
                const response = getAIResponse(message);
                appendMessage('ai', response);
            }, 500);
        }
    }

    // Toggle chat window
  
    chatToggle.addEventListener('click', () => {
        chatWindow.classList.toggle('hidden');
        if (!chatWindow.classList.contains('hidden')) {
            userMessage.focus();
            // Only add welcome message if it's the first time opening
            if (chatMessages.children.length === 0) {
                appendMessage('ai', `Hello! 👋 I'm the ${siteInfo.name} AI assistant. How can I help you today?`);
            }
        }
    });

    closeChat.addEventListener('click', () => {
        chatWindow.classList.add('hidden');
    });

    sendMessage.addEventListener('click', sendUserMessage);

    userMessage.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendUserMessage();
        }
    });

    // Append message to chat
    function appendMessage(type, text) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `mb-4 ${type === 'user' ? 'text-right' : ''}`;
        
        const messageBubble = document.createElement('div');
        messageBubble.className = type === 'user' 
            ? 'inline-block bg-red-500 text-white rounded-lg py-2 px-4 max-w-[80%]'
            : 'inline-block bg-gray-100 text-gray-800 rounded-lg py-2 px-4 max-w-[80%]';
        messageBubble.style.whiteSpace = 'pre-line';
        messageBubble.textContent = text;
        
        messageDiv.appendChild(messageBubble);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});
</script>