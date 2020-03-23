<?php
  /** @var array $_ */
  /** @var OCP\IURLGenerator $urlGenerator */
  $urlGenerator = $_['urlGenerator'];
  $version = \OC::$server->getAppManager()->getAppVersion('files_ifcviewer');
?>

{
	"id": <?php p($_['id']) ?>,
	"name": "19 rue Marc Antoine Petit 69002 Lyon",
	"creator": {
		"id": 83,
		"email": "demo@bimdata.io",
		"company": "",
		"firstname": "Démo",
		"lastname": "BIMData",
		"created_at": "2018-03-02T12:35:34Z",
		"updated_at": "2019-03-30T11:07:29Z",
		"cloud_role": 50,
		"project_role": 25,
		"provider": "bimdataconnect"
	},
	"status": "C",
	"source": "UPLOAD",
	"created_at": "2018-03-02T16:41:04Z",
	"updated_at": "2019-11-22T17:29:55Z",
	"document_id": 1,
	"document": {
		"id": 1,
		"parent": 185,
		"parent_id": 185,
		"creator": 83,
		"project": 100,
		"name": "19 rue Marc Antoine Petit 69002 Lyon",
		"file_name": "<?php p($_['fileName']) ?>",
		"description": null,
		"file": "http://localhost:8080/remote.php/webdav/<?php p($_['fileName']) ?>",
		"size": 29255229,
		"created_at": "2018-03-02T16:41:04Z",
		"updated_at": "2019-03-30T10:51:55Z",
		"ifc_id": <?php p($_['id']) ?>
	},
	"structure_file": "http://localhost:8080/remote.php/webdav/<?php p($_['fileName']) ?>_structure.json",
	"systems_file": "http://localhost:8080/remote.php/webdav/<?php p($_['fileName']) ?>_systems.json",
	"map_file": "http://localhost:8080/remote.php/webdav/<?php p($_['fileName']) ?>_map.json",
	"gltf_file": "http://localhost:8080/remote.php/webdav/<?php p($_['fileName']) ?>",
	"bvh_tree_file": null,
	"viewer_360_file": "http://localhost:8080/remote.php/webdav/<?php p($_['fileName']) ?>_a360.gltf",
	"xkt_file": "http://localhost:8080/remote.php/webdav/<?php p($_['fileName']) ?>.xkt",
	"project_id": 100,
	"errors": null,
	"warnings": null	
}