function showMessage(message) {
    const messageContainer = document.getElementById('message-container');
    messageContainer.innerText = message;
    messageContainer.style.display = 'block';
    setTimeout(function() {
        messageContainer.style.display = 'none';
        showNextMessage();
    }, 3000); // Ocultar mensaje después de 3 segundos
}

function queueMessage(message) {
    messageQueue.push(message);
}

function showNextMessage() {
    if (messageQueue.length > 0) {
        showMessage(messageQueue.shift());
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript cargado y DOM completamente cargado.');
    showNextMessage(); // Muestra el primer mensaje en la cola si existe
});

// Función para ser llamada desde PHP
function addPhpMessage(message) {
    queueMessage(message);
    if (document.readyState === 'complete') {
        showNextMessage();
    }
}



