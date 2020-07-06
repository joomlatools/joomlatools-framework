<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */


class JoomlatoolsTemporaryDispatcher extends JEventDispatcher
{
    /**
     * Get rid of registered Logman plugins and disable it permanently afterwards if it's version 1 or 2
     */
    public static function disableLogman()
    {
        $dispatcher = JEventDispatcher::getInstance();

        foreach ($dispatcher->_observers as $key => $observer)
        {
            if (is_object($observer)
                && (substr(get_class($observer), 0, 9) === 'PlgLogman' || get_class($observer) === 'PlgSystemKoowa')) {
                $dispatcher->detach($observer);
            }
        }

        $logman_manifest = JPATH_ADMINISTRATOR.'/components/com_logman/logman.xml';
        if (file_exists($logman_manifest))
        {
            $manifest = simplexml_load_file($logman_manifest);

            if ($manifest && $manifest->version)
            {
                $version = (string)$manifest->version;

                if ($version && version_compare($version, '3', '<'))
                {
                    $db = JFactory::getDbo();

                    $query = "UPDATE #__extensions SET enabled = 0 WHERE type='plugin' AND folder='koowa' AND element='logman'";
                    $db->setQuery($query)->query();

                    $query = "UPDATE #__extensions SET enabled = 0 WHERE type='plugin' AND folder='system' AND element='logman'";
                    $db->setQuery($query)->query();

                    $query = "UPDATE #__modules SET published = 0 WHERE module='mod_logman'";
                    $db->setQuery($query)->query();
                }
            }
        }
    }
}

class PlgSystemJoomlatoolsInstallerScript
{
    public function __construct($installer)
    {
        JoomlatoolsTemporaryDispatcher::disableLogman();
    }

    public function preflight($type, $installer)
    {
        if (defined('JOOMLATOOLS_PLATFORM')) {
            return;
        }

        if ($errors = $this->getServerErrors())
        {
            ob_start();
            echo JText::_("The installation cannot proceed until you resolve the following issues: ");
            echo implode(',', $errors);

            $error = ob_get_clean();
            JFactory::getApplication()->enqueueMessage($error, 'error');

            return false;
        }

        if (!$this->_uninstallExtman()) {
            JFactory::getApplication()->enqueueMessage(JText::_('Could not automatically uninstall EXTman.
            Please go to Extension Manager and remove EXTman first in order to upgrade to the latest version'), 'error');

            return false;
        }

        return true;
    }

    protected function _uninstallExtman()
    {
        $result = true;
        $db     = \JFactory::getDbo();
        $query  = /** @lang text */"SELECT extension_id FROM #__extensions
            WHERE type = 'component' AND element = 'com_extman'
            LIMIT 1
        ";

        $extension_id = $db->setQuery($query)->loadResult();

        if ($extension_id) {
            // Make extensions uninstallable by Joomla extension manager
            $query = /** @lang text */'UPDATE #__extensions SET protected = 0
              WHERE extension_id IN (SELECT joomla_extension_id FROM #__extman_extensions)';
            \JFactory::getDbo()->setQuery($query)->query();

            // First we remove the extension list so Extman does not give an error
            $query = /** @lang text */'CREATE TABLE IF NOT EXISTS #__extman_extensions_bkp AS SELECT * FROM #__extman_extensions;';
            $db->setQuery($query)->query();
            $query = /** @lang text */'TRUNCATE TABLE #__extman_extensions;';
            $db->setQuery($query)->query();

            // Temporary fix to avoid errors on uninstall
            $query = /** @lang text */"UPDATE #__extensions SET element = 'files_koowa' WHERE element = 'koowa' AND type = 'file';";
            $db->setQuery($query)->query();

            $installer = new \JInstaller();
            $result = $installer->uninstall('component', $extension_id, 1);

            if ($result) {
                // Delete old Koowa folder
                if (is_dir(JPATH_LIBRARIES.'/koowa')) {
                    JFolder::delete(JPATH_LIBRARIES.'/koowa');
                }
            }
        }

        return $result;
    }

    public function postflight($type, $installer)
    {
        $newer_version_installed = false;

        if (class_exists('Koowa') && method_exists('Koowa', 'getInstance')) {
            $current_version = Koowa::getInstance()->getVersion();
            $payload_version = null;
            $payload_koowa   = $installer->getParent()->getPath('source').'/libraries/joomlatools/library/koowa.php';

            if (is_file($payload_koowa) && is_readable($payload_koowa)
                && preg_match("#const\s+VERSION\s+=\s+'(.*?)'#i", file_get_contents($payload_koowa), $matches)
            ) {
                $payload_version = $matches[1];

                if (version_compare($payload_version, $current_version, '<')) {
                    $newer_version_installed = true;
                }
            }
        }

        if ($type !== 'discover_install' && !$newer_version_installed)
        {
            $source = $installer->getParent()->getPath('source');

            if (!$this->_moveFolder($source.'/libraries/joomlatools', JPATH_LIBRARIES.'/joomlatools')) {
                $warning = 'Could not create the libraries folder';
                JFactory::getApplication()->enqueueMessage($warning, 'warning');

                return false;
            }

            $components_source = $source.'/libraries/joomlatools-components';
            $components_target = JPATH_LIBRARIES.'/joomlatools-components';

            if (!JFolder::exists($components_target)) {
                JFolder::create($components_target);
            }

            if (JFolder::exists($components_source)) {
                // Move reusable components
                $components = JFolder::folders($components_source);

                foreach ($components as $component)
                {
                    $from = $components_source.'/'.$component;
                    $to   = $components_target.'/'.$component;

                    if (is_link($to)) {
                        continue;
                    }

                    $this->_moveFolder($from, $to);
                }
            }

            // Remove reusable components from the joomlatools folder as they have their own folder now
            if ($type === 'update')
            {
                $components = ['activites', 'ckeditor', 'files', 'migrator', 'scheduler', 'tags'];
                foreach ($components as $component) {
                    $component_path = JPATH_LIBRARIES.'/joomlatools/component/'.$component;

                    if (is_dir($component_path)) {
                        JFolder::delete($component_path);
                    }
                }
            }

            // Create media folder
            $media = JPATH_ROOT.'/media/koowa';

            if (!JFolder::exists($media)) {
                JFolder::create($media);
            }

            $assets = JPATH_LIBRARIES.'/joomlatools/library/resources/assets';
            $target = $media.'/framework';

            if (!is_link($target)) {
                $this->_moveFolder($assets, $target);
            }

            // Move com_koowa assets
            $results = glob(JPATH_LIBRARIES . '/joomlatools/component/*/resources/assets', GLOB_ONLYDIR);

            foreach ($results as $result)
            {
                $component = preg_replace('#^.*?component/([^/]+)/resources/assets#', '$1', $result);
                $target    = $media.'/com_'.$component;

                if (!$component || is_link($target)) {
                    continue;
                }

                $this->_moveFolder($result, $target);
            }

            // Move component assets
            $results = glob($components_target.'/*/resources/assets', GLOB_ONLYDIR);

            foreach ($results as $result)
            {
                $component = preg_replace('#^.*?joomlatools-components/([^/]+)/resources/assets#', '$1', $result);
                $target    = $media.'/com_'.$component;

                if (!$component || is_link($target)) {
                    continue;
                }

                $this->_moveFolder($result, $target);
            }
        }

        if (!$newer_version_installed) {
            $this->_runQueries();
        }

        $this->_clearCache();

        // Enable plugin
        $query = sprintf("UPDATE #__extensions SET enabled = 1 WHERE type = '%s' AND element = '%s' AND folder = '%s'",
            'plugin', 'joomlatools', 'system'
        );

        JFactory::getDbo()->setQuery($query)->query();

        $this->bootFramework();

        return true;
    }

    protected function _clearCache()
    {
        // Joomla does not clean up its plugins cache for us
        JCache::getInstance('callback', array(
            'defaultgroup' => 'com_plugins',
            'cachebase'    => JPATH_ADMINISTRATOR . '/cache'
        ))->clean();

        JFactory::getCache('com_koowa.tables', 'output')->clean();
        JFactory::getCache('com_koowa.templates', 'output')->clean();

        // Clear APC opcode cache
        if (extension_loaded('apc'))
        {
            apc_clear_cache();
            apc_clear_cache('user');
        }

        // Clear OPcache
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }

    protected function _moveFolder($from, $to)
    {
        $temp   = $to.'_tmp';
        $result = false;

        if (JFolder::exists($temp)) {
            if (!JFolder::delete($temp) || JFolder::exists($temp)) {
                return $result;
            }
        }

        if (JFolder::copy($from, $temp))
        {
            if (JFolder::exists($to)) {
                if (!JFolder::delete($to) || JFolder::exists($to)) {
                    return $result;
                }
            }

            $result = JFolder::move($temp, $to);
        }

        return $result;
    }

    public function uninstall($installer)
    {
        $folders = array(
            JPATH_LIBRARIES.'/joomlatools',
            JPATH_ROOT.'/media/koowa'
        );

        foreach ($folders as $folder) {
            if (JFolder::exists($folder)) {
                JFolder::delete($folder);
            }
        }
    }

    protected function _runQueries()
    {
        $results = glob(JPATH_LIBRARIES . '/joomlatools-components/*/resources/install/install.sql');
        $queries = array();

        $db = JFactory::getDbo();

        foreach ($results as $result) {
            if ($q = $db->splitSql(file_get_contents($result))) {
                $queries = array_merge($queries, $q);
            }
        }

        foreach ($queries as $query) {
            $query = trim($query);

            if ($query != '' && $query[0] != '#') {
                try {
                    $db->setQuery($query)->execute();
                } catch (Exception $e) {
                }
            }
        }
    }

    public function getServerErrors()
    {
        $errors = array();

        if(version_compare(JVERSION, '3.5', '<'))
        {
            $errors[] = sprintf(JText::_('Your site is running Joomla %s which is an unsupported version.
            Please upgrade Joomla to the latest version first.'), JVERSION);
        }

        if(version_compare(phpversion(), '5.4', '<'))
        {
            $errors[] = sprintf(JText::_('Your server is running PHP %s which is an old and insecure version.
            It also contains a bug affecting the operation of our extensions.
            Please contact your host and ask them to upgrade PHP to at least 5.4 version on your server.'), phpversion());
        }

        if (!function_exists('token_get_all')) {
            $errors[] = 'PHP tokenizer extension must be enabled by your host.';
        }

        if(!class_exists('mysqli')) {
            $errors[] = JText::_("We're sorry but your server isn't configured with the MySQLi database driver. Please
		    contact your host and ask them to enable MySQLi for your server.");
        }

        if(version_compare(JFactory::getDbo()->getVersion(), '5.1', '<')) {
            $errors[] = sprintf(JText::_('Joomlatools framework requires MySQL 5.1 or later.
            Please contact your host and ask them to upgrade MySQL to 5.1 or a newer version on your server.'), JFactory::getDbo()->getVersion());
        }
        else {
            $result = JFactory::getDbo()->setQuery("SELECT SUPPORT FROM INFORMATION_SCHEMA.ENGINES WHERE ENGINE = 'InnoDB'")->loadResult();
            if(!in_array(strtoupper($result), array('YES', 'DEFAULT'))) {
                $errors[] = JText::_("Joomlatools framework requires MySQL InnoDB support. Please contact your host and ask them to enable InnoDB.");
            }
        }

        // Check if Ohanah v2 or v3 is installed
        $ohanah_manifest = JPATH_ADMINISTRATOR.'/components/com_ohanah/ohanah.xml';
        if (file_exists($ohanah_manifest))
        {
            $errors[] = sprintf("You have the Ohanah event management extension installed.
                    Ohanah works with an older version of the Joomlatools framework, so upgrading Joomlatools framework now would break your site.
                    Installation is aborting. For more information please read our detailed explanation <a target=\"_blank\" href=\"%s\">here</a>.",
                'http://www.joomlatools.com/framework-known-issues');
        }

        if (class_exists('Koowa') && (!method_exists('Koowa', 'getInstance') || version_compare(Koowa::getInstance()->getVersion(), '1', '<')))
        {
            $errors[] = sprintf(JText::_("Your site has an older version of our library already installed. Installation
			 is aborting to prevent creating conflicts with other extensions."));
        }

        //Some hosts that specialize on Joomla are known to lock permissions to the libraries folder
        if(!is_writable(JPATH_LIBRARIES))
        {
            $errors[] = sprintf(JText::_("The <em title=\"%s\">libraries</em> folder needs to be writable in order for
		    Joomlatools framework to install correctly."), JPATH_LIBRARIES);
        }

        return $errors;
    }

    /**
     * Can't use JPluginHelper here since there is no way
     * of clearing the cached list of plugins.
     *
     * @return bool
     */
    public function bootFramework()
    {
        if (class_exists('Koowa')) {
            return true;
        }

        $path = JPATH_PLUGINS.'/system/joomlatools/joomlatools.php';

        if (!file_exists($path)) {
            return false;
        }

        require_once $path;

        $dispatcher = JEventDispatcher::getInstance();
        $className  = 'PlgSystemJoomlatools';

        // Constructor does all the work in the plugin
        if (class_exists($className))
        {
            $db = JFactory::getDbo();
            $db->setQuery(/** @lang text */"SELECT folder AS type, element AS name, params
			 FROM #__extensions
			 WHERE folder = 'system' AND element = 'joomlatools'"
            );
            $plugin = $db->loadObject();

            new $className($dispatcher, (array) ($plugin));
        }

        return class_exists('Koowa');
    }
}