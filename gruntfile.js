module.exports = function(grunt) {

    // measures the time each task takes
    require('time-grunt')(grunt);

    // load time-grunt and all grunt plugins found in the package.json
    require('jit-grunt')(grunt);

    // Variables
    var sass = require('node-sass');

    // grunt config
    grunt.initConfig({

        // Grunt variables
        KUIPath: '../kodekit-ui',
        srcPath: 'code/libraries/joomlatools/component/koowa/resources/assets',
        distPath: 'code/libraries/joomlatools/component/koowa/resources/assets',


        // Compile sass files
        sass: {
            dist: {
                files: {
                    '<%= distPath %>/css/admin.css': '<%= srcPath %>/scss/admin.scss',
                    '<%= distPath %>/css/admin-dark.css': '<%= srcPath %>/scss/admin-dark.scss',
                    '<%= distPath %>/css/component.css': '<%= srcPath %>/scss/component.scss',
                    '<%= distPath %>/css/modal-override.css': '<%= srcPath %>/scss/modal-override.scss',
                    '<%= distPath %>/css/hathor.css': '<%= srcPath %>/scss/hathor.scss',
                    '<%= distPath %>/css/isis.css': '<%= srcPath %>/scss/isis.scss'
                }
            },
            options: {
                implementation: sass,
                includePaths: [
                    'node_modules',
                    '<%= KUIPath %>/node_modules'
                ],
                outputStyle: 'expanded',
                sourceMap: false
            }
        },


        // Autoprefixer
        autoprefixer: {
            options: {
                browsers: ['> 5%', 'last 2 versions']
            },
            files: {
                expand: true,
                flatten: true,
                src: '<%= distPath %>/css/*.css',
                dest: '<%= distPath %>/css/'
            }
        },


        // Minify and clean CSS
        cssmin: {
            options: {
                roundingPrecision: -1,
                sourceMap: false
            },
            site: {
                files: [{
                    expand: true,
                    cwd: '<%= distPath %>/css',
                    src: ['*.css', '!*.min.css'],
                    dest: '<%= distPath %>/css',
                    ext: '.min.css'
                }]
            }
        },


        // Copy
        copy: {
            KUItoJUI: {
                files: [
                    {
                        expand: true,
                        src: ['<%= KUIPath %>/dist/fonts/k-icons/*.*'],
                        dest: '<%= distPath %>/fonts/k-icons/',
                        flatten: true
                    },
                    {
                        expand: true,
                        src: ['<%= KUIPath %>/dist/js/*.*'],
                        dest: '<%= distPath %>/js/',
                        flatten: true
                    }
                ]
            }
        },


        // Shell commands
        shell: {
            updateCanIUse: {
                command: 'npm update caniuse-db'
            }
        },


        // Watch files
        watch: {
            sass: {
                files: [
                    // Kodekit UI
                    '<%= KUIPath %>/src/scss/*.scss',
                    '<%= KUIPath %>/src/scss/**/*.scss',

                    // Joomlatools UI
                    '<%= srcPath %>/scss/*.scss',
                    '<%= srcPath %>/scss/**/*.scss'
                ],
                tasks: ['sass', 'autoprefixer', 'cssmin'],
                options: {
                    interrupt: true,
                    atBegin: true
                }
            },
            js: {
                files: [
                    // Kodekit UI
                    '<%= KUIPath %>/dist/js/build/*.js'
                ],
                tasks: ['copy'],
                options: {
                    interrupt: true,
                    atBegin: true
                }
            }
        }


    });

    // The dev task will be used during development
    grunt.registerTask('default', ['shell', 'watch']);

};
