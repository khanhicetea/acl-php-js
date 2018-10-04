yaml = require('yamljs');
fs   = require('fs');
Acl = require('acljs');

class AclProcessor {
    constructor(data) {
        this.data = data;
        this.acl = new Acl();
        this.processData();
    }

    addResource(resource, parent = null) {
        if (resource) {
            var that = this;
            Object.keys(resource).map(function(key, index) {
                that.acl.addResource(key, parent);
                console.log("addResource", key , parent);
                that.addResource(resource[key], key);
            })
        }
    }

    addRoles(roles) {
        if (roles) {
            var that = this;
            Object.keys(roles).map(function(key, index) {
                that.acl.addRole(key, roles[key]);
                console.log("addRole", key, roles[key]);
            })
        }
    }

    addRules(rules) {
        if (rules) {
            var that = this;
            rules.map((rule, key) => {
                var access = rule[0];
                switch (access) {
                    case 'allow':
                      that.acl.allow(rule[1], rule[2], rule[3]);
                      console.log('allow', rule[1], rule[2], rule[3]);
                      break;
              
                    case 'deny':
                      that.acl.deny(rule[1], rule[2], rule[3]);
                      console.log('deny', rule[1], rule[2], rule[3]);
                      break;
                }
            })
        }
    }

    processData() {
        this.addResource(this.data.resources);
        this.addRoles(this.data.roles);
        this.addRules(this.data.rules);
    }

    getAcl() {
        return that.acl;
    }
}

var data = yaml.load('acl.yml');
// console.log(data)

var processor = new AclProcessor(data);
var acl = processor.acl;

function isAllowed(acl, role, resource = null, privilege = null) {
    allowed = acl.isAllowed(role, resource, privilege);
    console.log("isAllowed", role, resource, privilege, allowed ? 'YES' : 'NO');
}

// Test
console.log('\n');
isAllowed(acl, 'guest', 'products', 'read');
isAllowed(acl, 'guest', 'products', 'comment');
isAllowed(acl, 'gold_member', 'posts', 'read');
isAllowed(acl, 'gold_member', 'posts', 'comment');
isAllowed(acl, 'gold_member', 'posts', 'vote');
isAllowed(acl, 'sale', 'products', 'list');
isAllowed(acl, 'sale', 'orders', 'delete');
isAllowed(acl, 'sale', 'settings');
isAllowed(acl, 'manager', 'settings');
isAllowed(acl, 'khanh', 'settings');
isAllowed(acl, 'admin', 'settings');
isAllowed(acl, 'admin', 'orders', 'delete');