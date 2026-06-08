// ================================================================
// SISTEMA DE PRODUTOS — app.js
// ================================================================

// ---- TOAST ----
function toast(msg, type = 'success', ms = 3500) {
    let wrap = document.querySelector('.toast-wrap');
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'toast-wrap';
        document.body.appendChild(wrap);
    }
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fa-solid ${icons[type]||icons.success}"></i><span>${msg}</span>`;
    wrap.appendChild(t);
    setTimeout(() => {
        t.style.animation = 'fadeOutRight .35s ease forwards';
        setTimeout(() => t.remove(), 350);
    }, ms);
}

// ---- MODAL EXCLUSÃO ----
function confirmarExclusao(id, nome) {
    // Remove modal anterior se existir
    const old = document.getElementById('modalExclusao');
    if (old) old.remove();

    const ov = document.createElement('div');
    ov.className = 'modal-overlay';
    ov.id = 'modalExclusao';
    ov.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:flex;align-items:center;justify-content:center;';
    ov.innerHTML = `
        <div style="background:#fff;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,.3);width:420px;max-width:95vw;padding:28px 24px;">
            <h2 style="font-size:17px;font-weight:700;margin-bottom:12px;color:#111;">Confirmar Exclusão</h2>
            <p style="font-size:13.5px;color:#555;margin-bottom:22px;line-height:1.55;">
                Tem certeza que deseja excluir <strong>${nome}</strong>?<br>Esta ação não pode ser desfeita.
            </p>
            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button onclick="fecharModal()" style="background:#fff;color:#333;border:1px solid #bbb;padding:8px 18px;font-size:13.5px;border-radius:5px;cursor:pointer;">Cancelar</button>
                <button onclick="doDelete(${id})" style="background:#c0392b;color:#fff;border:none;padding:8px 22px;font-size:13.5px;border-radius:5px;cursor:pointer;">Excluir</button>
            </div>
        </div>`;
    document.body.appendChild(ov);
    ov.addEventListener('click', e => { if (e.target === ov) fecharModal(); });
}

function fecharModal() {
    const m = document.getElementById('modalExclusao');
    if (m) m.remove();
}

function doDelete(id) {
    window.location.href = `excluir.php?id=${id}&ok=1`;
}

// ---- BUSCA em tempo real ----
document.addEventListener('DOMContentLoaded', function () {

    // Search
    const inp = document.getElementById('busca');
    if (inp) {
        inp.addEventListener('input', function () {
            const q = norm(this.value);
            let vis = 0;
            document.querySelectorAll('tbody tr.prow').forEach(tr => {
                const show = norm(tr.dataset.search || tr.textContent).includes(q);
                tr.style.display = show ? '' : 'none';
                if (show) vis++;
            });
            const empty = document.getElementById('emptyRow');
            if (empty) empty.style.display = vis === 0 ? '' : 'none';
        });
    }

    // Char counter
    const ta   = document.getElementById('descricaoProduto');
    const cnt  = document.getElementById('charCount');
    if (ta && cnt) {
        const max = parseInt(ta.getAttribute('maxlength')) || 500;
        const update = () => { cnt.textContent = `${ta.value.length}/${max}`; };
        ta.addEventListener('input', update); update();
    }

    // Auto hide flash alerts
    document.querySelectorAll('.alert-inline').forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity .5s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 500);
        }, 4500);
    });

    // File drop zone
    const drop  = document.getElementById('dropZone');
    const finp  = document.getElementById('arquivoCSV');
    const flbl  = document.getElementById('fileLabel');
    if (drop && finp) {
        drop.addEventListener('click', () => finp.click());
        drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('over'); });
        drop.addEventListener('dragleave',  ()=> drop.classList.remove('over'));
        drop.addEventListener('drop', e => {
            e.preventDefault(); drop.classList.remove('over');
            finp.files = e.dataTransfer.files;
            if (flbl && finp.files[0]) flbl.textContent = finp.files[0].name;
        });
        finp.addEventListener('change', () => {
            if (flbl && finp.files[0]) flbl.textContent = finp.files[0].name;
        });
    }

    // Select-all checkboxes (export)
    const sa = document.getElementById('selAll');
    if (sa) {
        sa.addEventListener('change', () => {
            document.querySelectorAll('.pchk').forEach(c => c.checked = sa.checked);
        });
    }
});

function norm(s) {
    return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}
