<?php
require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Zend\Permissions\Acl\Acl;

class AclProcessor {
    private $data;
    private $acl;
    private $log;

    public function __construct(array $data) {
        $this->acl = new Acl();
        $this->data = $data;
        $this->log = [];
        $this->processData();
    }

    private function addResource($resource, $parent = null) {
        if ($resource) {
            foreach ($resource as $key => $value) {
                $this->acl->addResource($key, $parent);
                $this->log[] = sprintf("addResource(%s, %s);", $key, $this->logFormat($parent));
                $this->addResource($value, $key);
            }
        }
    }

    private function addRoles(array $roles) {
        if ($roles) {
            foreach ($roles as $role => $parents) {
                $this->acl->addRole($role, $parents);
                $this->log[] = sprintf("addRole(%s, %s);", $role, $this->logFormat($parents));
            }
        }
    }

    private function addRules(array $rules) {
        if ($rules) {
            foreach ($rules as $rule) {
                $action = array_shift($rule);
                call_user_func_array([$this->acl, $action], $rule);
                $this->log[] = sprintf("%s(%s);", $action, implode(", ", array_map([$this, 'logFormat'], $rule)));
            }
        }
    }

    private function processData() {
        $resources = $this->data['resources'];
        $roles = $this->data['roles'];
        $rules = $this->data['rules'];

        $this->addResource($resources);
        $this->addRoles($roles);
        $this->addRules($rules);
    }

    private function logFormat($value) {
        return $value == null ? 'null' : is_array($value) ? json_encode($value) : $value;
    }

    public function getLog() {
        return $this->log;
    }

    public function getAcl() {
        return $this->acl;
    }
}

$yamlData = Yaml::parseFile('acl.yml');
$processor = new AclProcessor($yamlData);
$acl = $processor->getAcl();

echo implode("\n", $processor->getLog());

function logFormat($value) {
    return $value == null ? 'null' : is_array($value) ? json_encode($value) : $value;
}

function isAllowed($acl, $role, $resource = null, $privilege = null) {
    $allowed = $acl->isAllowed($role, $resource, $privilege);
    echo sprintf("\nisAllowed(%s, %s, %s) = %s", $role, logFormat($resource), logFormat($privilege), $allowed ? 'YES' : 'NO');
}

// Test
echo "\n";
isAllowed($acl, 'guest', 'products', 'read');
isAllowed($acl, 'guest', 'products', 'comment');
isAllowed($acl, 'gold_member', 'posts', 'read');
isAllowed($acl, 'gold_member', 'posts', 'comment');
isAllowed($acl, 'gold_member', 'posts', 'vote');
isAllowed($acl, 'sale', 'products', 'list');
isAllowed($acl, 'sale', 'orders', 'delete');
isAllowed($acl, 'sale', 'settings');
isAllowed($acl, 'manager', 'settings');
isAllowed($acl, 'khanh', 'settings');
isAllowed($acl, 'admin', 'settings');
isAllowed($acl, 'admin', 'orders', 'delete');