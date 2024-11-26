// Definición de tipos DBF y constantes
const DBF_TYPES = {
  C: "Carácter", // Character - Campo de texto
  N: "Numérico", // Numeric - Números con o sin decimales
  F: "Float", // Float - Números de punto flotante
  D: "Fecha", // Date - Formato fecha YYYYMMDD
  L: "Lógico", // Logical - Valores True/False
  M: "Memo", // Memo - Campo memo o texto largo
  B: "Binario", // Binary - Datos binarios
  G: "General", // General - Campo OLE
  P: "Picture", // Picture - Campo imagen
  Y: "Currency", // Currency - Campo moneda
  T: "DateTime", // DateTime - Fecha y hora
  I: "Integer", // Integer - Número entero
  V: "Varchar", // Varchar - Carácter de longitud variable
  "+": "Autoincrement", // Autoincrement - Campo autoincremental
  O: "Double", // Double - Doble precisión
  "@": "Timestamp", // Timestamp - Marca de tiempo
  0: "Null", // Indica campo que acepta nulos
  W: "Blob", // Binary Large Object
};

// Eventos de búsqueda y selección
document.addEventListener("DOMContentLoaded", function () {
  // Búsqueda de tablas
  const searchInput = document.getElementById("tableSearch");
  if (searchInput) {
    searchInput.addEventListener("input", function (e) {
      const searchTerm = e.target.value.toLowerCase();
      const rows = document.querySelectorAll("tbody tr[data-table-name]");

      rows.forEach((row) => {
        const tableName = row.getAttribute("data-table-name").toLowerCase();
        const shouldShow = tableName.includes(searchTerm);
        row.style.display = shouldShow ? "" : "none";

        // También ocultar/mostrar la fila de preview si existe
        const previewRow = document.getElementById(
          "preview-" + row.getAttribute("data-table-name")
        );
        if (previewRow) {
          previewRow.style.display = "none"; // Siempre ocultar previews al filtrar
        }
      });
    });
  }

  // Seleccionar/Deseleccionar todo
  const selectAllBtn = document.getElementById("selectAll");
  const deselectAllBtn = document.getElementById("deselectAll");

  if (selectAllBtn) {
    selectAllBtn.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll('input[name="tables[]"]');
      checkboxes.forEach((cb) => (cb.checked = true));
    });
  }

  if (deselectAllBtn) {
    deselectAllBtn.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll('input[name="tables[]"]');
      checkboxes.forEach((cb) => (cb.checked = false));
    });
  }
});

function fetchWithoutCache(url, options = {}) {
  return fetch(url, {
    ...options,
    cache: "no-store",
    headers: {
      ...options.headers,
      "Cache-Control": "no-cache, no-store, must-revalidate",
      Pragma: "no-cache",
      Expires: "0",
    },
  });
}

// Funciones para mostrar estructura
function showStructure(tableName) {
  const previewRow = document.getElementById("preview-" + tableName);

  if (previewRow.style.display === "table-row") {
    previewRow.style.display = "none";
    return;
  }

  previewRow.style.display = "table-row";
  const cell = previewRow.querySelector("td");
  cell.innerHTML =
    '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>';

  // Usar fetchWithoutCache
  fetchWithoutCache("get_structure.php?table=" + encodeURIComponent(tableName))
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        cell.innerHTML = `<div class="alert alert-danger m-2">${data.error}</div>`;
        return;
      }

      // Filtrar campos vacíos
      const validFields = data.fields.filter(
        (field) =>
          field.name &&
          field.name !== "<empty string>" &&
          field.name.trim() !== ""
      );

      // Console logs
      console.group("Estructura de la tabla: " + tableName);
      console.log("Número total de campos:", validFields.length);
      console.log("Número total de registros:", data.recordCount);
      console.table(validFields);
      console.group("Detalle de campos:");
      validFields.forEach((field, index) => {
        console.log(
          `${index + 1}. ${field.name} (${
            DBF_TYPES[field.type] || field.type
          }) - Longitud: ${field.length}${
            field.precision ? ", Decimales: " + field.precision : ""
          }`
        );
      });
      console.groupEnd();
      console.groupEnd();

      // Crear vista
      let html = '<div class="structure-view">';
      html += validFields.map((field) => createFieldHTML(field)).join("");
      html += "</div>";

      html += `
                <div class="text-muted small mt-1 px-2">
                    <span>Total registros: ${data.recordCount.toLocaleString()}</span>
                    <span class="ms-3">Total campos válidos: ${
                      validFields.length
                    }</span>
                </div>`;

      cell.innerHTML = html;
    })
    .catch((error) => {
      cell.innerHTML = `<div class="alert alert-danger m-2">Error al cargar la estructura: ${error.message}</div>`;
    });
}

// Funciones auxiliares
function createFieldHTML(field) {
  const { typeDesc, lengthInfo, tooltip } = formatFieldInfo(field);
  return `
        <span class="field-item" title="${tooltip}">
            <span class="field-name">${field.name}</span>
            <span class="field-type">(${typeDesc}</span>
            <span class="field-length">${lengthInfo})</span>
        </span>`;
}

function formatFieldInfo(field) {
  const typeDesc = DBF_TYPES[field.type] || field.type;
  let lengthInfo = field.length;

  if (field.precision !== null && field.precision > 0) {
    lengthInfo += "," + field.precision;
  }

  return {
    typeDesc,
    lengthInfo,
    tooltip: `Campo: ${field.name}
                 Tipo: ${typeDesc}
                 Longitud: ${lengthInfo}${
      field.precision !== null
        ? `
                 Decimales: ${field.precision}`
        : ""
    }`,
  };
}

// Función para manejar la conversión
async function handleConversion(e) {
    e.preventDefault();
    
    // Limpiar cookies y caché
    document.cookie.split(";").forEach(function(c) { 
        document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
    });
    
    const form = e.target;
    const formData = new FormData(form);

    console.log("Tablas seleccionadas:");
    for (let value of formData.getAll('tables[]')) {
        console.log(value);
    }
    
    if (form.querySelectorAll('input[name="tables[]"]:checked').length === 0) {
        alert('Por favor, seleccione al menos una tabla para convertir');
        return;
    }

    // Mostrar progreso
    const progressDiv = document.getElementById('conversionProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const logOutput = document.getElementById('logOutput');
    const convertButton = document.getElementById('convertButton');

    progressDiv.style.display = 'block';
    convertButton.disabled = true;
    logOutput.innerHTML = '';

    try {
        // Usar rutas absolutas con el puerto correcto
        const response = await fetch('http://localhost:8081/junta2024/conver.php', {
            method: 'POST',
            body: formData,
            cache: 'no-store'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Iniciar monitoreo del progreso
        const pollProgress = async () => {
            try {
                const statusResponse = await fetch('http://localhost:8081/junta2024/check_progress.php', {
                    cache: 'no-store'
                });
                
                if (!statusResponse.ok) {
                    throw new Error(`HTTP error! status: ${statusResponse.status}`);
                }
                
                const statusData = await statusResponse.json();

                if (statusData.log) {
                    // Actualizar log
                    logOutput.innerHTML = statusData.log.split('\n')
                        .filter(line => line.trim())
                        .map(line => `<div>${line}</div>`)
                        .join('');
                    logOutput.scrollTop = logOutput.scrollHeight;

                    // Actualizar barra de progreso si hay información de progreso
                    if (statusData.progress) {
                        progressBar.style.width = `${statusData.progress}%`;
                        progressText.textContent = `Progreso: ${statusData.progress}%`;
                    }
                }

                if (!statusData.completed) {
                    setTimeout(pollProgress, 1000);
                } else {
                    progressBar.style.width = '100%';
                    progressBar.classList.remove('progress-bar-animated');
                    progressText.textContent = 'Conversión completada';
                    convertButton.disabled = false;
                }
            } catch (error) {
                console.error('Error checking progress:', error);
                logOutput.innerHTML += `<div class="text-danger">Error: ${error.message}</div>`;
            }
        };

        pollProgress();

    } catch (error) {
        console.error('Error:', error);
        progressText.textContent = `Error: ${error.message}`;
        progressDiv.classList.add('text-danger');
        convertButton.disabled = false;
    }
}

// Añadir manejador para recargar página
window.addEventListener('load', function() {
    // Limpiar caché al cargar
    if (window.performance && window.performance.navigation.type === 1) {
        // Es un refresco (F5)
        document.cookie.split(";").forEach(function(c) { 
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
        });
        caches.keys().then(function(names) {
            for (let name of names) caches.delete(name);
        });
    }
});

// Agregar el manejador al formulario
document.querySelector('form').addEventListener('submit', handleConversion);