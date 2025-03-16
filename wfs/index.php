<?PHP
if ($_GET["REQUEST"] == "DescribeFeatureType") {
    include 'DescribeFeatureType.php';
} elseif ($_GET["REQUEST"] == "ListStoredQueries") {
} elseif ($_GET["REQUEST"] == "DescribeStoredQueries") {
} elseif ($_GET["REQUEST"] == "GetFeature") {
    include 'GetFeature.php';
} else /*($_GET["REQUEST"] == "GetCapabilities")*/ {
    include 'GetCapabilities.php';
}

?>
