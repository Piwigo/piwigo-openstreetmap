<?php
/***********************************************
* File      :   admin_gps.php
* Project   :   piwigo-openstreetmap
* Descr     :   read GPS file
*
* Created   :   10.10.2014
*
* Copyright 2013-2014 <xbgmsharp@gmail.com>
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
************************************************/

// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Check access and exit when user status is not ok
check_status(ACCESS_ADMINISTRATOR);

// Setup plugin Language
load_language('plugin.lang', OSM_PATH);


if (isset($_POST['submit'])) {
    $uploaded_errors = array();
    $upload_file = array();

    if($_FILES['file_uploaded']['size'] != 0) {
        $upload_file = gps_upload_file($_FILES['file_uploaded']);
        if(count($upload_file['errors']) != 0)
            $uploaded_errors['file'] = $upload_file['errors'];
    } else {
        $uploaded_errors['file']['no_file'] = l10n('Specify a file to upload');
    }

    if (count($uploaded_errors) == 0) {
        $file_path = pwg_db_real_escape_string($upload_file['destination']);
        $category = pwg_db_real_escape_string($_POST['category']);
        $query="INSERT INTO ".$prefixeTable."osm_gps ( `category_id`, `path` ) VALUES ('$category', '$file_path');";
        pwg_query($query);
        array_push($page['infos'], l10n('File uploaded and synchronized'));
    } else {
        array_push($page['errors'], l10n('There have been errors. See below'));
        $template->assign('uploaded_errors', $uploaded_errors);
    }
}

function gps_upload_file($uploaded_file) {
    $uploaded_galleries_dir = PHPWG_ROOT_PATH.'_data/i/galleries/gps/';
    $uploaded_file_tmp = $uploaded_file['tmp_name'];
    $uploaded_file_name = preg_replace('/[^a-zA-Z0-9s.]/', '_', $uploaded_file['name']);
    $uploaded_file_destination = $uploaded_galleries_dir . $uploaded_file_name;
    $ext = pathinfo($uploaded_file_name);
    $ext = $ext['extension'];
    $uploaded_errors = array();
    if(!in_array($ext, array('csv', 'gpx', 'kml', 'wkt', 'topojson', 'geojson'))) {
        $uploaded_errors['upload_error'] = l10n('Extension not supported');
    } else if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
        switch ($_FILES['file_uploaded']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $uploaded_errors['upload_error'] = l10n('File exceeds the upload_max_filesize directive in php.ini');
                break;
            case UPLOAD_ERR_PARTIAL:
                $uploaded_errors['upload_error'] = l10n('File was only partially uploaded');
                break;
            case UPLOAD_ERR_NO_FILE:
                $uploaded_errors['upload_error'] = l10n('No file to upload');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $uploaded_errors['upload_error'] = l10n('Missing a temporary folder');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $uploaded_errors['upload_error'] = l10n('Failed to write file to disk');
                break;
            case UPLOAD_ERR_EXTENSION:
                $uploaded_errors['upload_error'] = l10n('File upload stopped by extension');
                break;
            default:
                $uploaded_errors['upload_error'] = l10n('Upload error');
        }
    } else if (file_exists($uploaded_file_destination)) {
        $uploaded_errors['already_exist'] = l10n('file_uploader_error_already_exist');
    } else if (!move_uploaded_file($uploaded_file_tmp, $uploaded_file_destination)) {
        $uploaded_errors['move_uploaded_file'] = l10n('Can\'t upload file to galleries directory');
    }
    $return['errors'] = $uploaded_errors;
    $return['destination'] = $uploaded_file_destination;
    return $return;
}

//Categories
$query = 'SELECT id,name,uppercats,global_rank FROM '.CATEGORIES_TABLE.';';
display_select_cat_wrapper($query, array(), 'category_gps');

