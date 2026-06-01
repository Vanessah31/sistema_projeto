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
    ov.innerHTML = `
        <div class="modal">
            <h2>Confirmar Exclusão</h2>
            <p>Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.</p>
            <div class="modal-footer">
                <button class="btn btn-modal-cancel" onclick="fecharModal()">Cancelar</button>
                <button class="btn btn-confirmar-excluir" onclick="doDelete(${id})">Excluir</button>
            </div>
        </div>`;
    document.body.appendChild(ov);

    // Fechar ao clicar fora
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
