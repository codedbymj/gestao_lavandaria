<section class="page-heading">
    <div>
        <p class="eyebrow">Clientes</p>
        <h1>Novo cliente</h1>
        <p>Preencha os dados necessários para o cadastro.</p>
    </div>
</section>

<?php
$formAction = BASE_URL . '/clientes/novo';
$submitLabel = 'Cadastrar cliente';
require __DIR__ . '/_form.php';
?>