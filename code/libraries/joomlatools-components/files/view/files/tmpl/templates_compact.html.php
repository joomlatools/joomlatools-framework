<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die( 'Restricted access' ); ?>


<textarea style="display: none" id="compact_details_image">
[%
var width = 0, height = 0;
if (metadata.image) {
    width  = metadata.image.width;
    height = metadata.image.height;
}
%]
<div class="k-details">
    <div class="k-card">
        <div class="k-card__body">
            <div class="k-card__section k-card__section--small-spacing">
                <div class="k-ratio-block k-ratio-block--4-to-3 k-overflow-hidden">
                    <div class="k-loader"></div>
                    <div class="k-ratio-block__body">
                        <div class="k-ratio-block__centered">
                            <img class="icon" src="" alt="[%=name%]" border="0" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <dl>
        <dt><?= translate('Name'); ?></dt>
        <dd class="k-ellipsis"><span class="k-ellipsis__item">[%=name%]</span></dd>
        [% if (width && height) { %]
        <dt><?= translate('Dimensions'); ?></dt>
        <dd>[%=width%] x [%=height%]</dd>
        [% } %]
        <dt><?= translate('Size'); ?></dt>
        <dd>[%=size.humanize()%]</dd>
    </dl>
</div>
</textarea>

<textarea style="display: none" id="compact_details_file">
<div class="k-details">
    <div class="k-card k-card--center">
        <div class="k-card__body">
            <div class="k-card__section">
                <span class="k-icon-document-document k-icon--size-large k-icon--accent"></span>
            </div>
        </div>
    </div>
    <dl>
        <dt><?= translate('Name'); ?></dt>
        <dd>[%=name%]</dd>
        <dt><?= translate('Size'); ?></dt>
        <dd>[%=size.humanize()%]</dd>
    </dl>
</div>
</textarea>

<textarea style="display: none" id="compact_container">
    <div class="k-table-container">
        <div class="k-table">
            <table>
                <tbody></tbody>
            </table>
        </div>
    </div>
</textarea>


<textarea style="display: none"  id="compact_folder">
    <tr class="files-node files-folder">
        <td class="k-table-data--ellipsis">
            <span>
                <a class="navigate k-link-coverall" href="#" title="[%= name %]">
                    [%= name %]
                </a>
            </span>
        </td>
    </tr>
</textarea>

<textarea style="display: none"  id="compact_image">
    <tr class="files-node files-image">
        <td class="k-table-data--ellipsis">
            <span>
                <a class="navigate k-link-coverall" href="#" title="[%= name %]">
                    [%= name %]
                </a>
            </span>
        </td>
    </tr>

</textarea>

<textarea style="display: none"  id="compact_file">
    <tr class="files-node files-file">
        <td class="k-table-data--ellipsis">
            <span >
                <a class="navigate k-link-coverall" href="#" title="[%= name %]">
                    [%= name %]
                </a>
            </span>
        </td>
    </tr>
</textarea>
