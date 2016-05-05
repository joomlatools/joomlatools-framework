<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die( 'Restricted access' ); ?>

<textarea style="display: none" id="compact_details_image">
[%
var width = 0, height = 0, ratio = 0;
if (metadata.image) {
    width  = metadata.image.width;
    height = metadata.image.height;
    ratio  = 250 / (width > height ? width : height);
}
%]
<div class="k-details">
    <div class="k-details-image-placeholder">
        <div class="k-details-image-placeholder__content">
            <img class="icon" src="" alt="[%=name%]" border="0"
                 onerror="kQuery(this).hide();"
                width="[%=Math.min(ratio*width, width)%]" height="[%=Math.min(ratio*height, height)%]" />
        </div>
    </div>
    <p>
        <strong class="labl"><?= translate('Name'); ?></strong>
        [%=name%]
    </p>
    <p>
        <strong class="labl"><?= translate('Dimensions'); ?></strong>
        [%=width%] x [%=height%]
    </p>
    <p>
        <strong class="labl"><?= translate('Size'); ?></strong>
        [%=size.humanize()%]
    </p>
</div>
</textarea>

<textarea style="display: none" id="compact_details_file">
<div class="details">
    <div style="text-align: center">
        <span class="koowa_icon--document"><i>[%=name%]</i></span>
    </div>
    <table class="table table-condensed parameters">
        <tbody>
            <tr>
                <td class="detail-label"><?= translate('Name'); ?></td>
                <td>
                    <div class="koowa_wrapped_content">
                        <div class="whitespace_preserver">[%=name%]</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="detail-label"><?= translate('Size'); ?></td>
                <td>[%=size.humanize()%]</td>
            </tr>
        </tbody>
    </table>
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
        <td>
            <span>
                <a class="navigate" href="#" title="[%= name %]">
                    [%= name %]
                </a>
            </span>
        </td>
    </tr>
</textarea>

<textarea style="display: none"  id="compact_image">
    <tr class="files-node files-image">
        <td>
            <span>
                <a class="navigate" href="#" title="[%= name %]">
                    [%= name %]
                </a>
            </span>
        </td>
    </tr>

</textarea>

<textarea style="display: none"  id="compact_file">
    <tr class="files-node files-file">
        <td>
            <span >
                <a class="navigate" href="#" title="[%= name %]">
                    [%= name %]
                </a>
            </span>
        </td>
    </tr>
</textarea>