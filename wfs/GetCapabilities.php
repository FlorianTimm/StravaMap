<?PHP
header('Content-type: application/xml');
include 'layer.php';

echo <<<EOF
<?xml version='1.0' encoding='UTF-8'?>
<WFS_Capabilities version="2.0.0" xmlns="http://www.opengis.net/wfs/2.0" xmlns:wfs="http://www.opengis.net/wfs/2.0" xmlns:ows="http://www.opengis.net/ows/1.1" xmlns:ogc="http://www.opengis.net/ogc" xmlns:fes="http://www.opengis.net/fes/2.0" xmlns:gml="http://www.opengis.net/gml" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.opengis.net/wfs/2.0 http://schemas.opengis.net/wfs/2.0/wfs.xsd">
  <ows:ServiceIdentification>
    <ows:Title>SportWFS</ows:Title>
    <ows:Abstract></ows:Abstract>
    <ows:ServiceType codeSpace="http://www.opengeospatial.org/">WFS</ows:ServiceType>
    <ows:ServiceTypeVersion>2.0.0</ows:ServiceTypeVersion>
    <ows:AccessConstraints></ows:AccessConstraints>
  </ows:ServiceIdentification>
  <ows:ServiceProvider>
    <ows:ProviderName>Florian Timm</ows:ProviderName>
  </ows:ServiceProvider>
  <ows:OperationsMetadata>
    <ows:Operation name="GetCapabilities">
      <ows:DCP>
        <ows:HTTP>
          <ows:Get xlink:href="
EOF . Config::$url . <<<EOF
wfs/index.php?"/>
        </ows:HTTP>
      </ows:DCP>
      <ows:Parameter name="AcceptVersions">
        <ows:AllowedValues>
          <ows:Value>2.0.0</ows:Value>
        </ows:AllowedValues>
      </ows:Parameter>
      <ows:Parameter name="AcceptFormats">
        <ows:AllowedValues>
          <ows:Value>text/xml</ows:Value>
        </ows:AllowedValues>
      </ows:Parameter>
      <ows:Parameter name="Sections">
        <ows:AllowedValues>
          <ows:Value>ServiceIdentification</ows:Value>
          <ows:Value>ServiceProvider</ows:Value>
          <ows:Value>OperationsMetadata</ows:Value>
          <ows:Value>FeatureTypeList</ows:Value>
          <ows:Value>Filter_Capabilities</ows:Value>
        </ows:AllowedValues>
      </ows:Parameter>
    </ows:Operation>
    <ows:Operation name="DescribeFeatureType">
      <ows:DCP>
        <ows:HTTP>
          <ows:Get xlink:href="
EOF . Config::$url . <<<EOF
          wfs/index.php?"/>
          <ows:Post xlink:href="
EOF . Config::$url . <<<EOF
wfs/index.php"/>
        </ows:HTTP>
      </ows:DCP>
    </ows:Operation>
    <ows:Operation name="ListStoredQueries">
      <ows:DCP>
        <ows:HTTP>
          <ows:Get xlink:href="
EOF . Config::$url . <<<EOF
wfs/index.php?"/>
          <ows:Post xlink:href="
EOF . Config::$url . <<<EOF
wfs/index.php"/>
        </ows:HTTP>
      </ows:DCP>
    </ows:Operation>
    <ows:Operation name="DescribeStoredQueries">
      <ows:DCP>
        <ows:HTTP>
          <ows:Get xlink:href="
EOF . Config::$url . <<<EOF
wfs/index.php?"/>
          <ows:Post xlink:href="
EOF . Config::$url . <<<EOF
wfs/index.php"/>
        </ows:HTTP>
      </ows:DCP>
    </ows:Operation>
    <ows:Operation name="GetFeature">
      <ows:DCP>
        <ows:HTTP>
          <ows:Get xlink:href="
EOF . Config::$url . <<<EOF
wfs/index.php?"/>
          <ows:Post xlink:href="
EOF . Config::$url . <<<EOF
wfs/index.php"/>
        </ows:HTTP>
      </ows:DCP>
    </ows:Operation>
    <ows:Parameter name="version">
      <ows:AllowedValues>
        <ows:Value>2.0.0</ows:Value>
      </ows:AllowedValues>
    </ows:Parameter>
    <ows:Parameter name="srsName">
      <ows:AllowedValues>
        <ows:Value>EPSG:4326</ows:Value>
      </ows:AllowedValues>
    </ows:Parameter>
    <ows:Parameter name="outputFormat">
      <ows:AllowedValues>
        <ows:Value>application/gml+xml; version=2.1</ows:Value>
        <ows:Value>application/geo+json</ows:Value>
      </ows:AllowedValues>
    </ows:Parameter>
    <ows:Constraint name="ImplementsSimpleWFS">
      <ows:NoValues/>
      <ows:DefaultValue>TRUE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsBasicWFS">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsTransactionalWFS">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsLockingWFS">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="KVPEncoding">
      <ows:NoValues/>
      <ows:DefaultValue>TRUE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="XMLEncoding">
      <ows:NoValues/>
      <ows:DefaultValue>TRUE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="SOAPEncoding">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsInheritance">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsRemoteResolve">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsResultPaging">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsStandardJoins">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsSpatialJoins">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsTemporalJoins">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ImplementsFeatureVersioning">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ManageStoredQueries">
      <ows:NoValues/>
      <ows:DefaultValue>FALSE</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="ResolveLocalScope">
      <ows:NoValues/>
      <ows:DefaultValue>*</ows:DefaultValue>
    </ows:Constraint>
    <ows:Constraint name="QueryExpressions">
      <ows:AllowedValues>
        <ows:Value>wfs:Query</ows:Value>
        <ows:Value>wfs:StoredQuery</ows:Value>
      </ows:AllowedValues>
    </ows:Constraint>
  </ows:OperationsMetadata>
  <FeatureTypeList>
EOF;

/*
$rs = $db->query('SELECT * FROM my_table LIMIT 0');
for ($i = 0; $i < $rs->columnCount(); $i++) {
    $col = $rs->getColumnMeta($i);
    $columns[] = $col['name'];
}
print_r($columns);
*/

foreach ($layers as $key => $value) {
  $srid = 0;
  $sql = 'SELECT ST_SRID(' . $value[1] . ') srid FROM ' . $value[0] . ' LIMIT 1';
  //echo $sql;
  foreach ($db->query($sql) as $row) {
    $srid = $row['srid'];
  }
  echo '<FeatureType>
			<Name xmlns:gis="http://florian-timm.de/gis">gis:' . $key . '</Name>
			<Title>gis:' . $key . '</Title>
		  <DefaultCRS>EPSG:' . $srid . '</DefaultCRS>
		  <OutputFormats>
			<Format>application/gml+xml; version=2.1</Format>
		  </OutputFormats>
		  <ows:WGS84BoundingBox>
			<ows:LowerCorner>-15.76766 27.738752</ows:LowerCorner>
			<ows:UpperCorner>14.488132 54.736385</ows:UpperCorner>
		  </ows:WGS84BoundingBox>
		</FeatureType>';
}

echo <<<EOF
  </FeatureTypeList>
</WFS_Capabilities> 
EOF;
?>