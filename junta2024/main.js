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
  W: "Blob",
};

async function browseDirectory(currentPath, inputElement) {
  try {
    const response = await fetch(
      `browse_directory.php?path=${encodeURIComponent(currentPath)}`
    );
    const data = await response.json();

    if (!data.success) {
      throw new Error(data.error);
    }

    let modal = document.getElementById("directoryModal");
    if (!modal) {
      const modalHTML = `
                <div class="modal fade" id="directoryModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Seleccionar directorio</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="current-path mb-3"></div>
                                <div class="directory-list list-group"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="selectDirectory">Seleccionar</button>
                            </div>
                        </div>
                    </div>
                </div>`;
      document.body.insertAdjacentHTML("beforeend", modalHTML);
      modal = document.getElementById("directoryModal");
    }

    const modalInstance = new bootstrap.Modal(modal);
    const currentPathElement = modal.querySelector(".current-path");
    const directoryList = modal.querySelector(".directory-list");

    currentPathElement.textContent = `Directorio actual: ${data.current}`;
    directoryList.innerHTML = "";

    data.directories.forEach((dir) => {
      const item = document.createElement("a");
      item.href = "#";
      item.className = "list-group-item list-group-item-action";
      item.textContent = dir;
      item.addEventListener("click", (e) => {
        e.preventDefault();
        browseDirectory(dir, inputElement);
      });
      directoryList.appendChild(item);
    });

    // Manejar la selección del directorio
    const selectButton = modal.querySelector("#selectDirectory");
    selectButton.onclick = async () => {
      inputElement.value = data.current;
      modalInstance.hide();

      if (inputElement.id === "dbfPath") {
        // Esperar a que se actualice la lista de tablas antes de continuar
        await refreshTableList(data.current);
      }
    };

    modalInstance.show();
  } catch (error) {
    console.error("Error al explorar directorio:", error);
    alert("Error al explorar directorio: " + error.message);
  }
}

async function refreshTableList(path) {
  try {
    const response = await fetch(
      `get_tables.php?path=${encodeURIComponent(path)}`
    );
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();

    if (!data.success) {
      throw new Error(data.error);
    }

    // Actualizar la tabla en el DOM
    const tbody = document.querySelector("table tbody");
    if (!tbody) {
      throw new Error("No se encontró la tabla");
    }

    // Limpiar la tabla actual
    tbody.innerHTML = "";

    // Agregar las nuevas filas
    for (const table of Object.values(data.tables)) {
      const row = `
                <tr data-table-name="${escapeHtml(table.name)}">
                    <td>
                        <input type="checkbox" name="tables[]" 
                               value="${escapeHtml(table.name)}"
                               class="form-check-input">
                    </td>
                    <td>${escapeHtml(table.name)}</td>
                    <td>${escapeHtml(table.size)}</td>
                    <td>${escapeHtml(table.modified)}</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm"
                                onclick="showStructure('${escapeHtml(
                                  table.name
                                )}')">
                            Ver Estructura
                        </button>
                    </td>
                </tr>
                <tr id="preview-${escapeHtml(
                  table.name
                )}" class="table-preview" style="display: none">
                    <td colspan="5">
                        <div class="loading">Cargando estructura...</div>
                    </td>
                </tr>`;
      tbody.insertAdjacentHTML("beforeend", row);
    }

    // Actualizar el resumen
    const totalTables = Object.keys(data.tables).length;
    const totalSize = Object.values(data.tables).reduce(
      (sum, table) => sum + table.rawsize,
      0
    );

    document.querySelector("[data-total-tables]").textContent = totalTables;
    document.querySelector("[data-total-size]").textContent =
      formatSize(totalSize);
    document.querySelector("[data-current-path]").textContent = path;
  } catch (error) {
    console.error("Error al actualizar lista de tablas:", error);
    alert("Error al actualizar lista de tablas: " + error.message);
  }
}

async function updateTableList(path) {
  try {
    const response = await fetch(
      `get_tables.php?path=${encodeURIComponent(path)}`
    );
    const data = await response.json();

    if (!data.success) {
      throw new Error(data.error);
    }

    // Actualizar la tabla
    const tbody = document.querySelector("table tbody");
    if (!tbody) {
      throw new Error("No se encontró el cuerpo de la tabla");
    }

    // Limpiar tabla actual
    tbody.innerHTML = "";

    // Añadir nuevas filas
    Object.values(data.tables).forEach((table) => {
      const tr = document.createElement("tr");
      tr.setAttribute("data-table-name", table.name);

      tr.innerHTML = `
                <td>
                    <input type="checkbox" name="tables[]" 
                           value="${escapeHtml(table.name)}"
                           class="form-check-input">
                </td>
                <td>${escapeHtml(table.name)}</td>
                <td>${escapeHtml(table.size)}</td>
                <td>${escapeHtml(table.modified)}</td>
                <td>
                    <button type="button" class="btn btn-info btn-sm"
                            onclick="showStructure('${escapeHtml(
                              table.name
                            )}')">
                        Ver Estructura
                    </button>
                </td>
            `;

      tbody.appendChild(tr);

      // Añadir fila de preview
      const previewTr = document.createElement("tr");
      previewTr.id = `preview-${table.name}`;
      previewTr.className = "table-preview";
      previewTr.innerHTML =
        '<td colspan="5"><div class="loading">Cargando estructura...</div></td>';
      previewTr.style.display = "none";
      tbody.appendChild(previewTr);
    });

    // Actualizar resumen
    updateSummary(data.tables);
  } catch (error) {
    alert("Error al actualizar lista de tablas: " + error.message);
  }
}

// Función auxiliar para escapar HTML
function escapeHtml(str) {
  const div = document.createElement("div");
  div.textContent = str;
  return div.innerHTML;
}

function updateSummary(tables) {
  const totalTables = Object.keys(tables).length;
  const totalSize = Object.values(tables).reduce(
    (sum, table) => sum + (table.rawsize || 0),
    0
  );

  // Actualizar contadores
  const totalTablesElement = document.querySelector(
    ".card-body .row div:nth-child(1) strong"
  );
  if (totalTablesElement) {
    totalTablesElement.nextSibling.textContent = ` ${totalTables}`;
  }

  // Actualizar tamaño total
  const totalSizeElement = document.querySelector(
    ".card-body .row div:nth-child(3) strong"
  );
  if (totalSizeElement) {
    const formattedSize = formatSize(totalSize);
    totalSizeElement.nextSibling.textContent = ` ${formattedSize}`;
  }
}

function formatSize(bytes) {
  const units = ["B", "KB", "MB", "GB"];
  let size = bytes;
  let unitIndex = 0;
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024;
    unitIndex++;
  }
  return size.toFixed(2) + " " + units[unitIndex];
}

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

  // Manejadores de exploración de directorios
  const browseDbfPath = document.getElementById("browseDbfPath");
  const browseOutputPath = document.getElementById("browseOutputPath");
  const dbfPathInput = document.getElementById("dbfPath");
  const outputPathInput = document.getElementById("outputPath");

  if (browseDbfPath) {
    browseDbfPath.addEventListener("click", () => {
      browseDirectory(dbfPathInput.value || "C:\\", dbfPathInput);
    });
  }

  if (browseOutputPath) {
    browseOutputPath.addEventListener("click", () => {
      browseDirectory(outputPathInput.value || "C:\\", outputPathInput);
    });
  }

  // Formulario de conversión
  const form = document.getElementById("convertForm");
  if (form) {
    form.addEventListener("submit", handleConversion);
  }
});

// Función para hacer fetch sin caché
async function fetchWithoutCache(url, options = {}) {
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

      // Filtrar campos válidos
      const validFields = data.fields.filter(
        (field) =>
          field.name &&
          field.name !== "<empty string>" &&
          field.name.trim() !== ""
      );

      // Crear vista
      let html = '<div class="structure-view">';
      html += validFields
        .map((field) => {
          const typeDesc = DBF_TYPES[field.type] || field.type;
          const lengthInfo =
            field.length + (field.precision ? `,${field.precision}` : "");
          return `<span class="field-item" title="Campo: ${field.name}&#10;Tipo: ${typeDesc}&#10;Longitud: ${lengthInfo}">
                  <span class="field-name">${field.name}</span>
                  <span class="field-type">(${typeDesc}</span>
                  <span class="field-length">${lengthInfo})</span>
              </span>`;
        })
        .join("");

      html += `</div><div class="text-muted small mt-1 px-2">
              <span>Total registros: ${data.recordCount.toLocaleString()}</span>
              <span class="ms-3">Total campos válidos: ${
                validFields.length
              }</span>
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
  const selectedTables = formData.getAll("tables[]");

  // Log inicial
  console.log("Tablas seleccionadas:", selectedTables);

  if (selectedTables.length === 0) {
    alert("Por favor, seleccione al menos una tabla para convertir");
    return;
  }

  // Mostrar progreso
  const progressDiv = document.getElementById("conversionProgress");
  const progressBar = document.getElementById("progressBar");
  const progressText = document.getElementById("progressText");
  const logOutput = document.getElementById("logOutput");
  const convertButton = document.getElementById("convertButton");

  // Función para actualizar el log visual
  const updateLog = (message, isError = false) => {
    const div = document.createElement("div");
    div.className = isError ? "text-danger" : "text-info";
    div.textContent = `${new Date().toLocaleTimeString()} - ${message}`;
    logOutput.appendChild(div);
    logOutput.scrollTop = logOutput.scrollHeight;
    console.log(isError ? "ERROR:" : "INFO:", message);
  };

  progressDiv.style.display = "block";
  convertButton.disabled = true;
  logOutput.innerHTML = "";
  updateLog("Iniciando proceso de conversión...");

  try {
    updateLog("Enviando petición a conver.php...");

    // Mostrar los datos que se están enviando
    const formDataObj = {};
    formData.forEach((value, key) => {
      formDataObj[key] = value;
    });
    console.log("Datos enviados:", formDataObj);

    // Usar el nombre correcto del archivo: conver.php
    const response = await fetch("/junta2024/conver.php", {
      method: "POST",
      body: formData,
      cache: "no-store",
      headers: {
        "Cache-Control": "no-cache, no-store, must-revalidate",
        Pragma: "no-cache",
        Expires: "0",
      },
    });

    if (!response.ok) {
      throw new Error(
        `Error HTTP: ${response.status} - ${response.statusText}`
      );
    }

    const result = await response.json();
    console.log("Respuesta del servidor:", result);
    updateLog("Respuesta recibida del servidor");

    if (!result.success) {
      throw new Error(result.error || "Error desconocido en la conversión");
    }

    // Monitorear progreso
    let lastProgress = 0;
    const checkProgress = async () => {
      try {
        updateLog("Verificando progreso...");

        const statusResponse = await fetch("/junta2024/check_progress.php", {
          cache: "no-store",
          headers: {
            "Cache-Control": "no-cache, no-store, must-revalidate",
            Pragma: "no-cache",
            Expires: "0",
          },
        });

        if (!statusResponse.ok) {
          throw new Error(
            `Error HTTP: ${statusResponse.status} - ${statusResponse.statusText}`
          );
        }

        const statusData = await statusResponse.json();
        console.log("Estado de progreso:", statusData);

        if (statusData.log) {
          // Actualizar solo las nuevas líneas del log
          const newLines = statusData.log
            .split("\n")
            .filter((line) => line.trim())
            .map((line) => `<div>${line}</div>`)
            .join("");

          if (logOutput.innerHTML !== newLines) {
            logOutput.innerHTML = newLines;
            logOutput.scrollTop = logOutput.scrollHeight;
          }
        }

        if (
          statusData.progress !== undefined &&
          statusData.progress !== lastProgress
        ) {
          lastProgress = statusData.progress;
          progressBar.style.width = `${statusData.progress}%`;
          progressText.textContent = `Progreso: ${statusData.progress}%`;
          updateLog(`Progreso: ${statusData.progress}%`);
        }

        if (!statusData.completed) {
          setTimeout(checkProgress, 1000);
        } else {
          progressBar.style.width = "100%";
          progressBar.classList.remove("progress-bar-animated");
          progressText.textContent = "Conversión completada";
          convertButton.disabled = false;
          updateLog("¡Proceso completado!");

          if (result.sqlFile) {
            updateLog(`Archivo generado: ${result.sqlFile}`);
            const downloadLink = document.createElement("a");
            downloadLink.href = `/junta2024/exports/${result.sqlFile}`;
            downloadLink.className = "btn btn-success mt-3";
            downloadLink.textContent = "Descargar SQL";
            downloadLink.download = "";
            logOutput.parentNode.appendChild(downloadLink);
          }
        }
      } catch (error) {
        console.error("Error al verificar progreso:", error);
        updateLog(`Error al verificar progreso: ${error.message}`, true);
      }
    };

    checkProgress();
  } catch (error) {
    console.error("Error en la conversión:", error);
    updateLog(`Error en la conversión: ${error.message}`, true);
    progressText.textContent = `Error: ${error.message}`;
    progressDiv.classList.add("text-danger");
    convertButton.disabled = false;
  }
}
