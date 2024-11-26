
// Función para borrar el caché de las últimas 2 horas
function clearRecentCache() {
    if ('caches' in window) {
      caches.keys().then(function(cacheNames) {
        const twoHoursAgo = Date.now() - (2 * 60 * 60 * 1000);
        
        cacheNames.forEach(function(cacheName) {
          caches.open(cacheName).then(function(cache) {
            cache.keys().then(function(requests) {
              requests.forEach(function(request) {
                cache.match(request).then(function(response) {
                  if (response) {
                    const dateHeader = response.headers.get('date');
                    if (dateHeader) {
                      const cacheDate = new Date(dateHeader).getTime();
                      if (cacheDate > twoHoursAgo) {
                        cache.delete(request);
                      }
                    }
                  }
                });
              });
            });
          });
        });
      });
    }
  }
  
  // Ejecutar la función cuando se carga la página
  window.addEventListener('load', clearRecentCache);




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



