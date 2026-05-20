const API = '/api/dictionary';
let currentPage = 1;
const PER_PAGE = 7;

const addInput = document.getElementById('addInput');
const addAccentedInput = document.getElementById('addAccentedInput');
const addBtn = document.getElementById('addBtn');
const addMsg = document.getElementById('addMsg');
const dictBody = document.getElementById('dictBody');
const dictTable = document.getElementById('dictTable');
const dictEmpty = document.getElementById('dictEmpty');
const dictLoading = document.getElementById('dictLoading');
const dictTotal = document.getElementById('dictTotal');
const pagination = document.getElementById('pagination');
const searchInput = document.getElementById('searchInput');

let searchTimer = null;
searchInput.addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => { currentPage = 1; loadWords(); }, 300);
});

async function loadWords() {
  dictLoading.classList.remove('hidden');
  dictTable.classList.add('hidden');
  dictEmpty.classList.add('hidden');

  const params = new URLSearchParams({ action: 'list', page: currentPage, per_page: PER_PAGE });
  const search = searchInput.value.trim();
  if (search) params.set('search', search);

  try {
    const res = await fetch(`${API}?${params}`);
    const data = await res.json();
    if (data.error) { showAddMsg(data.error, true); return; }

    dictTotal.textContent = `${data.total} palabra${data.total !== 1 ? 's' : ''}`;

    if (data.words.length === 0) {
      dictLoading.classList.add('hidden');
      dictEmpty.classList.remove('hidden');
      dictEmpty.textContent = search ? 'Sin resultados' : 'No hay palabras en el diccionario';
      pagination.innerHTML = '';
      return;
    }

    dictBody.innerHTML = data.words.map(w =>
      `<tr><td>${w.word_no_accent}</td><td>${w.word_accented}</td></tr>`
    ).join('');

    dictLoading.classList.add('hidden');
    dictTable.classList.remove('hidden');
    renderPagination(data.page, data.total_pages);
  } catch (e) {
    dictLoading.classList.add('hidden');
    showAddMsg('Error al cargar: ' + e.message, true);
  }
}

function renderPagination(page, totalPages) {
  if (totalPages <= 1) { pagination.innerHTML = ''; return; }

  let html = '';
  html += `<button class="page-btn" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>‹ Anterior</button>`;

  const start = Math.max(1, page - 2);
  const end = Math.min(totalPages, page + 2);
  if (start > 1) html += `<span class="page-dots">…</span>`;
  for (let i = start; i <= end; i++) {
    html += `<button class="page-btn ${i === page ? 'page-active' : ''}" data-page="${i}">${i}</button>`;
  }
  if (end < totalPages) html += `<span class="page-dots">…</span>`;

  html += `<button class="page-btn" data-page="${page + 1}" ${page >= totalPages ? 'disabled' : ''}>Siguiente ›</button>`;

  pagination.innerHTML = html;

  pagination.querySelectorAll('.page-btn:not([disabled])').forEach(btn => {
    btn.addEventListener('click', () => {
      currentPage = parseInt(btn.dataset.page);
      loadWords();
      window.scrollTo({ top: document.querySelector('.dict-table').offsetTop - 20, behavior: 'smooth' });
    });
  });
}

function showAddMsg(msg, isError = false) {
  addMsg.textContent = msg;
  addMsg.className = 'add-msg';
  if (isError) addMsg.classList.add('add-error');
  else addMsg.classList.add('add-success');
  addMsg.classList.remove('hidden');
  setTimeout(() => addMsg.classList.add('hidden'), 3000);
}

addBtn.addEventListener('click', async () => {
  const word = addInput.value.trim();
  const accented = addAccentedInput.value.trim();
  if (!word || !accented) { showAddMsg('Completa ambos campos', true); return; }

  addBtn.disabled = true;
  addBtn.textContent = 'Agregando…';

  try {
    const params = new URLSearchParams({ action: 'add', word_no_accent: word, word_accented: accented });
    const res = await fetch(`${API}?${params}`);
    const data = await res.json();
    if (data.error) { showAddMsg(data.error, true); }
    else {
      showAddMsg(`✓ "${data.word_no_accent}" → "${data.word_accented}" agregada`);
      addInput.value = '';
      addAccentedInput.value = '';
      loadWords();
    }
  } catch (e) {
    showAddMsg('Error: ' + e.message, true);
  } finally {
    addBtn.disabled = false;
    addBtn.textContent = 'Agregar';
  }
});

[addInput, addAccentedInput].forEach(el =>
  el.addEventListener('keydown', e => { if (e.key === 'Enter') addBtn.click(); })
);

loadWords();
