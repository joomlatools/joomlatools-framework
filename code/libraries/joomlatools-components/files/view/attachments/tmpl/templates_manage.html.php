<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die; ?>


<textarea style="display: none" id="attachments_container">
    <table id="document_list">
        <thead>
        <tr>
            <th>
                <?= translate('Attached files'); ?>
                <span class="count"></span>
            </th>
        </tr>
        </thead>
        <tbody id="attachments-grid"></tbody>
    </table>
</textarea>

<textarea style="display: none" id="attachments_attachment">
    <table>
        <tbody class="template-item">
            <tr class="files-node files-attachment">
                <td>
                    <? if ($can_detach): ?>
                        <span type="button" class="detach_button k-button k-button--default k-button--small k-link-ontop" tabindex="0">x</span>
                    <? endif ?>
                    <a class="navigate k-link-coverall" href="#" title="[%= name %]">
                        [%= name %]
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</textarea>

<textarea style="display: none" id="attachments_details_attachment">
    [%
    var width = 0, height = 0, ratio = 0,
        is_image = (file.type == 'image');
    if (file.metadata && file.metadata.image) {
        width  = file.metadata.image.width;
        height = file.metadata.image.height;
        ratio  = 150 / (width > height ? width : height);
    }
    %]
    <div class="k-details">
        <div class="k-card k-card--center">
            <div class="k-card__body">
                [% if (is_image) { %]
                    <div class="k-card__section k-card__section--small-spacing">
                        <div class="k-ratio-block k-ratio-block--4-to-3">
                            <div class="k-loader"></div>
                            <div class="k-ratio-block__body">
                                <div class="k-ratio-block__centered">
                                    <img class="icon" src="" alt="[%=name%]" border="0" width="[%=Math.min(ratio*width, width)%]" height="[%=Math.min(ratio*height, height)%]" />
                                </div>
                            </div>
                        </div>
                    </div>
                [% } else { %]
                    <div class="k-card__section">
                        <span class="k-icon-document-default k-icon--size-large k-icon--accent" aria-hidden="true"></span>
                        <span class="k-visually-hidden">[%=name%]</span>
                    </div>
                [% } %]
            </div>
        </div>
        <dl>
            [% if (is_image && width && height){ %]
            <dt><?= translate('Dimensions'); ?></dt>
            <dd>[%=width%] x [%=height%]</dd>
            [% } %]
            <dt><?= translate('Size'); ?></dt>
            <dd>[%=size.humanize()%]</dd>
            <dt><?= translate('Attached by'); ?></dt>
            <dd>[%=attached_by_name%] <small><?= translate('on') ?> [%=getAttachedDate(true)%]</small></dd>
        </dl>
    </div>
</textarea>
