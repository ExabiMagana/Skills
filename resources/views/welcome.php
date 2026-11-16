<?php $title='Inicio'; ob_start(); ?>
<div class="bg-[#07212C] border border-[#00C8FF] rounded-lg p-8 text-center">
    <h1 class="text-3xl font-bold text-[#FFD33D] mb-4">ITCA SKILLS 2025</h1>
    <p class="mb-8">Practica tus conocimientos con preguntas por país y categoría.</p>
    <div class="flex justify-center gap-4">
        <a class="bg-[#FFF04B] text-[#0B1E28] px-6 py-3 rounded font-bold" href="<?= e(route('juego.index')) ?>">Comenzar juego</a>
        <a class="border border-[#00C8FF] px-6 py-3 rounded" href="<?= e(route('preguntas.index')) ?>">Administrar preguntas</a>
    </div>
</div>
<?php $content=ob_get_clean(); require __DIR__.'/layouts/app.php'; ?>
