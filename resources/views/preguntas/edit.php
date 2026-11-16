<?php $title = 'Editar Pregunta'; ob_start(); ?>


<div class="bg-[#0B1E28] border border-[#00C8FF] rounded-lg shadow-lg p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold text-[#FFD33D] mb-6">Editar Pregunta</h2>

    <form action="<?= e(route('preguntas.update', $pregunta->id)) ?>" method="POST">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="_method" value="PUT">
        
        <div class="mb-4">
            <label for="pais" class="block text-[#A8C3C7] font-medium mb-2">País *</label>
            <input type="text" name="pais" id="pais" 
                   class="w-full px-3 py-2 bg-[#07212C] border border-[#00C8FF] rounded-md text-[#A8C3C7] focus:outline-none focus:ring-2 focus:ring-[#FFD33D] placeholder-[#A8C3C7]"
                   value="<?= e(old('pais', $pregunta->pais)) ?>" required>
            <?php if (isset($errors['pais'])): $message = $errors['pais']; ?>
                <p class="text-[#FF4040] text-xs mt-1"><?= e($message) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label class="block text-[#A8C3C7] font-medium mb-2">Categorías *</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                <?php foreach (['CRI', 'PE', 'CSIS', 'AMACSS', 'DAE'] as $categoria): ?>
                <label class="flex items-center p-2 bg-[#07212C] border border-[#00C8FF] rounded hover:bg-[#0B1E28] transition-colors">
                    <input type="checkbox" name="categorias[]" value="<?= e($categoria) ?>" 
                           class="rounded border-[#00C8FF] text-[#FFD33D] focus:ring-[#FFD33D]"
                           <?= e(in_array($categoria, old('categorias', array_column($pregunta->categoriasPivot, 'categoria'))) ? 'checked' : '') ?>>
                    <span class="ml-2 text-[#A8C3C7]"><?= e($categoria) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <?php if (isset($errors['categorias'])): $message = $errors['categorias']; ?>
                <p class="text-[#FF4040] text-xs mt-1"><?= e($message) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="enunciado" class="block text-[#A8C3C7] font-medium mb-2">Enunciado *</label>
            <textarea name="enunciado" id="enunciado" rows="4"
                      class="w-full px-3 py-2 bg-[#07212C] border border-[#00C8FF] rounded-md text-[#A8C3C7] focus:outline-none focus:ring-2 focus:ring-[#FFD33D] placeholder-[#A8C3C7]"
                      required><?= e(old('enunciado', $pregunta->enunciado)) ?></textarea>
            <?php if (isset($errors['enunciado'])): $message = $errors['enunciado']; ?>
                <p class="text-[#FF4040] text-xs mt-1"><?= e($message) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-6">
            <label for="respuesta" class="block text-[#A8C3C7] font-medium mb-2">Respuesta Correcta *</label>
            <input type="text" name="respuesta" id="respuesta" 
                   class="w-full px-3 py-2 bg-[#07212C] border border-[#00C8FF] rounded-md text-[#A8C3C7] focus:outline-none focus:ring-2 focus:ring-[#FFD33D] placeholder-[#A8C3C7]"
                   value="<?= e(old('respuesta', $pregunta->respuesta)) ?>" required>
            <?php if (isset($errors['respuesta'])): $message = $errors['respuesta']; ?>
                <p class="text-[#FF4040] text-xs mt-1"><?= e($message) ?></p>
            <?php endif; ?>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="<?= e(route('preguntas.index')) ?>" 
               class="bg-[#07212C] hover:bg-[#0B1E28] text-[#A8C3C7] hover:text-[#FFD33D] border border-[#00C8FF] px-4 py-2 rounded transition-colors font-medium">
                Cancelar
            </a>
            <button type="submit" 
                    class="bg-[#FFF04B] hover:bg-[#FFD33D] text-[#0B1E28] px-4 py-2 rounded font-medium transition-colors shadow-md">
                Actualizar Pregunta
            </button>
        </div>
    </form>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>