
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





document.addEventListener('DOMContentLoaded', function() {
    var btnAbrirPopup = document.getElementById('btn-abrir-popup');
    var overlay = document.getElementById('overlay');
    var popup = document.getElementById('popup');
    var btnEnviarCorreo = document.getElementById('btn-enviar-correo');
    var btnDescargarLocal = document.getElementById('btn-descargar-local');
    var btnCancelar = document.getElementById('btn-cancelar');

    btnAbrirPopup.addEventListener('click', function(e){
        e.preventDefault();
        overlay.classList.add('active');
        popup.classList.add('active');
    });


    btnEnviarCorreo.addEventListener('click', function(e){
        e.preventDefault();
        console.log('Enviando informe por correo...');
        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=enviarInforme'
        })
        .then(response => response.text())
        .then(data => {
            // Eliminar cualquier contenido HTML o script de la respuesta
            let cleanedData = data.replace(/<\/?[^>]+(>|$)/g, "").trim();
            // Mostrar solo "Mensaje enviado correctamente" si está presente
            if (cleanedData.includes("Mensaje enviado correctamente")) {
                alert("Mensaje enviado correctamente");
            } else {
                alert(cleanedData || "Error al enviar el mensaje");
            }
            cerrarPopup();
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Hubo un error al enviar el informe');
        });
    });


    btnDescargarLocal.addEventListener('click', function(e){
        e.preventDefault();
        console.log('Iniciando descarga local...');
        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=descargarInforme'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'RecibosErroneos_' + new Date().toISOString().split('T')[0] + '.txt';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            cerrarPopup();
            console.log('Descarga iniciada');
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Hubo un error al descargar el archivo: ' + error.message);
        });
    });

    btnCancelar.addEventListener('click', function(e){
        e.preventDefault();
        cerrarPopup();
    });

    function cerrarPopup() {
        overlay.classList.remove('active');
        popup.classList.remove('active');
    }

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});

