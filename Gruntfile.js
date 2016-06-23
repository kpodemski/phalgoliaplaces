module.exports = function(grunt) {
    var package = require('./package.json'),
        options = package.options,
        moduleName = options.moduleName;

    grunt.initConfig({
        compress: {
            main: {
                options: {
                    archive: moduleName + '.zip'
                },
                files: [
                    {src: ['controllers/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['classes/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['models/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['docs/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['override/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['logs/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['vendor/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['translations/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['upgrade/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['init/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: ['views/**'], dest: moduleName + '/', filter: 'isFile'},
                    {src: 'config.xml', dest: moduleName + '/'},
                    {src: 'index.php', dest: moduleName + '/'},
                    {src: moduleName + '.php', dest: moduleName + '/'},
                    {src: 'logo.png', dest: moduleName + '/'},
                    {src: 'logo.gif', dest: moduleName + '/'},
                    {src: 'LICENSE.md', dest: moduleName + '/'},
                    {src: 'CONTRIBUTORS.md', dest: moduleName + '/'},
                    {src: 'README.md', dest: moduleName + '/'}
                ]
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-compress');

    grunt.registerTask('default', ['compress']);
};