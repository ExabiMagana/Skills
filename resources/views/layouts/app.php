<?php /* Plantilla compartida de PHP puro. */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITCA SKILLS 2025 - <?= e($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0B1E28;color:#A8C3C7;font-family:Arial,sans-serif;margin:0}nav{padding:1rem;background:#07212C;border-bottom:1px solid #00C8FF}main{max-width:1200px;margin:24px auto;padding:16px}a{color:#00C8FF}input,textarea,select{max-width:100%}.hidden{display:none}table{width:100%}</style>
</head>
<body class="bg-[#0B1E28]">
<nav class="bg-[#07212C] border-b border-[#00C8FF] p-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <a href="<?= e(route('home')) ?>" class="text-xl font-bold text-[#FFD33D]">ITCA SKILLS 2025</a>
        <div class="space-x-4">
            <a href="<?= e(route('preguntas.index')) ?>" class="text-[#A8C3C7] hover:text-[#FFD33D] font-medium">Preguntas</a>
            <a href="<?= e(route('juego.index')) ?>" class="text-[#A8C3C7] hover:text-[#FFD33D] font-medium">Jugar</a>
        </div>
    </div>
</nav>
<main class="container mx-auto mt-6 p-4">
    <?php if ($success): ?><div role="status" class="border border-[#4CAF50] text-[#4CAF50] px-4 py-3 rounded mb-4"><?= e($success) ?></div><?php endif; ?>
    <?= $content ?>
</main>
</body>
</html>
