document.addEventListener('DOMContentLoaded', function() {
    const desc = document.getElementById('descricao');
    const counter = document.getElementById('charCounter');
    const MAX = 500;
    function atualizar() {
        let len = desc.value.length;
        counter.textContent = `Maximo ${MAX} caracteres (${len}/${MAX})`;
        if (len > MAX) counter.classList.add('text-danger');
        else counter.classList.remove('text-danger');
    }
    if (desc) {
        desc.addEventListener('input', atualizar);
        atualizar();
    }
});

function limparFormulario() {
    const form = document.getElementById('produtoForm');
    form.reset();
    document.getElementById('codigo').value = '';
    document.getElementById('nome').value = '';
    document.getElementById('preco').value = '';
    document.getElementById('estoque').value = '';
    document.getElementById('descricao').value = '';
    document.getElementById('charCounter').textContent = 'Maximo 500 caracteres (0/500)';
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    document.querySelectorAll('.error-field').forEach(el => el.classList.remove('error-field'));
}