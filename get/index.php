<?php
isset($_GET['id']) ? $id = $_GET['id'] : exit('error');

require '../tools/modelList.php';
require '../tools/modelTextures.php';
require '../tools/jsonCompatible.php';

$modelList = new modelList();
$modelTextures = new modelTextures();
$jsonCompatible = new jsonCompatible();

$id = explode('-', $id);
$modelId = (int)$id[0];
$modelTexturesId = isset($id[1]) ? (int)$id[1] : 0;

$modelName = $modelList->id_to_name($modelId);
if (!$modelName) {
    // prevent empty modelName
    $modelName = $modelList->id_to_name(1);
}

if (is_array($modelName)) {
    $modelName = $modelTexturesId > 0 ? $modelName[$modelTexturesId-1] : $modelName[0];
    $json = json_decode(file_get_contents('../model/'.$modelName.'/model.json'), 1);
} else {
    $json = json_decode(file_get_contents('../model/'.$modelName.'/model.json'), 1);
    if ($modelTexturesId > 0) {
        $modelTexturesName = $modelTextures->get_name($modelName, $modelTexturesId);
        if (isset($modelTexturesName)) $json['textures'] = is_array($modelTexturesName) ? $modelTexturesName : array($modelTexturesName);
    }
}
if (strpos($json['model'],"live2d")) {
    $textures = json_encode($json['textures']);
	$textures = str_replace('model', '../model', $textures);
	$textures = json_decode($textures, 1);
	$json['textures'] = $textures;
	$json['model'] = '../'.$json['model'];
	if (isset($json['physics'])) $json['physics'] = '../'.$json['physics'];
	if (isset($json['motions'])) {
        $motions = json_encode($json['motions']);
        $motions = str_replace('model', '../model', $motions);
        $motions = json_decode($motions, 1);
        $json['motions'] = $motions;
	}
	
	if (isset($json['expressions'])) {
	    $expressions = json_encode($json['expressions']);
	    $expressions = str_replace('model', '../model', $expressions);
	    $expressions = json_decode($expressions, 1);
	    $json['expressions'] = $expressions;
	}
}
else {
    $textures = preg_filter('/^/', '../model/'.$modelName.'/', $json['textures']);
    $json['textures'] = $textures;

    $json['model'] = '../model/'.$modelName.'/'.$json['model'];
    if (isset($json['pose'])) $json['pose'] = '../model/'.$modelName.'/'.$json['pose'];
    if (isset($json['physics'])) $json['physics'] = '../model/'.$modelName.'/'.$json['physics'];

    if (isset($json['motions'])) {
        $motions = json_encode($json['motions']);
        if (strpos($motions, 'sound') !== false) {
            $motions = preg_replace('/("sound":\s*")/', '$1../model/'.$modelName.'/', $motions);
        }
        $motions = preg_replace('/("file":\s*")/', '$1../model/'.$modelName.'/', $motions);
        $motions = json_decode($motions, 1);
        $json['motions'] = $motions;
    }

    if (isset($json['expressions'])) {
        $expressions = json_encode($json['expressions']);
        $expressions = preg_replace('/("file":\s*")/', '$1../model/'.$modelName.'/', $expressions);
        $expressions = json_decode($expressions, 1);
        $json['expressions'] = $expressions;
    }
}

header("Content-type: application/json");
echo $jsonCompatible->json_encode($json);
