let storedApiKey = '';

function saveApiKey() {
    const keyInput = document.getElementById('apiKey');
    storedApiKey = keyInput.value.trim();
    keyInput.value = '';
    alert('API key saved securely!');
}

async function sendMessage() {
    const userInput = document.getElementById('userInput');
    const message = userInput.value.trim();
    const chatHistory = document.getElementById('chatHistory');
    
    if (!message) return;
    if (!storedApiKey) {
        alert('Please enter and save your OpenAI API key first!');
        return;
    }

    // Add user message
    chatHistory.innerHTML += `
        <div class="message user-message">
            <strong>You:</strong> ${message}
        </div>
    `;

    // Clear input
    userInput.value = '';

    // Create OpenAI client
    const openai = new OpenAI({
        apiKey: storedApiKey,
        dangerouslyAllowBrowser: true
    });

    try {
        const completion = await openai.chat.completions.create({
            model: "gpt-3.5-turbo",
            messages: [{
                role: "user",
                content: `As a travel journal assistant, help me with: ${message}`
            }],
            temperature: 0.7
        });

        const response = completion.choices[0].message.content;
        
        // Add bot response
        chatHistory.innerHTML += `
            <div class="message bot-message">
                <strong>Assistant:</strong> ${response}
            </div>
        `;
        
        // Scroll to bottom
        chatHistory.scrollTop = chatHistory.scrollHeight;

    } catch (error) {
        alert('Error contacting AI: ' + error.message);
    }
}