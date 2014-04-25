module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    jshint: {
      options: {
        force: true
      },
      all: ['assets/src/js/admin.js', 'assets/src/js/we-the-people.js', 'assets/src/js/tinymce.js']
    },

    uglify: {
      min: {
        files: {
          'assets/dist/js/admin.js': ['assets/src/js/admin.js'],
          'assets/dist/js/tinymce.js': ['assets/src/js/tinymce.js'],
          'assets/dist/js/we-the-people.js': ['assets/src/js/we-the-people.js']
        }
      }
    },

    compass: {
      dist: {
        options: {
          config: 'config.rb'
        }
      }
    },

    watch: {
      options: {
        livereload: true
      },
      scripts: {
        files: ['assets/src/js/*.js',],
        tasks: ['jshint', 'uglify']
      },
      styles: {
        files: ['assets/src/css/*.scss'],
        tasks: ['compass']
      },
    },

  });

  // Load tasks
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // Default task(s).
  grunt.registerTask('default', ['jshint', 'uglify', 'compass']);

};