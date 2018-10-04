yaml = require('yamljs');
fs   = require('fs');

var doc = yaml.load('acl.yml');
console.log(doc);