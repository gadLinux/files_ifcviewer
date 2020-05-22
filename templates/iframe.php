<?php
/** @var array $_ */
/** @var OCP\IURLGenerator $urlGenerator */
$urlGenerator = $_['urlGenerator'];
$version = \OCP\App::getAppVersion('files_ifcviewer');

?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>xeokit BIM Viewer</title>

    <!-- BIMViewer styles -->
    <link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_ifcviewer', 'css/fontawesome-free-5.11.2-web/css/all.min.css')) ?>?v=<?php p($version) ?>" type="text/css"/>
    <link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_ifcviewer', 'css/BIMViewer.css')) ?>?v=<?php p($version) ?>" type="text/css"/>

    <!-- App style -->
    <link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_ifcviewer', 'css/style.css')) ?>?v=<?php p($version) ?>"/>

    <!-- App tooltips style -->
    <link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_ifcviewer', 'css/backdrop.css')) ?>?v=<?php p($version) ?>"/>
	<!-- Annotations style -->
    <link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_ifcviewer', 'css/annotation.css')) ?>?v=<?php p($version) ?>"/> 
    <?php if($_['minmode']):?>
<!--       <link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_ifcviewer', 'css/minmode.css')) ?>?v=<?php p($version) ?>"/>  -->
    <?php endif;?>
</head>

<body tabindex="1" class="loadingInProgress">

<div id="myViewer" class="xeokit-busy-modal-backdrop">
    <div id="myExplorer" class="active"></div>
    <div id="myContent">
        <div id="myToolbar"></div>
        <canvas id="myCanvas"></canvas>
    </div>
</div>
<canvas id="myNavCubeCanvas"></canvas>
</body>

<!-- App tooltips libraries-->
<script nonce="<?php p(\OC::$server->getContentSecurityPolicyNonceManager()->getNonce()) ?>" src="<?php p($urlGenerator->linkTo('files_ifcviewer', 'js/vendor/popper.js')) ?>?v=<?php p($version) ?>"></script>
<script nonce="<?php p(\OC::$server->getContentSecurityPolicyNonceManager()->getNonce()) ?>" src="<?php p($urlGenerator->linkTo('files_ifcviewer', 'js/vendor/tippy.js')) ?>?v=<?php p($version) ?>"></script>
<script nonce="<?php p(\OC::$server->getContentSecurityPolicyNonceManager()->getNonce()) ?>" src="<?php p($urlGenerator->linkTo('files_ifcviewer', 'js/viewer/main.js')) ?>?v=<?php p($version) ?>"></script>
<!-- Integration -->
<script nonce="<?php p(\OC::$server->getContentSecurityPolicyNonceManager()->getNonce()) ?>" src="<?php p($urlGenerator->linkTo('files_ifcviewer', 'js/viewer/iframe-communications.js')) ?>?v=<?php p($version) ?>"></script>
<script nonce="<?php p(\OC::$server->getContentSecurityPolicyNonceManager()->getNonce()) ?>" src="<?php p($urlGenerator->linkTo('files_ifcviewer', 'js/viewer/iframe-viewer.js')) ?>?v=<?php p($version) ?>"></script>

</html> 
