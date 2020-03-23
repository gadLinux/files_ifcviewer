<?php
namespace OCA\Files_Ifcviewer\Controller;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\IURLGenerator;
use OCP\IRequest;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;
use OCP\Files\FileInfo;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\ILogger;
use OC\Files\Utils\Scanner;
use OC\Files\Storage;

class ApiController extends Controller {
    private $userId;

	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var Folder */
	private $userFolder;
	
	public function __construct($AppName, 
	                            IRequest $request,
	                            IURLGenerator $urlGenerator,
	                            Folder $userFolder,  
	                            $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->urlGenerator = $urlGenerator;
		$this->userFolder = $userFolder;
	}

	/**
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 * 
	 * @param bool $minmode
	 * @return TemplateResponse	 
	 */
	public function serve() {
	    return new DataResponse("{}");
	}
	/**
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param bool $minmode
	 * @return TemplateResponse
	 */
	public function structure(string $cloudid, string $projectid, string $ifcid) {
	    return new JSONResponse(json_decode ("{}"));
	}
	
 
	/**
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param bool $minmode
	 * @return TemplateResponse
	 */
	public function ifc(string $cloudid, string $projectid, string $ifcid) {
	    try {
	        $file = $this->userFolder->getById($ifcid);
	        if (count($file) < 1 || $file[0] instanceof Folder) {
	            throw new NotFoundException();
	        }
	    } catch (NotFoundException $e) {
	        return new DataResponse(['message' => 'File not found.'], Http::STATUS_NOT_FOUND);
	    } catch (\Exception $e) {
	        return new DataResponse([], Http::STATUS_BAD_REQUEST);
	    }
	    // Based on https://github.com/nextcloud/workflow_pdf_converter/blob/master/lib/BackgroundJobs/Convert.php
	    /** @var OC\Files\Node\File $fileNode */
	    $fileNode = $file[0];
	    
	    $mimeType = $fileNode->getMimetype();
	    
	    $dir = dirname($fileNode->getPath());
	    $fileName = $fileNode->getName();
	    
	    $fileNameWithoutExtension = substr($fileName, 0, strlen($filename) - (1+strlen($fileNode->getExtension())));
	    $newBaseFilePath = $dir . '/' . $fileNameWithoutExtension;
	    $view = new \OC\Files\View($dir);
	    $mountPoint = $view->resolvePath("/");
	    $subdir = implode('/', array_slice(explode('/', $mountPoint[1], 10),1));
	    $fileSystemBaseFileName=$mountPoint[0]->getLocalFile($mountPoint[1] . '/' . $fileNameWithoutExtension);
	    
	    //$fileSize = $view->filesize($fileNode->getName());
	    //$fileSystemPath = $view->getAbsolutePath($fileNode->getName());

	    
	    //$mountPoint[0]->
// 	    //
// 	    $filePath = $fileNode->getInternalPath();
// 	    $fileNameWithoutExtension = substr($fileName, 0, strlen($filename) - (1+strlen($fileNode->getExtension())));
// 	    $pathSegments = explode('/', $filePath, 4);

	    //\OC\Files\Filesystem::init($mountPoint . '/files/' .$fileNameWithoutExtension . '-files');

//	    $basename = '/' . $pathSegments[1] . '/' .$fileNameWithoutExtension . '-files';
// 	    $tmpPath = $view->toTmpFile($basename);
	    
// 	    $defaultParameters = ' -env:UserInstallation=file://' . escapeshellarg($tmpDir . '/nextcloud-' . $this->config->getSystemValue('instanceid') . '/') . ' --headless --nologo --nofirststartwizard --invisible --norestore --convert-to pdf --outdir ';
// 	    $clParameters = $this->config->getSystemValue('preview_office_cl_parameters', $defaultParameters);
	    if($mimeType == 'model/gltf-binary') {
    	    $command = '/usr/local/bin/gltf2xkt'; // Get this to config $this->config->getSystemValue('gltf2xkt_path', null);
    	    $commandParameters = ' -s ' . escapeshellarg($fileSystemBaseFileName.'.'.$fileNode->getExtension()) . ' -o ' . escapeshellarg($fileSystemBaseFileName.'.xkt');
    	    $exec = $command . $commandParameters;
    	    $exitCode = 0;
    	    exec($exec, $out, $exitCode);
    	    if ($exitCode !== 0) {
    	        $this->logger->error("could not convert {file}, reason: {out}",
    	            [
    	                'app' => 'workflow_ifc_converter',
    	                'file' => $fileNode->getPath(),
    	                'out' => $out
    	            ]
    	            );
    	        return;
    	    }
    	    
    	    $directory = $fileNode->getParent();
    //	    $directoryList = $directory->getDirectoryListing();
    	    //$storage = $directory->getStorage();
    // 	    $scanner->scanFile($newBaseFilePath.'.xkt');
    	    
    	    try {
    	        $scanner = new Scanner(
    	            $this->userId,
    	            null,
    	            \OC::$server->query(IEventDispatcher::class),
    	            \OC::$server->getLogger()
    	            );
    	        $scanner->scan($dir);
    	    } catch (\Exception $e) {
    	        $this->logger->logException($e, ['app' => 'files']);
    	    }
    	    
    // 	    $storage->
    	    if($directory->nodeExists($newBaseFilePath.'.xkt')){
    	        $xktfileInfo = $view->getFileInfo($newBaseFilePath.'.xkt');
    	        
    	    }
	   }
	   $responseModel = [
 	        "id" => $ifcid,
 	       "name" => $fileNameWithoutExtension,
        	"creator" => [
        		"id" => 83,
        		"email" => "gaguilar@level2crm.com",
        		"company" => "Level2 CRM",
        		"firstname" => "Gonzalo",
        		"lastname" => "Aguilar Delgado",
        		"created_at" => "2018-03-02T12:35:34Z",
        		"updated_at" => "2019-03-30T11:07:29Z",
        		"cloud_role" => 50,
        		"project_role" => 25,
        		"provider" => "bimdataconnect"
        	],
        	"status" => "C",
        	"source" => "UPLOAD",
        	"created_at" => "2018-03-02T16:41:04Z",
        	"updated_at" => "2019-11-22T17:29:55Z",
        	"document_id" => 1,
        	"document" => [
        		"id" => 1,
        		"parent" => 185,
        		"parent_id" => 185,
        		"creator" => 83,
        		"project" => 100,
        	    "name" => $fileNameWithoutExtension,
        	    "file_name" => $fileName,
        		"description" => null,
        	    "file" => "/remote.php/webdav/".$subdir."/".$fileName,
        		"size" => 29255229,
        		"created_at" => "2018-03-02T16:41:04Z",
        		"updated_at" => "2019-03-30T10:51:55Z",
        		"ifc_id" => $ifcid
        	],
	       "structure_file" => "/remote.php/webdav/".$subdir."/".$fileNameWithoutExtension."_structure.json",
	       "systems_file" => "/remote.php/webdav/".$subdir."/".$fileNameWithoutExtension."_systems.json",
	       "map_file" => "/remote.php/webdav/".$subdir."/".$fileNameWithoutExtension."_map.json",
	       "gltf_file" => "/remote.php/webdav/".$subdir."/".$fileName,
        	"bvh_tree_file" => null,
	       "viewer_360_file" => "/remote.php/webdav/".$subdir."/".$fileNameWithoutExtension."_a360.gltf",
	       "xkt_file" => "/remote.php/webdav/".$subdir."/".$fileNameWithoutExtension.".xkt",
        	"project_id" => 100,
        	"errors" => null,
        	"warnings" => null	
 	   ];
 	   
 	   $response = new JSONResponse($responseModel);
//  	   $response->cacheFor(3600);
       return $response;
//	    return new JSONResponse(json_decode ("{ \"id\": 78, \"name\": \"19 rue Marc Antoine Petit 69002 Lyon\", \"creator\": { \"id\": 83, \"email\": \"demo@bimdata.io\", \"company\": \"\", \"firstname\": \"Démo\", \"lastname\": \"BIMData\", \"created_at\": \"2018-03-02T12:35:34Z\", \"updated_at\": \"2019-03-30T11:07:29Z\", \"cloud_role\": 50, \"project_role\": 25, \"provider\": \"bimdataconnect\" }, \"status\": \"C\", \"source\": \"UPLOAD\", \"created_at\": \"2018-03-02T16:41:04Z\", \"updated_at\": \"2019-11-22T17:29:55Z\", \"document_id\": 1, \"document\": { \"id\": 1, \"parent\": 185, \"parent_id\": 185, \"creator\": 83, \"project\": 100, \"name\": \"19 rue Marc Antoine Petit 69002 Lyon\", \"file_name\": \"Duplex_A_20110907_optimized.gltf\", \"description\": null, \"file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf\", \"size\": 29255229, \"created_at\": \"2018-03-02T16:41:04Z\", \"updated_at\": \"2019-03-30T10:51:55Z\", \"ifc_id\": 78 }, \"structure_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf_structure.json\", \"systems_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf_systems.json\", \"map_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf_map.json\", \"gltf_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf\", \"bvh_tree_file\": null, \"viewer_360_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf_a360.gltf\", \"xkt_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf.xkt\", \"project_id\": 100, \"errors\": null, \"warnings\": null }"));
	}
	
	/**
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param bool $minmode
	 * @return TemplateResponse
	 */
	public function ifclist(string $cloudid, string $projectid) {
	    return new JSONResponse(json_decode ("[{}]"));
	}
	
}
