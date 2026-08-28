<section class="page-heading">
    <div>
        <p class="eyebrow">Clientes</p>
        <h1>Editar cliente</h1>
        <p>Atualize os dados de <?= e($client['nome']) ?>.</p>
    </div>
</section>

<?php
$formAction = BASE_URL . '/clientes/editar';
$submitLabel = 'Guardar alterações';
require __DIR__ . '/_form.php';
?>