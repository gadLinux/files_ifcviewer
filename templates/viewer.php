<?php
//script('files_ifcviewer','annotation-binding-script');
//FIXME: scape request params
$iframe_url = 'iframe?file='.$_GET['file'].'&projectId='.$_GET['projectId'].'&modelId='.$_GET['modelId'].'&dataDir='.$_GET['dataDir'];
?>
<iframe id="ifcframe" style="width:100%;height:100%;display:block;position:absolute;top:0;z-index:1041;margin-top:50px" src="<?php echo $iframe_url; ?>" allowfullscreen="true"/>


