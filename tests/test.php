<?php

$m = new Memcached('doctrine2');
$m->addServer('localhost', 11211);

$m->set('int', 99);
$m->set(str_replace(' ', '_', 'int con spazi'), 99, 30);
$m->set('string', 'a simple string');
$m->set('array', array(11, 12));

var_dump($m->getAllKeys(), $m->get('int'), $m->get('int con spazi'));
//sleep(3);
//var_dump($m->getAllKeys(), $m->get('int'), $m->get('int con spazi'));



/*
$m2 = new Memcache();
$m2->addServer('localhost', 11211);

$m2->set('int', 99);
$m2->set('string', 'a simple string');
$m2->set('array', array(11, 12));


$keys = array();
$allSlabs = $m2->getExtendedStats('slabs');

foreach ($allSlabs as $server => $slabs) {
    foreach (array_keys($slabs) as $slabId) {
        $dump = $m2->getExtendedStats('cachedump', (int) $slabId);
        foreach ($dump as $entries) {
            if ($entries) {
                $keys = array_merge($keys, array_keys($entries));
            }
        }
    }
}
*/