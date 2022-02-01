/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

(function($){

    /**
     * Files.Tree is a wrapper for Koowa.Tree, which wraps jqTree
     * @type extend Koowa.Tree
     */
    Files.Tree = Koowa.Tree.extend({
        /**
         * Get the default options
         * @returns options combined with the defaults from parent classes
         */
        getDefaults: function(){

            var self = this,
                defaults = {
                    initial_response: false,
                    autoOpen: 0, //root.open = true on previous script
                    onSelectNode: function(){},
                    dataFilter: function(response){
                        return self.filterData(response);
                    }
                };

            return $.extend(true, {}, this.supr(), defaults); // get the defaults from the parent and merge them
        },
        filterData: function(response) {

            var that = this;

            var data = response.entities,
                parse = function(item, parent)
                {
                    var path = (!parent && that.options.root_path) ? that.options.root_path + '/' : ''; // Prepend root folder if set
                    path += (parent && parent.path) ? parent.path+'/' : '';
                    path += item.name;

                    //Parse attributes
                    //@TODO check if 'type' is necessary
                    item = $.extend(item, {
                        id: path,
                        path: path,
                        url: '#'+path,
                        type: 'folder'
                    });

                    if (item.children) {
                        var children = [];
                        Object.each(item.children, function(child) {
                            children.push(parse(child, item));
                        });
                        item.children = children;
                    }

                    return item;
                };

            if (response.meta.total) {
                Object.each(data, function(item, key) {
                    parse(item);
                });
            }

            return this.parseData(data);
        },
        /**
         * Customized parseData method due to using json, in a already nested data format
         * @param list json returned data
         * @returns data
         */
        parseData: function(list){
            var tree = {
                label: this.options.root.text,
                url: '#',
                children: list
            };

            if (this.options.root_path)
            {
                tree.id = this.options.root_path;
                tree.url = '#' + tree.id;
            }

            return [tree];
        },
        fromUrl: function(url, callback) {

            var self = this;
            this.tree('loadDataFromUrl', url, null, function(response){
                /**
                 * @TODO refactor chaining support to this.selectPath so it works even when the tree isn't loaded yet
                 */
                if(Files.app && Files.app.hasOwnProperty('active')) self.selectPath(Files.app.active);

                if (callback) {
                    callback(response);
                }
            });

        },
        /**
         * Select a path, pass '' to select the root
         * @param path string
         */
        selectPath: function(path) {

            var node = this.tree('getNodeById', path);

            if(!node) {
                var tree = this.tree('getTree');
                node = tree.children.length ? tree.children[0] : null;
            }

            this.tree('selectNode', node);
        },
        /**
         * Append a node to the tree
         * Required properties are 'id' and 'label', other properties are optional.
         * If no parent specified then the node is appended to the current selected node.
         * Pass parent as null for adding the node to root
         *
         * This API is intended for adding user created nodes, don't use this API to add multiple items or to refresh
         * the tree with updated data from the server.
         * Use fromUrl instead, as it's performance optimized for that purpose.
         *
         * @param row
         * @param parent    optional    Node instance, pass 'null' to force the node to be added to the root
         */
        appendNode: function(row, parent){

            if(parent === undefined) parent = this.tree('getSelectedNode');
            if(parent === false)     parent = this.tree('getTree').children[0]; //Get the root node when nothing is selected

            var node, data = $.extend(true, {}, row, {
                path: row.id,
                url: '#'+row.id,
                type: 'folder'
            });

            /**
             * If there's siblings, make sure it's added in alphabetical order
             */
            if(parent && parent.children && parent.children.length) {
                var name = data.label.toLowerCase();
                if(parent.children[0].name.toLowerCase() > name) {
                    node = this.tree('addNodeBefore', data, parent.children[0]);
                } else if(parent.children[parent.children.length - 1].name.toLowerCase() < name) {
                    node = this.tree('appendNode', data, parent);
                } else {
                    var i = 0;
                    while(parent.children[i].name.toLowerCase() < name) {
                        i++;
                    }
                    node = this.tree('addNodeBefore', data, parent.children[i]);
                }
            } else {
                node = this.tree('appendNode', data, parent);
            }
            /**
             * @TODO please investigate:
             * It may be counter-productive to always navigate into newly created folders, investigate if
             * just selecting the folder in the grid is a better workflow as it allows creating multiple folders with
             * lesser clicking around.
             */
            this.tree('selectNode', node);

            return this;
        },

        /**
         * Remove a node by path
         * @param path
         */
        removeNode: function(path){

            var node = this.tree('getNodeById', path);
            if(node) {
                this.tree('removeNode', node);
            }

        },

        attachHandlers: function(){

            this._attachHandlers(); // Attach needed events from Koowa.Tree._attachHandlers

            var options = this.options, self = this, initial = this.options.initial_response;

            this.element.bind({
                'tree.init': function(){
                    self.element.on('tree.select', function(event){

                        var element;
                        if(event.node) { // When event.node is null, it's actually a deselect event
                            element = $(event.node.element);

                            self.tree('openNode', event.node); // open the selected node, if not open already

                            //Fire custom select node handler
                            if(!initial) {
                                options.onSelectNode(event.node);
                            } else {
                                initial = false;
                            }
                        }
                        if(event.node && !event.node.hasOwnProperty('is_open') && event.node.getLevel() === 2) {
                            self.scrollIntoView(event.node, self.element, 300);
                        }

                        /**
                         * Sidebar.js will fire a resize event when it sets the height on load, we want our animated scroll
                         * to happen after that, but not on future resize events as it would confuse the user experience
                         */

                        self.element.one('resize', function(){
                            if(self.tree('getSelectedNode')) {
                                self.scrollIntoView(self.tree('getSelectedNode'), self.element, 900);
                            }
                        });
                    });
                },
                // Animate a scroll to the node being opened so child elements scroll into view
                'tree.open': function(event) {
                    self.scrollIntoView(event.node, self.element, 300);
                }
            });

        }
    });

}(window.kQuery));