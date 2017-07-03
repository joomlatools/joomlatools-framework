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
        KUIPath: '../kodekit-ui/dist',
        JUIPath: '../joomlatools-ui/dist',


        // Shell commands
        shell: {
            updateCanIUse: {
                command: 'npm update caniuse-db'
            }
        },


        // Copy Joomlatools UI files
        copy: {
            JUI: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= JUIPath %>/css',
                        src: ['*.css', '!*.min.css'],
                        dest: '<%= joomlatoolsFrameworkAssetsPath %>/css/build/'
                    },
                    {
                        expand: true,
                        cwd: '<%= JUIPath %>/css',
                        src: ['*.min.css'],
                        dest: '<%= joomlatoolsFrameworkAssetsPath %>/css/',
                        rename: function(dest, src) {
                            return dest + src.replace(/\.min/, "");
                        }
                    },
                    {
                        expand: true,
                        cwd: '<%= JUIPath %>/fonts',
                        src: ['**'],
                        dest: '<%= joomlatoolsFrameworkAssetsPath %>/fonts/'
                    }
                ]
            },
            KUI: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= KUIPath %>/css',
                        src: ['*.css', '!*.min.css'],
                        dest: '<%= nookuFrameworkAssetsPath %>/css/build/'
                    },
                    {
                        expand: true,
                        cwd: '<%= KUIPath %>/css',
                        src: ['*.min.css'],
                        dest: '<%= nookuFrameworkAssetsPath %>/css/',
                        rename: function(dest, src) {
                            return dest + src.replace(/\.min/, "");
                        }
                    },
                    {
                        expand: true,
                        cwd: '<%= KUIPath %>/fonts',
                        src: ['**'],
                        dest: '<%= nookuFrameworkAssetsPath %>/fonts/'
                    },
                    {
                        expand: true,
                        cwd: '<%= KUIPath %>/js',
                        src: ['*.js', '!*.min.js'],
                        dest: '<%= nookuFrameworkAssetsPath %>/js/build/'
                    },
                    {
                        expand: true,
                        cwd: '<%= KUIPath %>/js',
                        src: ['*.min.js'],
                        dest: '<%= nookuFrameworkAssetsPath %>/js/min/',
                        rename: function(dest, src) {
                            return dest + src.replace(/\.min/, "");
                        }
                    }
                ]
            },
            VUE: {
                files: [
                    {
                        expand: true,
                        cwd: 'node_modules/vue/dist',
                        src: ['vue.js'],
                        dest: '<%= nookuFrameworkAssetsPath %>/js/build/'
                    },
                    {
                        expand: true,
                        cwd: 'node_modules/vuex/dist',
                        src: ['vuex.js'],
                        dest: '<%= nookuFrameworkAssetsPath %>/js/build/'
                    },
                    {
                        expand: true,
                        cwd: 'node_modules/vue/dist',
                        src: ['vue.min.js'],
                        dest: '<%= nookuFrameworkAssetsPath %>/js/min/',
                        rename: function(dest, src) {
                            return dest + src.replace(/\.min/, "");
                        }
                    },
                    {
                        expand: true,
                        cwd: 'node_modules/vuex/dist',
                        src: ['vuex.min.js'],
                        dest: '<%= nookuFrameworkAssetsPath %>/js/min/',
                        rename: function(dest, src) {
                            return dest + src.replace(/\.min/, "");
                        }
                    }
                ]
            }
        },


        // Compile sass files
        sass: {
            options: {
                outputStyle: 'minified',
                includePaths: [
                    'bower_components',
                    'node_modules'
                ]
            },
            dist: {
                files: {
                    // Nooku Framework
                    '<%= nookuFrameworkAssetsPath %>/css/bootstrap.css': '<%= nookuFrameworkAssetsPath %>/scss/bootstrap.scss',
                    '<%= nookuFrameworkAssetsPath %>/css/debugger.css': '<%= nookuFrameworkAssetsPath %>/scss/debugger.scss',
                    '<%= nookuFrameworkAssetsPath %>/css/dumper.css': '<%= nookuFrameworkAssetsPath %>/scss/dumper.scss',
                    '<%= nookuFrameworkAssetsPath %>/css/site.css': '<%= nookuFrameworkAssetsPath %>/scss/site.scss'
                }
            }
        },


        // Autoprefixer
        autoprefixer: {
            options: {
                browsers: ['> 5%', 'last 2 versions']
            },
            files: {
                nooku: {
                    expand: true,
                    flatten: true,
                    src: '<%= nookuFrameworkAssetsPath %>/css/*.css',
                    dest: '<%= nookuFrameworkAssetsPath %>/css/'
                }
            }
        },



        // Watch files
        watch: {
            sass: {
                files: [
                    '<%= nookuFrameworkAssetsPath %>/scss/*.scss',
                    '<%= nookuFrameworkAssetsPath %>/scss/**/*.scss'
                ],
                tasks: ['sass', 'autoprefixer'],
                options: {
                    interrupt: true,
                    atBegin: true
                }
            }
        }


    });

    // The dev task will be used during development
    grunt.registerTask('default', ['shell', 'copy', 'watch']);

};