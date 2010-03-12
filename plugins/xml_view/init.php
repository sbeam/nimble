<?php

require_once('XML/Serializer.php'); // PEAR 

class XMLview {

    public static function build($rootNode, $vals=null) {
        $xsz = new XML_Serializer(array('rootName' => $rootNode));
        $xsz->serialize($vals);
        return $xsz->getSerializedData();
    }
}

