<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die( 'Restricted access' ); ?>


<script>
window.addEvent('domready', function() {
	document.id('files-canvas').addEvent('click:relay(input.-check-all)', function(e) {
		var value = e.target.checked,
			grid = Files.app.grid,
			nodes = grid.nodes;

		Object.each(nodes, function(node) {
			if (value && !node.checked) {
				grid.checkNode(node);
			} else if (!value && node.checked) {
				grid.checkNode(node);
			}

		});

	});

	if (Files.app.tree)
    {
        Files.app.tree.element.on('tree.select', function(event)
        {
            var el = document.id('select-check-all');

            if (el && el.checked) {
                el.checked = false;
            }
        });

    }

    Files.app.grid.addEvent('afterDeleteNode', function() {
        var el = document.id('select-check-all');

        if (el && el.checked && !this.nodes.getLength()) {
            el.checked = false;
        }
    }.bind(Files.app.grid));
})
</script>

<textarea style="display: none" id="details_container">
    <div class="k-table">
        <table class="k-js-responsive-table">
            <thead>
            <tr>
                <th width="1%" class="k-table-data--form">
                    <input type="checkbox" class="-check-all" id="select-check-all" />
                </th>
                <th width="1%" class="k-table-data--toggle" data-toggle="true"></th>
                <th width="1%" class="k-table-data--icon"></th>
                <th width="80%" data-name="name" class="k-js-files-sortable">
                    <a href="#"><?= translate('Name'); ?></a>
                </th>
                <th width="1%" data-hide="phone">
                    <?= translate('Size'); ?>
                </th>
                <th class="k-js-files-sortable k-table-data--nowrap" data-hide="phone,tablet,desktop"
                    data-name="modified_on">
                    <a href="#"><?= translate('Last Modified'); ?></a>
                </th>
                <th class="k-table-data--icon" width="1%" data-hide="phone,tablet">
                    <span class="k-icon-data-transfer-download"></span>
                </th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</textarea>

<textarea style="display: none" id="details_folder">
	<tr class="files-node files-folder">
		<td class="k-table-data--form">
			<input type="checkbox" class="files-select" value="" />
		</td>
        <td class="k-table-data--toggle k-table-data--toggle--hidden"></td>
		<td class="k-table-data--icon">
            <span class="k-icon-folder-closed"></span>
		</td>
		<td class="k-table-data--ellipsis" colspan="5">
            <a href="#" class="navigate" data-k-tooltip='{"container":".k-ui-container","delay":{"show":500,"hide":50}}' data-original-title="<?= translate('Open folder') ?>">[%=name%]</a>
		</td>
	</tr>
</textarea>

<textarea style="display: none" id="details_file">
	<tr class="files-node files-file">
		<td class="k-table-data--form">
			<input type="checkbox" class="files-select" value="" />
		</td>
        [%
        var icon = 'default',
        extension = name.substr(name.lastIndexOf('.')+1).toLowerCase();

        kQuery.each(Files.icon_map, function(key, value) {
                if (kQuery.inArray(extension, value) !== -1) {
                icon = key;
            }
        });
        %]
        <td class="k-table-data--toggle"></td>
        <td class="k-table-data--icon">
            [% if (type == 'image') { %]
                <img src="[%= client_cache || Files.blank_image %]" alt="[%=name%]" border="0" class="image-thumbnail [%= client_cache ? 'loaded' : '' %]" height="24px" />
            [% } else { %]
                <span class="k-icon-document-[%=icon%]"></span>
            [% } %]
		</td>
		<td class="k-table-data--ellipsis">
            <a href="#" class="navigate" data-k-tooltip='{"container":".k-ui-container","delay":{"show":500,"hide":50}}' data-original-title="<?= translate('View file info') ?>">[%=name%]</a>
		</td>
		<td class="k-table-data--nowrap">
            [%=size.humanize()%]
		</td>
		<td class="k-table-data--nowrap">
            [%=getModifiedDate(true)%]
		</td>
        <td class="k-table-data--icon">
            <a href="[%=download_link%]" target="_blank" download="[%=name%]">
                <span class="k-icon-data-transfer-download"></span>
            </a>
        </td>
	</tr>
</textarea>

<textarea style="display: none" id="details_image">
	<tr class="files-node files-image">
		<td class="k-table-data--form">
			<input type="checkbox" class="files-select" value="" />
		</td>
        <td class="k-table-data--toggle"></td>
        <td class="k-table-data--icon">
            [% if (type == 'image') { %]
                <img src="[%= client_cache || Files.blank_image %]" alt="[%=name%]" border="0" class="image-thumbnail [%= client_cache ? 'loaded' : '' %]" height="24px" />
            [% } else { %]
                <span class="k-icon-document-image"></span>
            [% } %]
		</td>
        <td class="k-table-data--ellipsis">
            <a href="#" class="navigate" data-k-tooltip='{"container":".k-ui-container","delay":{"show":500,"hide":50}}' data-original-title="<?= translate('View image') ?>">[%=name%]</a>
		</td>
		<td class="k-table-data--nowrap">
            [%=size.humanize()%][% if (metadata.image) { %]<br />
            <small>([%=metadata.image.width%] x [%=metadata.image.height%])[% } %]</small>
		</td>
		<td class="k-table-data--nowrap">
            [%=getModifiedDate(true)%]
		</td>
        <td class="k-table-data--icon">
            <a href="[%=download_link%]" target="_blank" download="[%=name%]">
                <span class="k-icon-data-transfer-download"></span>
            </a>
        </td>
	</tr>
</textarea>
