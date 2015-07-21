<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/d4w33d/CalBox>.
 */

// =============================================================================

if (defined('CALBOX')) return;

define('CALBOX', true);

// -----------------------------------------------------------------------------
// Directories

define('CALBOX_ROOT_DIR', realpath(__DIR__ . DS . '..'));
define('CALBOX_SRC_DIR', CALBOX_ROOT_DIR . DS . 'src');
define('CALBOX_VENDOR_DIR', CALBOX_SRC_DIR . DS . 'vendor');
define('CALBOX_USER_DIR', CALBOX_ROOT_DIR . DS . 'user');

// -----------------------------------------------------------------------------
// URLs

define('CALBOX_REQUEST_URL', $_SERVER['REQUEST_URI']);
define('CALBOX_BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

$protocol = 'http';
$port = (string) $_SERVER['SERVER_PORT'];

if (strpos($port, '443') === 0) {
    $protocol = 'https';
} if (in_array($port, array('80', '443'))) {
    $port = null;
}

define('CALBOX_HOST', $protocol . '://' . $_SERVER['SERVER_NAME']
    . ($port ? ':' . $port : ''));

define('CALBOX_BASE_URL_WITH_HOST', CALBOX_HOST . CALBOX_BASE_URL);

define('CALBOX_HTTP_METHOD', strtoupper($_SERVER['REQUEST_METHOD']));
define('CALBOX_IS_POST', CALBOX_HTTP_METHOD === 'POST');

define('CALBOX_HTTP_REFERER',
    array_key_exists('HTTP_REFERER', $_SERVER) ?
        $_SERVER['HTTP_REFERER'] : null);

define('CALBOX_DOMAIN_NAME', array_key_exists('SERVER_NAME', $_SERVER) ?
    strtolower($_SERVER['SERVER_NAME']) : '');

define('CALBOX_TOP_LEVEL_DOMAIN_NAME',
    preg_replace('/^(.*\.)*([^.]*\.[a-z]+)$/', '$2', CALBOX_DOMAIN_NAME));

define('CALBOX_IS_AJAX', array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)
    && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));

// -----------------------------------------------------------------------------
// Load classes

$directories = [
    CALBOX_SRC_DIR . DS . 'lib',
    CALBOX_USER_DIR . DS . 'models',
];

foreach ($directories as $dir) {
    foreach (glob($dir . DS . '*.php') as $pathname) {
        require_once $pathname;
    }
}

// Load vendors

if (!class_exists('ORM')) {
    require_once CALBOX_VENDOR_DIR
        . DS . 'j4mie'
        . DS . 'idiorm'
        . DS . 'idiorm.php';
}

if (!class_exists('PHPMailer')) {
    require_once CALBOX_VENDOR_DIR
        . DS . 'phpmailer'
        . DS . 'phpmailer'
        . DS . 'PHPMailerAutoload.php';
}
