<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die( 'Restricted access' ); ?>


<!-- Uploader -->
<div id="files-upload-multi" class="k-upload--boxed-top"></div>


<div class="k-dynamic-content-holder">
    <?= import('com:files.files.uploader_scripts.html') ?>

    <script>
        window.addEvent('domready', function() {
            var timeout = null,
                createUploader = function() {
                    if (Files.app) {
                        Files.createUploader({
                            multi_selection: <?= json_encode((!isset($multi_selection) || $multi_selection !== false) ? true : false) ?>
                        });

                        if (timeout) {
                            clearTimeout(timeout);
                        }
                    } else {
                        timeout = setTimeout(createUploader, 100);
                    }
                };

            createUploader();

        });
    </script>
</div>
