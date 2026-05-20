const API_BASE = '';

const nombresInput = document.getElementById('nombresInput');
const apellidosInput = document.getElementById('apellidosInput');
const ordenSelect = document.getElementById('ordenSelect');
const formatoSelect = document.getElementById('formatoSelect');
const correctBtn = document.getElementById('correctBtn');
const resultDiv = document.getElementById('result');
const errorDiv = document.getElementById('error');
const loadingDiv = document.getElementById('loading');
const originalDisplay = document.getElementById('originalDisplay');
const correctedDisplay = document.getElementById('correctedDisplay');
const changesDiv = document.getElementById('changes');

function getParams() {
  const params = new URLSearchParams();
  const nombres = nombresInput.value.trim();
  const apellidos = apellidosInput.value.trim();
  if (!nombres && !apellidos) return null;
  if (nombres) params.set('nombres', nombres);
  if (apellidos) params.set('apellidos', apellidos);
  params.set('orden', ordenSelect.value);
  params.set('formato', formatoSelect.value);
  return params;
}

async function correctName() {
  const params = getParams();
  if (!params) {
    showError('Ingresa al menos nombre(s) o apellidos');
    return;
  }

  correctBtn.disabled = true;
  correctBtn.textContent = 'Corrigiendo…';
  hideError();
  loadingDiv.classList.remove('hidden');
  resultDiv.classList.add('hidden');

  try {
    const res = await fetch(`${API_BASE}/api/correct?${params}`);
    if (!res.ok) throw new Error('HTTP ' + res.status);

    const data = await res.json();
    if (data.error) { showError(data.error); return; }

    resultDiv.classList.remove('hidden');
    originalDisplay.textContent = data.original;
    correctedDisplay.textContent = data.corrected;

    if (data.changes && data.changes.length > 0) {
      let html = '<div class="changes-list">';
      data.changes.forEach(c => {
        html += `<span class="change-chip"><span class="change-from">${c.from}</span><span class="change-arrow-chip">→</span><span class="change-to">${c.to}</span></span>`;
      });
      html += '</div>';
      changesDiv.innerHTML = html;
    } else {
      changesDiv.innerHTML = '<span class="change-none">✓ Sin cambios necesarios</span>';
    }
  } catch (e) {
    showError('Error de conexión: ' + e.message);
  } finally {
    correctBtn.disabled = false;
    correctBtn.textContent = 'Corregir';
    loadingDiv.classList.add('hidden');
  }
}

function showError(msg) {
  errorDiv.textContent = msg;
  errorDiv.classList.remove('hidden');
  resultDiv.classList.add('hidden');
}

function hideError() {
  errorDiv.classList.add('hidden');
}

correctBtn.addEventListener('click', correctName);
[nombresInput, apellidosInput].forEach(el =>
  el.addEventListener('keydown', e => { if (e.key === 'Enter') correctName(); })
);

document.querySelectorAll('.test-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    nombresInput.value = btn.dataset.nombres;
    apellidosInput.value = btn.dataset.apellidos;
    correctName();
  });
});