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
                        cwd: '<%= KUIPath %>/../dist/js',
                        src: ['**'],
                        dest: '<%= nookuFrameworkAssetsPath %>/js'
                    },
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