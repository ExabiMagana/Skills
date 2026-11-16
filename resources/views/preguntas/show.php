<?php $title='Ver pregunta'; ob_start(); ?>
<article class="bg-[#07212C] border border-[#00C8FF] rounded-lg p-6">
    <h2 class="text-2xl text-[#FFD33D] mb-4"><?= e($pregunta->pais) ?></h2>
    <p class="mb-4"><?= e(implode(', ', array_column($pregunta->categoriasPivot, 'categoria'))) ?></p>
    <p class="mb-4"><?= e($pregunta->enunciado) ?></p>
    <p class="text-[#4CAF50] mb-4">Respuesta: <?= e($pregunta->respuesta) ?></p>
    <a href="<?= e(route('preguntas.edit', $pregunta->id)) ?>">Editar</a> · <a href="<?= e(route('preguntas.index')) ?>">Volver</a>
</article>
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>
