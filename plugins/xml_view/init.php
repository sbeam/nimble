<?php

require_once('XML/Serializer.php'); // PEAR 

class XMLview {

    public static function build($rootNode, $vals=null) {
        $xsz = new XML_Serializer(array('rootName' => $rootNode, 'mode' => 'simplexml'));
        $xsz->serialize($vals);
        $xml = $xsz->getSerializedData();
        Nimble::log($xml, PEAR_LOG_DEBUG);
        return $xml;
    }
}

