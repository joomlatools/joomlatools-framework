<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		https://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die( 'Restricted access' ); ?>


<textarea style="display: none" id="file_preview">
<div class="k-ui-namespace k-small-inline-modal-holder mfp-hide extension-[%=metadata.extension%]">
    <div class="k-inline-modal">
        [% var view_path = Files.app.createRoute({view: 'file', format: 'html', name: name, folder: folder}); %]
        <div class="k-content-block">
            <p>
                <span class="k-icon-document k-icon--size-xlarge"></span>
            </p>
            <p>
                [% if (typeof image !== 'undefined') { %]
                <a class="k-button k-button--default k-button--small" href="[%=view_path%]" target="_blank">
                    <span class="k-icon-eye" aria-hidden="true"></span> <?= translate('View'); ?>
                </a>
                [% } else { %]
                <a class="k-button k-button--default k-button--small" href="[%=view_path%]" target="_blank" download="[%=name%]">
                    <span class="k-icon-data-transfer-download" aria-hidden="true"></span> <?= translate('Download'); ?>
                </a>
                [% } %]
            </p>
        </div>
        <dl>
            <dt><?= translate('Name'); ?></dt>
            <dd>[%=name%]</dd>
            <dt><?= translate('Size'); ?></dt>
            <dd>[%=size.humanize()%]</dd>
            <dt><?= translate('Modified'); ?></dt>
            <dd>[%=getModifiedDate(true)%]</dd>
        </dl>
    </div>
</div>
</textarea>

<textarea style="display: none" id="icons_container">
    <div class="k-gallery__items"></div>
</textarea>

<textarea style="display: none" id="icons_folder">
    <div class="k-gallery__item k-gallery__item--folder files-node files-folder">
        <div class="k-card k-card--rounded js-navigate-folder">
            <a href="javascript:void(0)" class="k-card__body navigate">
                <div class="k-card__section k-card__section--small-spacing">
                    <div class="k-ratio-block k-ratio-block--4-to-3">
                        <div class="k-ratio-block__body">
                            <div class="k-ratio-block__centered">
                                <span class="k-icon-document-folder k-icon--size-xlarge k-icon--accent extension-label"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <label class="k-card__caption k-card__caption--overflow">
                <input type="checkbox" class="files-select" value="" />
                [%=name%]
            </label>
        </div>
    </div>
</textarea>

<textarea style="display: none" id="icons_file">
    <div class="k-gallery__item k-gallery__item--file files-node files-file">
        <div class="k-card k-card--rounded">
            [%
            var icon = 'default',
            extension = name.substr(name.lastIndexOf('.')+1).toLowerCase();

            kQuery.each(Files.icon_map, function(key, value) {
                    if (kQuery.inArray(extension, value) !== -1) {
                    icon = key;
                }
            });
            %]
            <a href="javascript:void(0)"
               class="k-card__body navigate"
               data-filetype="[%=filetype%]"
               data-extension="[%=metadata.extension%]">
                <div class="k-card__section k-card__section--small-spacing">
                    <div class="k-ratio-block k-ratio-block--4-to-3">
                        <div class="k-ratio-block__body">
                            <div class="k-ratio-block__centered">
                            [% if (type == 'image') { %]
                                <img class="image-thumbnail [%= client_cache ? 'loaded' : '' %]" src="" alt="[%=name%]" border="0" />
                            [% } else { %]
                                <span class="k-icon-document-[%=icon%] k-icon--size-large k-icon--accent extension-label"></span>
                            [% }%]
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <label class="k-card__caption k-card__caption--overflow js-select-node">
                <input type="checkbox" class="files-select" value="" />
                [%=name%]
            </label>
        </div>
    </div>
</textarea>

<textarea style="display: none" id="icons_image">
    <div class="k-gallery__item k-gallery__item--file files-node files-image">
        <div class="k-card k-card--rounded">
            <a href="javascript:void(0)"
               class="k-card__body navigate"
               title="[%=name%]"
               data-filetype="[%=filetype%]"
               data-extension="[%=metadata.extension%]">
                <div class="k-card__section k-card__section--small-spacing">
                    <div class="k-ratio-block k-ratio-block--4-to-3">
                        <div class="k-ratio-block__body">
                            <div class="k-ratio-block__centered">
                            [% if (type == 'image') { %]
                                <img class="image-thumbnail [%= client_cache ? 'loaded' : '' %]" src="" alt="[%=name%]" border="0" />
                            [% } else { %]
                                <span class="k-icon-document-image k-icon--size-large k-icon--accent"></span>
                            [% }%]
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <label class="k-card__caption k-card__caption--overflow js-select-node">
                <input type="checkbox" class="files-select" value="" />
                [%=name%]
            </label>
        </div>
    </div>
</textarea>
