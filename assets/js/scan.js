const msg = document.getElementById('scanMessage');
function updateRow(barcode, scannedQty) {
  const row = document.querySelector(`tr[data-barcode="${barcode}"] .scanned`);
  if (row) row.textContent = scannedQty;
}
function sanitizeCode(raw) {
  if (!raw) return '';
  let s = String(raw);
  s = s.replace(/[\x00-\x1F\x7F]/g, '');
  s = s.replace(/\s+/g, '');
  return s.trim();
}
async function sendScan(code) {
  try {
    const payload = sanitizeCode(code);
    if (!payload) { msg.innerHTML = `<div class="alert alert-warning">Barcode kosong/invalid</div>`; return; }
    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ packing_id: packingId, barcode: payload })
    });
    const data = await res.json();
    if (data.ok) {
      updateRow(data.barcode, data.scanned_qty);
      msg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
    } else {
      msg.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
    }
  } catch (e) {
    msg.innerHTML = `<div class="alert alert-danger">Gagal submit</div>`;
  }
}
document.getElementById('manualSubmit').addEventListener('click', () => {
  const input = document.getElementById('manualBarcode');
  const code = sanitizeCode(input.value);
  if (code) {
    sendScan(code).finally(() => { input.value=''; input.focus(); });
  }
});
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('manualBarcode');
  input.focus();
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      const code = sanitizeCode(input.value);
      if (code) {
        e.preventDefault();
        sendScan(code).finally(() => { input.value=''; input.focus(); });
      }
    }
  });
  setupKeyboardWedge();
  setupConnectButton();
});
function startScanner() {
  if (typeof Html5Qrcode === 'undefined') {
    msg.innerHTML = `<div class="alert alert-warning">Scanner kamera tidak tersedia. Gunakan hardware scanner atau input manual.</div>`;
    return;
  }
  const html5QrCode = new Html5Qrcode('reader');
  Html5Qrcode.getCameras().then(cameras => {
    const id = cameras && cameras.length ? cameras[0].id : undefined;
    if (!id) return;
    html5QrCode.start(id, { fps: 10, qrbox: 250 }, code => {
      if (code) sendScan(code);
    }, () => {}).catch(() => {});
  }).catch(() => {});
}
startScanner();

// Keyboard wedge detection (for HID scanners acting as keyboard)
let wedgeEnabled = true;
let wedgeBuffer = '';
let wedgeLast = 0;
let wedgeTimer = null;
const CHAR_INTERVAL_MS = 50; // fast typing threshold
const FINALIZE_IDLE_MS = 120; // finalize after idle
const MIN_LEN = 4; // minimal barcode length
function setupKeyboardWedge(){
  document.addEventListener('keydown', (e) => {
    if (!wedgeEnabled) return;
    const now = Date.now();
    const fast = (now - wedgeLast) <= CHAR_INTERVAL_MS;
    wedgeLast = now;
    let ch = '';
    if (e.key === 'Enter' || e.key === 'Tab') {
      finalizeWedge();
      return;
    }
    if (e.key.length === 1 && fast) {
      ch = e.key;
      wedgeBuffer += ch;
      if (wedgeTimer) clearTimeout(wedgeTimer);
      wedgeTimer = setTimeout(finalizeWedge, FINALIZE_IDLE_MS);
    }
  }, true);
}
function finalizeWedge(){
  if (!wedgeBuffer) return;
  const code = sanitizeCode(wedgeBuffer);
  wedgeBuffer = '';
  if (code.length >= MIN_LEN) {
    sendScan(code);
  }
}
function setupConnectButton(){
  const btn = document.getElementById('connectScanner');
  if (!btn) return;
  btn.addEventListener('click', async () => {
    wedgeEnabled = true;
    msg.innerHTML = `<div class="alert alert-info">Scanner mode aktif. Silakan scan.</div>`;
    // Optional WebHID request (most scanners act as keyboard and don't need this)
    if (navigator.hid) {
      try {
        const devices = await navigator.hid.requestDevice({ filters: [] });
        if (devices && devices.length) {
          msg.innerHTML = `<div class="alert alert-success">Perangkat HID tersedia. Gunakan scanner untuk input.</div>`;
        }
      } catch (err) {
        // Ignore errors; keyboard wedge still works
      }
    }
    document.getElementById('manualBarcode').focus();
  });
}