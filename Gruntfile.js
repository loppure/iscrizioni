module.exports = function(grunt) {
    // Project config

    var path = {
        src: './web/assets/',
        app_styl: './web/assets/stylus/app.styl',
        dst: './web/',
        app_css: './web/app.css'
    }

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        stylus: {
            options: {
                compress: true,
                use: [
                    require("kouto-swiss")
                ]
            },
            styles: {
                files: {
                    "./web/app.css": "./web/assets/stylus/app.styl"
                }
            }
        },

        watch: {
            styles: {
                files: ['./web/assets/stylus/**/*.styl'],
                tasks: ['stylus'],
                options: {
                    spawn: false
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-stylus');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['stylus', 'watch']);
};
