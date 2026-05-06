/* =============================================================
   public/js/game.js  —  logique du jeu
   Dépend de la variable ENTRY injectée par PHP dans main.php.
   ============================================================= */

'use strict';

// -------------------------------------------------------------
//  État local (sessionStorage pour survivre à un rechargement)
// -------------------------------------------------------------
const STORAGE_KEY = 'galerie_session';

function loadSession() {
  try {
    return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) || {};
  } catch { return {}; }
}

function saveSession(session) {
  try {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(session));
  } catch {}
}

// État de la partie en cours
const session = loadSession();
const dateKey = ENTRY.date;

if (!session[dateKey]) {
  session[dateKey] = { attempts: [], solved: false, abandoned: false };
}
const state = session[dateKey];

// -------------------------------------------------------------
//  Éléments DOM
// -------------------------------------------------------------
const colDebrief  = document.getElementById('col-debrief');
const bottomZone  = document.getElementById('bottom-zone');
const hintLine    = document.getElementById('hint-line');
const wordInput   = document.getElementById('word-input');
const sendBtn     = document.getElementById('send-btn');
const fbEl        = document.getElementById('fb');
const tongueBtn   = document.getElementById('tongue-btn');
const prevBtn     = document.getElementById('prev-btn');
const nextBtn     = document.getElementById('next-btn');
const centerSlot  = document.getElementById('center-slot');

// -------------------------------------------------------------
//  Rendu débrief (colonne droite)
// -------------------------------------------------------------
function renderDebrief() {
  let html = '<div class="debrief-label">essais</div>';

  for (const att of state.attempts) {
    if (att.correct) {
      html += `<div class="attempt-block">
        <div class="attempt-word hit">${esc(att.word)}</div>
        <div class="debrief-lines">
          <div class="debrief-line" style="color:var(--ok);font-size:11px;">✓</div>
        </div>
      </div>`;
    } else {
      let lines = '';
      for (const sc of att.scores) {
        if (sc.total === 0) {
          lines += `<div class="debrief-line">
            <span class="debrief-count zero">0</span>
          </div>`;
        } else {
          lines += `<div class="debrief-line">
            <span class="debrief-count">${sc.total}</span>
            <div class="dots">${renderDots(sc.green, sc.blue)}</div>
          </div>`;
        }
      }
      html += `<div class="attempt-block">
        <div class="attempt-word">${esc(att.word)}</div>
        <div class="debrief-lines">${lines}</div>
      </div>`;
    }
  }

  if (state.abandoned && !state.solved) {
    html += `<div class="nouveau-mot">nouveau mot</div>`;
  }

  colDebrief.innerHTML = html;
  colDebrief.scrollTop = colDebrief.scrollHeight;
}

function renderDots(green, blue) {
  let h = '';
  for (let i = 0; i < green; i++) h += '<span class="dot green"></span>';
  for (let i = 0; i < blue;  i++) h += '<span class="dot blue"></span>';
  return h;
}

// -------------------------------------------------------------
//  Rendu zone input + navigation
// -------------------------------------------------------------
function renderUI() {
  const over = state.solved || state.abandoned;

  // Champ de saisie
  if (over) {
    bottomZone.style.display = 'none';
  } else {
    bottomZone.style.display = '';
    wordInput.disabled = false;
    sendBtn.disabled   = false;
    wordInput.focus();
  }

  // Bouton suivant
  if (over && ENTRY.index < ENTRY.total - 1) {
    nextBtn.classList.add('visible');
  } else {
    nextBtn.classList.remove('visible');
  }

  // Slot central
  centerSlot.textContent = state.solved ? '✓ trouvé' : '';
}

// -------------------------------------------------------------
//  Soumission d'un essai
// -------------------------------------------------------------
async function submit() {
  const raw = wordInput.value.trim();
  if (!raw) return;

  // Déjà proposé (vérif locale)
  const norm = raw.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const alreadyTried = state.attempts.some(
    a => a.word.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '') === norm
  );
  if (alreadyTried) {
    showFb('déjà proposé.', 'neutral');
    wordInput.value = '';
    return;
  }

  // Désactiver pendant la requête
  wordInput.disabled = true;
  sendBtn.disabled   = true;

  try {
    const res  = await fetch('/public/api/guess.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ date: ENTRY.date, mot: raw }),
    });
    const data = await res.json();

    state.attempts.push({
      word:    raw,
      correct: data.correct,
      scores:  data.scores,   // [{green, blue, total}, ...]
    });
    if (data.correct) state.solved = true;
    saveSession(session);

    wordInput.value = '';
    renderDebrief();
    renderUI();

    if (!data.correct) showFb('Pas tout à fait.', 'err');

  } catch (err) {
    console.error(err);
    showFb('Erreur réseau, réessayez.', 'err');
    wordInput.disabled = false;
    sendBtn.disabled   = false;
  }
}

// -------------------------------------------------------------
//  Abandon (langue au chat)
// -------------------------------------------------------------
function abandon() {
  state.abandoned = true;
  saveSession(session);
  renderDebrief();
  renderUI();
}

// -------------------------------------------------------------
//  Navigation entre dessins
// -------------------------------------------------------------
function goTo(index) {
  const url = new URL(window.location.href);
  url.searchParams.set('index', index);
  window.location.href = url.toString();
}

// -------------------------------------------------------------
//  Feedback visuel
// -------------------------------------------------------------
function showFb(msg, type) {
  fbEl.textContent  = msg;
  fbEl.className    = 'fb ' + type;
}

// -------------------------------------------------------------
//  Utilitaire : échapper le HTML
// -------------------------------------------------------------
function esc(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

// -------------------------------------------------------------
//  Événements
// -------------------------------------------------------------
sendBtn.addEventListener('click', submit);
wordInput.addEventListener('keydown', e => { if (e.key === 'Enter') submit(); });
tongueBtn.addEventListener('click', abandon);

prevBtn.addEventListener('click', () => {
  if (ENTRY.index > 0) goTo(ENTRY.index - 1);
});
nextBtn.addEventListener('click', () => {
  if (ENTRY.index < ENTRY.total - 1) goTo(ENTRY.index + 1);
});

// -------------------------------------------------------------
//  Init
// -------------------------------------------------------------
renderDebrief();
renderUI();
