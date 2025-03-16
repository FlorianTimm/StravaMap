<?php
header('Content-type: application/xml');
include 'layer.php';

echo <<<EOF
<?xml version='1.0' encoding='UTF-8'?>
<schema xmlns="http://www.w3.org/2001/XMLSchema" xmlns:gml="http://www.opengis.net/gml/3.2" xmlns:gis="http://florian-timm.de/gis" xmlns:xlink="http://www.w3.org/1999/xlink" targetNamespace="http://florian-timm.de/gis" elementFormDefault="qualified" attributeFormDefault="unqualified">
  <import namespace="http://www.opengis.net/gml/3.2" schemaLocation="http://schemas.opengis.net/gml/3.2.1/gml.xsd"/>
EOF;


foreach ($layers as $key=>$value) {
    $rs = $db->query('SELECT *, ST_GeometryType('.$value[1].') geometryType FROM '.$value[0].' LIMIT 1');
    
    echo '
    <element name="'.$key.'" substitutionGroup="gml:AbstractFeature">
        <complexType>
        <complexContent>
            <extension base="gml:AbstractFeatureType">
            <sequence>';
            /*
                <element name="name" minOccurs="0" type="string"/>
                <element name="velocity" minOccurs="0" type="float"/>
                <element name="kontakt" minOccurs="0" type="string"/>
                <element name="anmerkungen" minOccurs="0" type="string"/>
                <element name="geom" minOccurs="0" type="gml:PointPropertyType"/>*/
            for ($i = 0; $i < $rs->columnCount()-1; $i++) {
                $col = $rs->getColumnMeta($i);
                if ($col["native_type"] != "GEOMETRY") {
                echo '<element name="' . $col['name'] .'" minOccurs="0" type="';
                switch ($col["native_type"]) {
                    case "LONGLONG":
                    case "LONG":
                        echo "number";
                        break;
                    case "FLOAT":
                        echo "float";
                        break;
                    case "TINY":
                        echo "boolean";
                        break;
                    case "DATETIME":
                        echo "datetime";
                        break;
                    case "VAR_STRING":
                    case "BLOB":
                        echo "string";
                        break;
                }
                echo '" />';
                }
                }
                 echo '<element name="geom" minOccurs="0" type="gml:';
                 $row = $rs->fetch();
            
                  $geom = $row['geometryType'];
        if ($geom == "LINESTRING") {
            echo "LineString";
        } else if ($geom == "POINT") {
            echo "Point";
            }
                 
                 echo 'PropertyType"/>';
                //var_dump($col);
/*
array(7) {
  ["native_type"]=>
  string(8) "LONGLONG"
  ["pdo_type"]=>
  int(2)
  ["flags"]=>
  array(1) {
    [0]=>
    string(8) "not_null"
  }
  ["table"]=>
  string(13) "strava_points"
  ["name"]=>
  string(8) "activity"
  ["len"]=>
  int(20)
  ["precision"]=>
  int(0)
}
*/
     echo '       </sequence>
            </extension>
        </complexContent>
        </complexType>
    </element>';
}
echo '</schema>';
?>
