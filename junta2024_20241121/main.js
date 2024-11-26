// Definición de tipos DBF y constantes
const DBF_TYPES = {
  C: "Carácter",
  N: "Numérico",
  F: "Float",
  D: "Fecha",
  L: "Lógico",
  M: "Memo",
  B: "Binario",
  G: "General",
  P: "Picture",
  Y: "Currency",
  T: "DateTime",
  I: "Integer",
  V: "Varchar",
  "+": "Autoincrement",
  O: "Double",
  "@": "Timestamp",
  0: "Null",
  W: "Blob"
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
                  previewRow.style.display = "none";
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

  // Formulario de conversión
  const form = document.getElementById('convertForm');
  if (form) {
      form.addEventListener('submit', handleConversion);
  }
});

// Función para hacer fetch sin caché
async function fetchWithoutCache(url, options = {}) {
  return fetch(url, {
      ...options,
      cache: 'no-store',
      headers: {
          ...options.headers,
          'Cache-Control': 'no-cache, no-store, must-revalidate',
          'Pragma': 'no-cache',
          'Expires': '0'
      }
  });
}

// Función para mostrar estructura de tabla
function showStructure(tableName) {
  const previewRow = document.getElementById("preview-" + tableName);
  if (!previewRow) return;

  if (previewRow.style.display === "table-row") {
      previewRow.style.display = "none";
      return;
  }

  previewRow.style.display = "table-row";
  const cell = previewRow.querySelector("td");
  cell.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>';

  // Usar fetchWithoutCache
  fetchWithoutCache("get_structure.php?table=" + encodeURIComponent(tableName))
      .then((response) => response.json())
      .then((data) => {
          if (data.error) {
              cell.innerHTML = `<div class="alert alert-danger m-2">${data.error}</div>`;
              return;
          }

          // Filtrar campos válidos
          const validFields = data.fields.filter(
              (field) => field.name && field.name !== "<empty string>" && field.name.trim() !== ""
          );

          // Crear vista
          let html = '<div class="structure-view">';
          html += validFields.map((field) => {
              const typeDesc = DBF_TYPES[field.type] || field.type;
              const lengthInfo = field.length + (field.precision ? `,${field.precision}` : '');
              return `<span class="field-item" title="Campo: ${field.name}&#10;Tipo: ${typeDesc}&#10;Longitud: ${lengthInfo}">
                  <span class="field-name">${field.name}</span>
                  <span class="field-type">(${typeDesc}</span>
                  <span class="field-length">${lengthInfo})</span>
              </span>`;
          }).join("");

          html += `</div><div class="text-muted small mt-1 px-2">
              <span>Total registros: ${data.recordCount.toLocaleString()}</span>
              <span class="ms-3">Total campos válidos: ${validFields.length}</span>
          </div>`;

          cell.innerHTML = html;
      })
      .catch((error) => {
          cell.innerHTML = `<div class="alert alert-danger m-2">Error: ${error.message}</div>`;
      });
}

// Función para manejar la conversión
// Modificar solo la función handleConversion, el resto del código se mantiene igual

async function handleConversion(e) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData(form);
  const selectedTables = formData.getAll('tables[]');

  // Log inicial
  console.log('Tablas seleccionadas:', selectedTables);

  if (selectedTables.length === 0) {
      alert('Por favor, seleccione al menos una tabla para convertir');
      return;
  }

  // Mostrar progreso
  const progressDiv = document.getElementById('conversionProgress');
  const progressBar = document.getElementById('progressBar');
  const progressText = document.getElementById('progressText');
  const logOutput = document.getElementById('logOutput');
  const convertButton = document.getElementById('convertButton');

  // Función para actualizar el log visual
  const updateLog = (message, isError = false) => {
      const div = document.createElement('div');
      div.className = isError ? 'text-danger' : 'text-info';
      div.textContent = `${new Date().toLocaleTimeString()} - ${message}`;
      logOutput.appendChild(div);
      logOutput.scrollTop = logOutput.scrollHeight;
      console.log(isError ? 'ERROR:' : 'INFO:', message);
  };

  progressDiv.style.display = 'block';
  convertButton.disabled = true;
  logOutput.innerHTML = '';
  updateLog('Iniciando proceso de conversión...');

  try {
      updateLog('Enviando petición a conver.php...');
      
      // Mostrar los datos que se están enviando
      const formDataObj = {};
      formData.forEach((value, key) => {
          formDataObj[key] = value;
      });
      console.log('Datos enviados:', formDataObj);

      // Usar el nombre correcto del archivo: conver.php
      const response = await fetch('/junta2024/conver.php', {
          method: 'POST',
          body: formData,
          cache: 'no-store',
          headers: {
              'Cache-Control': 'no-cache, no-store, must-revalidate',
              'Pragma': 'no-cache',
              'Expires': '0'
          }
      });

      if (!response.ok) {
          throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
      }

      const result = await response.json();
      console.log('Respuesta del servidor:', result);
      updateLog('Respuesta recibida del servidor');

      if (!result.success) {
          throw new Error(result.error || 'Error desconocido en la conversión');
      }

      // Monitorear progreso
      let lastProgress = 0;
      const checkProgress = async () => {
          try {
              updateLog('Verificando progreso...');
              
              const statusResponse = await fetch('/junta2024/check_progress.php', {
                  cache: 'no-store',
                  headers: {
                      'Cache-Control': 'no-cache, no-store, must-revalidate',
                      'Pragma': 'no-cache',
                      'Expires': '0'
                  }
              });
              
              if (!statusResponse.ok) {
                  throw new Error(`Error HTTP: ${statusResponse.status} - ${statusResponse.statusText}`);
              }
              
              const statusData = await statusResponse.json();
              console.log('Estado de progreso:', statusData);

              if (statusData.log) {
                  // Actualizar solo las nuevas líneas del log
                  const newLines = statusData.log.split('\n')
                      .filter(line => line.trim())
                      .map(line => `<div>${line}</div>`)
                      .join('');
                  
                  if (logOutput.innerHTML !== newLines) {
                      logOutput.innerHTML = newLines;
                      logOutput.scrollTop = logOutput.scrollHeight;
                  }
              }

              if (statusData.progress !== undefined && statusData.progress !== lastProgress) {
                  lastProgress = statusData.progress;
                  progressBar.style.width = `${statusData.progress}%`;
                  progressText.textContent = `Progreso: ${statusData.progress}%`;
                  updateLog(`Progreso: ${statusData.progress}%`);
              }

              if (!statusData.completed) {
                  setTimeout(checkProgress, 1000);
              } else {
                  progressBar.style.width = '100%';
                  progressBar.classList.remove('progress-bar-animated');
                  progressText.textContent = 'Conversión completada';
                  convertButton.disabled = false;
                  updateLog('¡Proceso completado!');

                  if (result.sqlFile) {
                      updateLog(`Archivo generado: ${result.sqlFile}`);
                      const downloadLink = document.createElement('a');
                      downloadLink.href = `/junta2024/exports/${result.sqlFile}`;
                      downloadLink.className = 'btn btn-success mt-3';
                      downloadLink.textContent = 'Descargar SQL';
                      downloadLink.download = '';
                      logOutput.parentNode.appendChild(downloadLink);
                  }
              }
          } catch (error) {
              console.error('Error al verificar progreso:', error);
              updateLog(`Error al verificar progreso: ${error.message}`, true);
          }
      };

      checkProgress();

  } catch (error) {
      console.error('Error en la conversión:', error);
      updateLog(`Error en la conversión: ${error.message}`, true);
      progressText.textContent = `Error: ${error.message}`;
      progressDiv.classList.add('text-danger');
      convertButton.disabled = false;
  }
}