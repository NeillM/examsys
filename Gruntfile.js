// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file configures Grunt tasks for Rogo, such as mimification of JavaScript.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

module.exports = function(grunt) {
  /**
   * Utility function to remanme Javascript files.
   * 
   * @param {String} destination The current destination path
   * @param {String} source The source path
   * @returns {String}
   */
  var buildName = function(destination, source) {
    destination = destination + source.replace('src/', '');
    destination = destination.replace('.js', '.min.js');
    return destination;
  }

  grunt.initConfig({
    eslint: {
      options: {
        configFile: 'testing/eslint/eslint.json'
      },
      core: {
        src: ['js/core/meta/namespaces.js', 'js/core/*.js'],
        rules: {
          'no-console': 'warn'
        }
      },
      html5: {
        src: ['js/html_questions/meta/namespaces.js', 'js/html_questions/*.js']
      },
      admin: {
        src: ['admin/**/js/src/*.js']
      },
      corejs: {
        src: ['js/src/**/*.js']
      }
    },
    uglify: {
      options: {
        mangle: false
      },
      core: {
        files: {
          'js/core.min.js': ['js/core/meta/namespaces.js', 'js/core/*.js']
        }
      },
      html5: {
        options: {
          compress: {}
        },
        files: {
          'js/html5_questions.min.js': [
            'js/html_questions/polyfill.js',
            'js/html_questions/meta/namespaces.js',
            'js/html_questions/html5.js',
            'js/html_questions/html5.question.js',
            'js/html_questions/html5.image.js',
            'js/html_questions/html5.answer.hotspot.js',
            'js/html_questions/html5.question.hotspot.js',
            'js/html_questions/html5.question.hotspot.answer.js',
            'js/html_questions/html5.question.hotspot.correction.js',
            'js/html_questions/html5.question.hotspot.edit.js',
            'js/html_questions/html5.question.hotspot.analysis.js',
            'js/html_questions/html5.question.hotspot.standardset.js',
            'js/html_questions/html5.question.hotspot.script.js',
            'js/html_questions/html5.question.hotspot.layer.js',
            'js/html_questions/html5.question.hotspot.shape.js',
            'js/html_questions/html5.question.hotspot.colourselector.js',
            'js/html_questions/html5.menu.js',
            'js/html_questions/html5.menu.item.js',
            'js/html_questions/html5.menu.button.js',
            'js/html_questions/html5.menu.group.js',
            'js/html_questions/html5.menu.filler.js',
            'js/html_questions/html5.menu.checkbox.js',
            'js/html_questions/html5.menu.hotspot.layerzone.js',
            'js/html_questions/html5.listener.js',
            'js/html_questions/html5.listener.hotspot.js'
          ]
        }
       },
      admin: {
        files: [{
          expand: true,
          cwd: 'admin/',
          src: '**/js/src/*.js',
          dest: 'admin/',
          rename: buildName
        }]
      },
      corejs: {
        files: [{
          expand: true,
          cwd: 'js/',
          src: 'src/**/*.js',
          dest: 'js/',
          rename: buildName
        }]
      }
    },
    cssmin: {
      options: {
        report: true
      },
      standard: {
        files: [{
          expand: true,
          cwd: 'css/source',
          src: '*.css',
          dest: 'css',
          ext: '.css'
        }]
      },
    },
    sprite: {
      html5canvas: {
        dest: 'js/images/combined.png',
        destCss: 'js/html_questions/html5.image.js',
        src: [
          'js/images/toolbar/*.png',
        ],
        cssTemplate: 'js/images/html5.template.js'
     }
    }
  });

  // Load plugins.
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-eslint');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-spritesmith');

  // Register tasks.
  grunt.registerTask('css', ['cssmin:standard']);
  grunt.registerTask('core', ['eslint:core', 'uglify:core']);
  grunt.registerTask('admin', ['eslint:admin', 'uglify:admin']);
  grunt.registerTask('corejs', ['eslint:corejs', 'uglify:corejs']);
  grunt.registerTask('html5', ['sprite:html5canvas', 'eslint:html5', 'uglify:html5']);
  grunt.registerTask('default', ['admin', 'css', 'core', 'html5', 'corejs']);
}
