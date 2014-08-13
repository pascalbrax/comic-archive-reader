<?php

error_reporting(-1);
$GLOBALS['comicsDir'] = "comics";
$GLOBALS['imagesDir'] = "comic-images";

// used to filter out . and .. from scandir results
function notJustDots($x)
{
    return $x != "." && $x != "..";
}

function clearImages()
{
    shell_exec("rm -rf {$GLOBALS['imagesDir']}/*");
}

function unzipArchive($comic, $archive)
{
    $fileType = substr($archive, -3);
    switch ($fileType) {
        case "cbz":
            shell_exec("unzip {$GLOBALS['comicsDir']}/{$comic}/{$archive} -d {$GLOBALS['imagesDir']}/");
            break;
        case "cbr":
            shell_exec("./unrar e {$GLOBALS['comicsDir']}/{$comic}/{$archive} {$GLOBALS['imagesDir']}/");
            break;
        default:
            return false;
            break;
    }
    return true;
}

function scanIssues($series)
{
    $rawList = scandir("{$GLOBALS['comicsDir']}/{$series}/");
    return array_filter($rawList, "notJustDots");
}

function scanImages($issue)
{
    $rootDir = $GLOBALS['imagesDir'];
    $rawList = scandir("{$rootDir}");
    $filteredList = array_filter($rawList, "notJustDots");
    $limit = 3;
    // loop over the results if it's only one item long
    // this generally indicates a folder within the archive that holds the images
    while ($limit > 0 && count($filteredList) == 1) {
        $rootDir .= "/" . array_pop($filteredList);
        $filteredList = array_filter(scandir($rootDir), "notJustDots");
        $limit--;
    }
    $gallery = array();
    foreach($filteredList as $imgName) {
        $gallery[] = "{$rootDir}/{$imgName}";
    }
    return $gallery;
}

// if this is a GET request with parameters, it's AJAX
if (count($_GET) > 0) {
    $action = $_GET['action'];
    switch ($action) {
        case "issueList":
            $series = $_GET['series'];
            $issues = scanIssues($series);
            echo json_encode(array("result" => "success", "issues" => $issues));
            break;
        case "expandIssue":
            $series = $_GET['series'];
            $issue = $_GET['issue'];
            clearImages();
            unzipArchive($series, $issue);
            $images = scanImages();
            echo json_encode(array("result" => "success", "library" => $images));
            break;
        default:
            echo json_encode(array("result" => "failure"));
    }
    exit;
}

$comics = array_filter(scandir($comicsDir), "notJustDots");

?>

<html>
    <head>
        <script type="application/javascript" src="js/jquery-1.6.1.min.js"></script>
        <script type="application/javascript" src="js/jquery.prettyPhoto.js"></script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        
        <script type="application/javascript">
            var currentGallery = [];
            
            function fetchIssues(series) {
                $.getJSON("", {action: "issueList", series: series}, function(json) {
                    if (json.result == "success") {
                        renderIssues(json.issues);
                    }
                });
            }
            
            function renderIssues(issues) {
                var html = "<option>Choose an issue</option>";
                $.each(issues, function (i, v) {
                    html += "<option>" + v + "</option>";
                });
                $('#issuePicker').html(html);
            }
            
            function fetchLibrary(series, issue) {
                $('#openGallery').hide();
                $.getJSON("", {action: "expandIssue", series: series, issue: issue}, function(json) {
                    if (json.result == "success") {
                        currentGallery = json.library;
                        $('#openGallery').show();
                    }
                });
            }
            
            $(document).ready(function() {
                $.fn.prettyPhoto({ social_tools: false });
                
                $('#comicPicker').change(function() {
                    var series = $(this).val();
                    if (series == "") {
                        return false;
                    }
                    fetchIssues(series);
                });
                
                $('#issuePicker').change(function() {
                    var series = $('#comicPicker').val(),
                        issue = $('#issuePicker').val();
                    
                    if (series == "") {
                        return false;
                    }
                    fetchLibrary(series, issue);
                });
                
                $('#openGallery').click(function(e) {
                    e.preventDefault();
                    $.prettyPhoto.open(currentGallery, [], []);
                });
            });
        </script>
    </head>
    <body>
        
        Choose a comic:<br/>
        <select id="comicPicker">
            <option>Choose a series</option>
            <?php
                foreach($comics as $comic) {
                    echo "<option>" . $comic . "</option>";
                }
            ?>
        </select><br/>
        <select id="issuePicker">
            <option></option>
        </select><br/>
        <a style="display:none;" href="#" id="openGallery">Open Gallery</a>
    </body>
</html>