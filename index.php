<?php
require __DIR__ . '/bootstrap/app.php';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$path = $_GET['route'] ?? substr($uri, strlen($base));
$path = '/' . trim($path, '/');
if ($path === '/index.php') $path = '/';
$isJson = str_starts_with($path, '/juego/');
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = $_POST;
    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        $input = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($input)) json_response(['message'=>'Solicitud inválida.'], 400);
    }
    if ($method === 'POST') {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['_token'] ?? '';
        if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
            if ($isJson) json_response(['message'=>'La sesión expiró. Recarga la página.'], 419);
            http_response_code(419); exit('La sesión expiró. Recarga el formulario.');
        }
        $method = strtoupper(is_string($input['_method'] ?? null) ? $input['_method'] : 'POST');
    }
    if ($method === 'GET' && $path === '/') { view('welcome'); exit; }
    if ($method === 'GET' && $path === '/juego') { view('juego.index'); exit; }
    if ($method === 'GET' && $path === '/preguntas') { view('preguntas.index', ['preguntas'=>array_map('question_object', questions())]); exit; }
    if ($method === 'GET' && $path === '/preguntas/create') { view('preguntas.create'); exit; }
    if (preg_match('~^/preguntas/(\d+)(/edit)?$~', $path, $match)) {
        $id = (int)$match[1]; $row = question($id);
        if ($method === 'GET') { view(isset($match[2]) ? 'preguntas.edit' : 'preguntas.show', ['pregunta'=>question_object($row)]); exit; }
        if ($method === 'DELETE' && !isset($match[2])) {
            $pdo = db(); $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM categoria_pregunta WHERE pregunta_id=?')->execute([$id]);
            $pdo->prepare('DELETE FROM preguntas WHERE id=?')->execute([$id]); $pdo->commit();
            $_SESSION['success']='Pregunta eliminada exitosamente.'; redirect('preguntas.index');
        }
    }
    if (($method === 'POST' && $path === '/preguntas') || ($method === 'PUT' && preg_match('~^/preguntas/(\d+)$~', $path, $match))) {
        $id = $method === 'PUT' ? (int)$match[1] : null;
        if ($id) question($id);
        $errors=[];
        foreach (['pais'=>'País','enunciado'=>'Enunciado','respuesta'=>'Respuesta'] as $key=>$label) {
            if (!isset($input[$key]) || !is_string($input[$key]) || trim($input[$key]) === '') $errors[$key]="$label es obligatorio.";
            elseif ($key !== 'enunciado' && mb_strlen($input[$key]) > 255) $errors[$key]="$label no puede superar 255 caracteres.";
        }
        if (!valid_categories($input['categorias'] ?? null)) $errors['categorias']='Selecciona al menos una categoría válida.';
        if ($errors) {
            $_SESSION['errors']=$errors; $_SESSION['old']=$input;
            header('Location: ' . ($id ? route('preguntas.edit', $id) : route('preguntas.create')), true, 303); exit;
        }
        $pdo=db(); $pdo->beginTransaction();
        $values=[$input['pais'],$input['enunciado'],$input['respuesta']];
        if ($id) { $pdo->prepare('UPDATE preguntas SET pais=?,enunciado=?,respuesta=?,updated_at=CURRENT_TIMESTAMP WHERE id=?')->execute([...$values,$id]); $pdo->prepare('DELETE FROM categoria_pregunta WHERE pregunta_id=?')->execute([$id]); }
        else { $pdo->prepare('INSERT INTO preguntas (pais,enunciado,respuesta,created_at,updated_at) VALUES (?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)')->execute($values); $id=(int)$pdo->lastInsertId(); }
        $stmt=$pdo->prepare('INSERT INTO categoria_pregunta (pregunta_id,categoria) VALUES (?,?)');
        foreach (array_unique($input['categorias']) as $category) $stmt->execute([$id,$category]);
        $pdo->commit(); $_SESSION['success']=$method === 'PUT' ? 'Pregunta actualizada exitosamente.' : 'Pregunta creada exitosamente.'; redirect('preguntas.index');
    }
    if ($method === 'POST' && $path === '/juego/obtener-preguntas') {
        $count=filter_var($input['cantidad'] ?? null, FILTER_VALIDATE_INT);
        if (!$count || $count<1 || $count>50 || !valid_categories($input['categorias'] ?? null)) json_response(['message'=>'Selecciona entre 1 y 50 preguntas y categorías válidas.'],422);
        $rows=questions(array_values(array_unique($input['categorias'])), $count);
        $_SESSION['quiz']=[];
        foreach ($rows as &$row) { $_SESSION['quiz'][$row['id']]=['attempts'=>0,'answered'=>false]; unset($row['respuesta']); }
        json_response($rows);
    }
    if ($method === 'POST' && $path === '/juego/verificar-respuesta') {
        $id=filter_var($input['pregunta_id'] ?? null,FILTER_VALIDATE_INT);
        if (!$id || !isset($_SESSION['quiz'][$id]) || !is_string($input['respuesta_usuario'] ?? null) || trim($input['respuesta_usuario']) === '' || !filter_var($input['intentos'] ?? null,FILTER_VALIDATE_INT) || $input['intentos']<1) json_response(['message'=>'Respuesta o pregunta inválida.'],422);
        $state=&$_SESSION['quiz'][$id];
        if ($state['answered']) json_response(['message'=>'Esta pregunta ya fue respondida.'],409);
        $row=question($id); $state['attempts']++;
        $correct=mb_strtolower(trim($row['respuesta']),'UTF-8') === mb_strtolower(trim($input['respuesta_usuario']),'UTF-8');
        $state['answered']=true;
        json_response(['es_correcta'=>$correct,'respuesta_correcta'=>$row['respuesta'],'puntuacion_obtenida'=>$correct ? (int)max(100 / pow(2,$state['attempts']-1),10) : 0]);
    }
    http_response_code(404);
    if ($isJson) json_response(['message'=>'Ruta no encontrada.'],404);
    echo '<h1>Página no encontrada</h1><a href="'.e(route('home')).'">Volver al inicio</a>';
} catch (JsonException $error) {
    json_response(['message'=>'JSON inválido.'],400);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log((string)$error);
    $status=$error->getCode() === 404 ? 404 : 500;
    $message=$status === 404 ? 'Pregunta no encontrada.' : 'No se pudo completar la operación. Comprueba MySQL, las credenciales en config/database.php o .env y la importación de INDICACIONES/quiz_game.sql.';
    if ($isJson) json_response(['message'=>$message],$status);
    http_response_code($status); echo '<meta charset="utf-8"><h1>'.e($message).'</h1><a href="'.e(route('home')).'">Volver al inicio</a>';
}
