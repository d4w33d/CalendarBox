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

defined($k = 'DS') || define($k, DIRECTORY_SEPARATOR);

// -----------------------------------------------------------------------------

use CalBox\Models\Event;
use CalBox\Models\Registration;

// =============================================================================

class CalBox
{

    // =========================================================================

    private static $config;

    // -------------------------------------------------------------------------

    /**
     * CalBox object initialization
     * @return null
     */
    public static function initialize()
    {
        // Check if CalBox was already initialized
        if (self::$config !== null) {
            return;
        }

        // Load bootstrap script
        require_once __DIR__ . DS . 'src' . DS . 'bootstrap.php';

        // Do nothing if CalBox is not installed
        if (!self::isInstalled()) {
            return;
        }

        // Load configuration
        self::$config = require self::getConfigFile();

        // Initialize database
        self::initializeDatabase(self::cfg('database'));
    }

    public static function isInstalled()
    {
        return is_file(self::getConfigFile());
    }

    public static function getConfigFile()
    {
        return CALBOX_ROOT_DIR . DS . 'user' . DS . 'config.php';
    }

    public static function getDatabaseDsn(array $cfg = null)
    {
        if ($cfg === null) {
            $cfg = self::cfg('database');
        }

        if ($cfg['dbms'] === 'sqlite') {
            return 'sqlite:' . $cfg['filepath'];
        } else if (in_array($cfg['dbms'], ['mysql', 'pgsql'])) {
            return $cfg['dbms']
                . ':host=' . $cfg['host']
                . ';port=' . $cfg['port']
                . ';dbname=' . $cfg['dbname'];
        }
    }

    public static function initializeDatabase(array $cfg = null)
    {
        if ($cfg === null) {
            $cfg = self::cfg('database');
        }

        ORM::configure(self::getDatabaseDsn($cfg));

        if ($cfg['username'] !== null)
            ORM::configure('username', $cfg['username']);
        if ($cfg['password'] !== null)
            ORM::configure('password', $cfg['password']);
    }

    public static function executeDatabaseAction($action)
    {
        if ($action === 'reinstall') {
            self::executeDatabaseAction('uninstall');
            self::executeDatabaseAction('install');
            return;
        }

        $db = ORM::get_db();

        $models = [
            'event' => new Event(),
            'registration' => new Registration(),
        ];

        $tables = $models;
        array_walk($tables, function(&$value, $key)
        {
            $value = $value->getTableName();
        });

        $cmd = require CALBOX_SRC_DIR . DS . 'sql' . DS . $action . '.php';

        foreach ($cmd[self::cfg('database.dbms')]() as $sql) {
            $db->exec($sql);
        }
    }

    /**
     * Get configuration value
     * @param string $key Dotted chain of configuration keys
     * @param mixed $default Default value if entry doesn't exists
     * @return mixed
     */
    public static function cfg($key, $default = null)
    {
        $value = self::$config;
        foreach (explode('.', $key) as $col) {
            if (!is_array($value) || !array_key_exists($col, $value)) {
                return $default;
            }

            $value = $value[$col];
        }

        return $value;
    }

    /**
     * Redirect to given URL and exit script
     * @param string $url
     * @param integer $code HTTP code of redirection
     * @return null
     */
    public static function redirect($url, $code = 302)
    {
        if (!$url || $url{0} !== '/') {
            $url = CALBOX_BASE_URL . '/' . $url;
        }

        header('Location: ' . $url, true, $code);
        exit;
    }

    /**
     * Returns a formatted URL from route name
     * @param string $name Route name
     * @param array $vars Array of vars to pass as query string
     * @param boolean $withHost Returns current hostname with scheme in URL
     * @return string
     */
    public static function makeUrl($name, array $vars = array(), $withHost = false)
    {
        $routes = self::cfg('routes');
        if (!array_key_exists($name, $routes)) {
            return;
        }

        $url = $routes[$name];

        if (!$url || $url{0} !== '/') {
            $url = CALBOX_BASE_URL . '/' . $url;
        }

        if ($query = http_build_query($vars)) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . $query;
        }

        if ($withHost) {
            $url = CALBOX_HOST . $url;
        }

        return $url;
    }

    /**
     * Returns the URL to homepage as given in the configuration
     * @param array $vars Array of vars to pass as query string
     * @return string
     */
    public static function homepageUrl(array $vars = array())
    {
        $url = self::cfg('behaviour.homepageUrl');

        if (strpos($url, '://') === false) {
            if (!$url || $url{0} !== '/') {
                $url = CALBOX_BASE_URL . '/' . $url;
            }

            if (preg_match('/\/\.\.?$/', $url)) {
                $url .= '/';
            }

            $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
            for($n=1; $n>0; $url=preg_replace($re, '/', $url, -1, $n)) {}

            $url = CALBOX_HOST . $url;
        }

        if ($query = http_build_query($vars)) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . $query;
        }

        return $url;
    }

}

// =============================================================================

CalBox::initialize();
