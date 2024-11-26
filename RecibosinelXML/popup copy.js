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
        realizarAccion('enviarInforme');
    });

    btnDescargarLocal.addEventListener('click', function(e){
        e.preventDefault();
        realizarAccion('descargarInforme');
    });

    btnCancelar.addEventListener('click', function(e){
        e.preventDefault();
        cerrarPopup();
    });

    function realizarAccion(accion) {
        console.log('Realizando acción: ' + accion);
        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=' + accion
        })
        .then(response => {
            if (accion === 'descargarInforme') {
                return response.blob();
            } else {
                return response.text();
            }
        })
        .then(data => {
            if (accion === 'descargarInforme') {
                const url = window.URL.createObjectURL(data);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = 'RecibosErroneos_' + new Date().toISOString().split('T')[0] + '.txt';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            } else {
                alert(data);
            }
            cerrarPopup();
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Hubo un error al procesar la solicitud');
        });
    }

    function cerrarPopup() {
        overlay.classList.remove('active');
        popup.classList.remove('active');
    }
});