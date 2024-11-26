function showMessage(message) {
    const messageContainer = document.getElementById('message-container');
    messageContainer.innerText = message;
    messageContainer.style.display = 'block';

    setTimeout(function() {
        messageContainer.style.display = 'none';
    }, 3000); // Ocultar mensaje después de 3 segundos
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript cargado y DOM completamente cargado.');
});

