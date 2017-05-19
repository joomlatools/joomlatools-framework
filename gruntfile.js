module.exports = function(grunt) {

    // measures the time each task takes
    require('time-grunt')(grunt);

    // load time-grunt and all grunt plugins found in the package.json
    require('jit-grunt')(grunt);


    // grunt config
    grunt.initConfig({

        // Grunt variables
        nookuFrameworkAssetsPath: 'code/libraries/joomlatools/library/resources/assets',
        joomlatoolsFrameworkAssetsPath: 'code/libraries/joomlatools/component/koowa/resources/assets',
        KUIPath: '../kodekit-ui/src',
        JUIPath: '../joomlatools-ui/dist',


        // Shell commands
        shell: {
            updateCanIUse: {
                command: 'npm update caniuse-db'
            }
        },


        // Copy Joomlatools UI files
        copy: {
            adminjs: {
                files: [
                    {
                        src: '<%= KUIPath %>/../dist/js/build/admin.js',
                        dest: '<%= nookuFrameworkAssetsPath %>/js/build/admin.js'
                    },
                    {
                        src: '<%= KUIPath %>/../dist/js/min/admin.js',
                        dest: '<%= nookuFrameworkAssetsPath %>/js/min/admin.js'
                    },
                    {
                        src: '<%= KUIPath %>/../dist/js/min/admin.js.map',
                        dest: '<%= nookuFrameworkAssetsPath %>/js/min/admin.js.map'
                    }
                ]
            },
            JUItoJUIFramework: {
                files: [
                    {
                        expand: true,
                        src: ['<%= JUIPath %>/css/*.*'],
                        dest: '<%= joomlatoolsFrameworkAssetsPath %>/css',
                        flatten: true
                    },
                    {
                        expand: true,
                        cwd: '<%= JUIPath %>/fonts',
                        src: ['**'],
                        dest: '<%= joomlatoolsFrameworkAssetsPath %>/fonts'
                    }
                ]
            },
            JUItoKUIFramework: {
                files: [
                    {
                        expand: true,
                        src: ['<%= JUIPath %>/css/admin.*'],
                        dest: '<%= nookuFrameworkAssetsPath %>/css',
                        flatten: true
                    },
                    {
                        expand: true,
                        cwd: '<%= JUIPath %>/fonts',
                        src: ['**'],
                        dest: '<%= nookuFrameworkAssetsPath %>/fonts'
                    },
                    {
                        expand: true,
                        cwd: '<%= JUIPath %>/js',
                        src: ['**'],
                        dest: '<%= nookuFrameworkAssetsPath %>/js'
                    },
                    {
                        expand: true,
                        cwd: '<%= KUIPath %>/js',
                        src: ['**'],
                        dest: '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui'
                    }
                ]
            }
        },


        // Compile sass files
        sass: {
            options: {
                outputStyle: 'compact',
                includePaths: [
                    'bower_components',
                    'node_modules'
                ]
            },
            dist: {
                files: {

                    // Nooku Framework
                    '<%= nookuFrameworkAssetsPath %>/css/admin.css': '<%= nookuFrameworkAssetsPath %>/scss/admin.scss',
                    '<%= nookuFrameworkAssetsPath %>/css/bootstrap.css': '<%= nookuFrameworkAssetsPath %>/scss/bootstrap.scss',
                    '<%= nookuFrameworkAssetsPath %>/css/debugger.css': '<%= nookuFrameworkAssetsPath %>/scss/debugger.scss',
                    '<%= nookuFrameworkAssetsPath %>/css/dumper.css': '<%= nookuFrameworkAssetsPath %>/scss/dumper.scss',
                    '<%= nookuFrameworkAssetsPath %>/css/site.css': '<%= nookuFrameworkAssetsPath %>/scss/site.scss',

                    // Joomlatools Framework
                    '<%= joomlatoolsFrameworkAssetsPath %>/css/admin.css': '<%= joomlatoolsFrameworkAssetsPath %>/scss/admin.scss',
                    '<%= joomlatoolsFrameworkAssetsPath %>/css/component.css': '<%= joomlatoolsFrameworkAssetsPath %>/scss/component.scss',
                    '<%= joomlatoolsFrameworkAssetsPath %>/css/isis.css': '<%= joomlatoolsFrameworkAssetsPath %>/scss/isis.scss',
                    '<%= joomlatoolsFrameworkAssetsPath %>/css/hathor.css': '<%= joomlatoolsFrameworkAssetsPath %>/scss/hathor.scss'
                }
            }
        },


        // Minify and clean CSS
        cssmin: {
            options: {
                roundingPrecision: -1,
                sourceMap: true
            },
            site: {
                files: [{
                    expand: true,
                    src: ['<%= nookuFrameworkAssetsPath %>/css/*.css', '<%= joomlatoolsFrameworkAssetsPath %>/css/*.css', '!*.css']
                }]
            }
        },


        // Concatenate files

        concat: {
            js: {
                files: {
                    '<%= nookuFrameworkAssetsPath %>/js/build/vue.js': [
                        'node_modules/vue/dist/vue.js',
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/build/vuex.js': [
                        'node_modules/vuex/dist/vuex.js',
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/vue.js': [
                        'node_modules/vue/dist/vue.min.js',
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/vuex.js': [
                        'node_modules/vuex/dist/vuex.min.js',
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/build/jquery.js': [
                        'node_modules/jquery/dist/jquery.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.noconflict.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/build/jquery.magnific-popup.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        'node_modules/magnific-popup/dist/jquery.magnific-popup.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/build/jquery.validate.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        'node_modules/jquery-validation/dist/jquery.validate.js',
                        '<%= nookuFrameworkAssetsPath %>/js/jquery.validate.patch.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/build/koowa.datepicker.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/datepicker.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/build/koowa.select2.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        'node_modules/select2/dist/js/select2.full.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/build/koowa.tree.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        'node_modules/jqtree/tree.jquery.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.tree.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/build/koowa.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/jquery.ui.widget.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.scopebar.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.class.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.grid.js',
                        '<%= nookuFrameworkAssetsPath %>/js/koowa.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/build/tooltip.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/bootstrap.tooltip.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ]
                }
            }
        },

        // Uglify
        uglify: {
            options: {
                sourceMap: true,
                preserveComments: /(?:^!|@(?:license|preserve|cc_on))/ // preserve @license tags
            },
            build: {
                files: {
                    '<%= nookuFrameworkAssetsPath %>/js/min/bootstrap.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/bootstrap.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/jquery.js': [
                        'node_modules/jquery/dist/jquery.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.noconflict.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/jquery.magnific-popup.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        'node_modules/magnific-popup/dist/jquery.magnific-popup.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/jquery.validate.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        'node_modules/jquery-validation/dist/jquery.validate.js',
                        '<%= nookuFrameworkAssetsPath %>/js/jquery.validate.patch.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/koowa.datepicker.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/datepicker.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/koowa.select2.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        'node_modules/select2/dist/js/select2.full.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/koowa.tree.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        'node_modules/jqtree/tree.jquery.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.tree.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/koowa.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/jquery.ui.widget.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.scopebar.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.class.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/koowa.grid.js',
                        '<%= nookuFrameworkAssetsPath %>/js/koowa.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ],
                    '<%= nookuFrameworkAssetsPath %>/js/min/tooltip.js': [
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.set.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/bootstrap-tooltip.js',
                        '<%= nookuFrameworkAssetsPath %>/js/kodekit-ui/kquery.unset.js'
                    ]
                }
            }
        },


        // Autoprefixer
        autoprefixer: {
            options: {
                browsers: ['> 5%', 'last 2 versions', 'ie 11']
            },
            files: {
                nooku: {
                    expand: true,
                    flatten: true,
                    src: '<%= nookuFrameworkAssetsPath %>/css/*.css',
                    dest: '<%= nookuFrameworkAssetsPath %>/css/'
                },
                joomlatools: {
                    expand: true,
                    flatten: true,
                    src: '<%= joomlatoolsFrameworkAssetsPath %>/css/*.css',
                    dest: '<%= joomlatoolsFrameworkAssetsPath %>/css/'
                }
            }
        },



        // Watch files
        watch: {
            fontcustom: {
                files: [
                    '<%= nookuFrameworkAssetsPath %>/icons/svg/*.svg'
                ],
                tasks: ['sass', 'cssmin', 'autoprefixer'],
                options: {
                    interrupt: true,
                    atBegin: false
                }
            },
            sass: {
                files: [
                    '<%= nookuFrameworkAssetsPath %>/scss/*.scss',
                    '<%= nookuFrameworkAssetsPath %>/scss/**/*.scss',
                    '<%= joomlatoolsFrameworkAssetsPath %>/scss/*.scss',
                    '<%= joomlatoolsFrameworkAssetsPath %>/scss/**/*.scss',
                    '<%= KUIPath %>/scss/*.scss',
                    '<%= KUIPath %>/scss/**/*.scss'
                ],
                tasks: ['sass', 'cssmin', 'autoprefixer'],
                options: {
                    interrupt: true,
                    atBegin: true
                }
            },
            javascript: {
               files: [
                   '<%= nookuFrameworkAssetsPath %>/scripts/*.js',
                   '<%= nookuFrameworkAssetsPath %>/js/*.js',
                   '!<%= nookuFrameworkAssetsPath %>/js/min/*.js'
               ],
               tasks: ['concat', 'uglify'],
               options: {
                   interrupt: true,
                   atBegin: true
               }
            }
        }


    });

    // The dev task will be used during development
    grunt.registerTask('default', ['shell', 'copy', 'watch']);

    // Javascript only
    grunt.registerTask('javascript', ['uglify', 'concat']);

    grunt.registerTask('css', ['sass', 'cssmin', 'autoprefixer']);
};