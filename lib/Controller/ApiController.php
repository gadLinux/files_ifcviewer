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

class ApiController extends Controller
{

    private $userId;

    /** @var IURLGenerator */
    private $urlGenerator;

    /** @var Folder */
    private $userFolder;

    public function __construct($AppName, IRequest $request, IURLGenerator $urlGenerator, Folder $userFolder, $UserId)
    {
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
    public function serve()
    {
        
        $responseModel = [
            "projects" => [[
                "id" => "123",
                "name" => "123",
            ]],
        ];
        $response = new JSONResponse($responseModel);
        // $response->cacheFor(3600);
        return $response;
    }
    
    /**
     *
     * @PublicPage
     * @NoCSRFRequired
     *
     * @param bool $minmode
     * @return TemplateResponse
     */
    public function serveProjectInfo(string $projectid)
    {
        
        $responseModel = [
            "id"=> $projectid,
            "name"=> "Duplex",
            "models"=> [
                [
                    "id"=> "duplex",
                    "name"=> "Modelo Duplex"
                ]
            ],
            "viewerConfigs"=> [
                "cameraNear"=> "0.05",
                "cameraFar"=> "3000.0",
                "saoEnabled"=> "true",
                "saoBias"=> "0.5",
                "saoIntensity"=> "0.5",
                "saoScale"=> "1200.0",
                "saoKernelRadius"=> "100"
            ],
            "viewerContent"=> [
                "modelsLoaded"=> [
                    "duplex"
                ]
            ]
        ];
        $response = new JSONResponse($responseModel);
        // $response->cacheFor(3600);
        return $response;
    }

    /**
     *
     * @PublicPage
     * @NoCSRFRequired
     *
     * @param bool $minmode
     * @return TemplateResponse
     */
    public function structure(string $cloudid, string $projectid, string $ifcid)
    {
        return new JSONResponse(json_decode("{}"));
    }

    /**
     *
     * @PublicPage
     * @NoCSRFRequired
     *
     * @param bool $minmode
     * @return TemplateResponse
     */
    public function ifc(string $cloudid, string $projectid, string $ifcid)
    {
        try {
            $file = $this->userFolder->getById($ifcid);
            if (count($file) < 1 || $file[0] instanceof Folder) {
                throw new NotFoundException();
            }
        } catch (NotFoundException $e) {
            return new DataResponse([
                'message' => 'File not found.'
            ], Http::STATUS_NOT_FOUND);
        } catch (\Exception $e) {
            return new DataResponse([], Http::STATUS_BAD_REQUEST);
        }
        // Based on https://github.com/nextcloud/workflow_pdf_converter/blob/master/lib/BackgroundJobs/Convert.php
        /** @var OC\Files\Node\File $fileNode */
        $fileNode = $file[0];
        $mimeType = $fileNode->getMimetype();
        $fileName = $fileNode->getName();
        $checksum = $fileNode->getChecksum();
        $hash = $fileNode->hash("md5");

        $ext = pathinfo($fileNode->getPath());
        $versionedFileName = $ext['filename']."-v".$hash;
        
        $newBaseFilePath = $ext['dirname'].'/'.$ext['filename'];
        $view = new \OC\Files\View($ext['dirname']);
        $mountPoint = $view->resolvePath("/");
        $subdir = implode('/', array_slice(explode('/', $mountPoint[1], 10), 1));
        $fileSystemBaseFileName = $mountPoint[0]->getLocalFile($mountPoint[1] . '/' . $ext['basename']);

        // $fileSize = $view->filesize($fileNode->getName());
        // $fileSystemPath = $view->getAbsolutePath($fileNode->getName());

        // $mountPoint[0]->
        // //
        // $filePath = $fileNode->getInternalPath();
        // $fileNameWithoutExtension = substr($fileName, 0, strlen($filename) - (1+strlen($fileNode->getExtension())));
        // $pathSegments = explode('/', $filePath, 4);

        // \OC\Files\Filesystem::init($mountPoint . '/files/' .$fileNameWithoutExtension . '-files');

        // $basename = '/' . $pathSegments[1] . '/' .$fileNameWithoutExtension . '-files';
        // $tmpPath = $view->toTmpFile($basename);

        // $defaultParameters = ' -env:UserInstallation=file://' . escapeshellarg($tmpDir . '/nextcloud-' . $this->config->getSystemValue('instanceid') . '/') . ' --headless --nologo --nofirststartwizard --invisible --norestore --convert-to pdf --outdir ';
        // $clParameters = $this->config->getSystemValue('preview_office_cl_parameters', $defaultParameters);
        
        $expectedExp = pathinfo($fileSystemBaseFileName);
        $expectedFileName = $expectedExp['filename']."-v".$hash;
        $expectedFilePath = $expectedExp['dirname'].'/'.$expectedFileName.".xkt";
        if ($mimeType == 'application/x-step') {
            if(!file_exists($expectedFilePath)){
                $daeFileName = $this->convertIfc($fileSystemBaseFileName, $versionedFileName);
                $gltfFileName = $this->convertCollada($daeFileName, $versionedFileName);
                $xktFileName = $this->convertGltf($gltfFileName, $versionedFileName);
            }else{
                $xktFileName = $expectedFilePath;
            }
        }
        
        if ($mimeType == 'model/gltf-binary') {
            $xktFileName = $this->convertGltf($fileSystemBaseFileName, $versionedFileName);
        }

        if (file_exists($xktFileName)) {

            $directory = $fileNode->getParent();
            // $directoryList = $directory->getDirectoryListing();
            // $storage = $directory->getStorage();
            // $scanner->scanFile($newBaseFilePath.'.xkt');

            try {
                $scanner = new Scanner($this->userId, null, \OC::$server->query(IEventDispatcher::class), \OC::$server->getLogger());
                $scanner->scan($ext['dirname']);
            } catch (\Exception $e) {
                $this->logger->logException($e, [
                    'app' => 'files'
                ]);
            }

            // $storage->
            if ($directory->nodeExists($newBaseFilePath . '.xkt')) {
                $xktfileInfo = $view->getFileInfo($newBaseFilePath . '.xkt');
            }

            $responseModel = [
                "id" => $ifcid,
                "name" => $ext['basename'],
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
                    "provider" => "cde"
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
                    "name" => $ext['basename'],
                    "file_name" => $fileName,
                    "description" => null,
                    "file" => "/remote.php/webdav/" . $subdir . "/" . $fileName,
                    "size" => 29255229,
                    "created_at" => "2018-03-02T16:41:04Z",
                    "updated_at" => "2019-03-30T10:51:55Z",
                    "ifc_id" => $ifcid
                ],
                "structure_file" => "/remote.php/webdav/" . $subdir . "/" . $expectedFileName . "_structure.json",
                "systems_file" => "/remote.php/webdav/" . $subdir . "/" . $expectedFileName . "_systems.json",
                "map_file" => "/remote.php/webdav/" . $subdir . "/" . $expectedFileName . "_map.json",
                "gltf_file" => "/remote.php/webdav/" . $subdir . "/" . $expectedFileName.".gltf",
                "bvh_tree_file" => null,
                "viewer_360_file" => "/remote.php/webdav/" . $subdir . "/" . $expectedFileName . "_a360.gltf",
                "xkt_file" => "/remote.php/webdav/" . $subdir . "/" . $expectedFileName . ".xkt",
                "project_id" => 100,
                "errors" => null,
                "warnings" => null
            ];
        } else {
            $responseModel = [];
        }

        $response = new JSONResponse($responseModel);
        // $response->cacheFor(3600);
        return $response;
        // return new JSONResponse(json_decode ("{ \"id\": 78, \"name\": \"19 rue Marc Antoine Petit 69002 Lyon\", \"creator\": { \"id\": 83, \"email\": \"demo@bimdata.io\", \"company\": \"\", \"firstname\": \"Démo\", \"lastname\": \"BIMData\", \"created_at\": \"2018-03-02T12:35:34Z\", \"updated_at\": \"2019-03-30T11:07:29Z\", \"cloud_role\": 50, \"project_role\": 25, \"provider\": \"bimdataconnect\" }, \"status\": \"C\", \"source\": \"UPLOAD\", \"created_at\": \"2018-03-02T16:41:04Z\", \"updated_at\": \"2019-11-22T17:29:55Z\", \"document_id\": 1, \"document\": { \"id\": 1, \"parent\": 185, \"parent_id\": 185, \"creator\": 83, \"project\": 100, \"name\": \"19 rue Marc Antoine Petit 69002 Lyon\", \"file_name\": \"Duplex_A_20110907_optimized.gltf\", \"description\": null, \"file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf\", \"size\": 29255229, \"created_at\": \"2018-03-02T16:41:04Z\", \"updated_at\": \"2019-03-30T10:51:55Z\", \"ifc_id\": 78 }, \"structure_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf_structure.json\", \"systems_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf_systems.json\", \"map_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf_map.json\", \"gltf_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf\", \"bvh_tree_file\": null, \"viewer_360_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf_a360.gltf\", \"xkt_file\": \"/remote.php/webdav/Duplex_A_20110907_optimized.gltf.xkt\", \"project_id\": 100, \"errors\": null, \"warnings\": null }"));
    }

    /**
     * 
     * @param string $souceFile
     * @return void|string
     */
    public function convertIfc($souceFile, $versionedFileName)
    {
        $ext = pathinfo($souceFile);
        if($versionedFileName==null){
            $versionedFileName=$ext['filename'];
        }
        $destFileName = $ext['dirname'].'/'.$versionedFileName.'.dae';
//        ./IfcConvert  20200318SD-M-1637-L55-IFC\ 200318.IFC factory.dae
        $command = '/usr/local/bin/IfcConvert';
        $commandParameters = ' -y ' . 
            escapeshellarg($ext['dirname'].'/'.$ext['filename'].'.'.$ext['extension']) . 
            ' ' . 
            escapeshellarg($destFileName);
        $exec = $command . $commandParameters;
        $exitCode = 0;
        exec($exec, $out, $exitCode);
        if ($exitCode !== 0) {
            $this->logger->error("could not convert {file}, reason: {out}", [
                'app' => 'workflow_ifc_converter',
                'file' => $fileNode->getPath(),
                'out' => $out
            ]);
            return;
        }
        return $destFileName;
    }
    
    
    public function convertCollada($souceFile, $versionedFileName)
    {
        $ext = pathinfo($souceFile);
        if($versionedFileName==null){
            $versionedFileName=$ext['filename'];
        }
        $destFileName = $ext['dirname'].'/'.$versionedFileName.'.gltf';
        $command = '/usr/local/bin/COLLADA2GLTF-bin';
        $commandParameters = ' -i ' . 
            escapeshellarg($ext['dirname'].'/'.$ext['filename'].'.'.$ext['extension']). 
            ' -o ' . 
            escapeshellarg($destFileName);
        $exec = $command . $commandParameters;
        $exitCode = 0;
        exec($exec, $out, $exitCode);
        if ($exitCode !== 0) {
            $this->logger->error("could not convert {file}, reason: {out}", [
                'app' => 'workflow_ifc_converter',
                'file' => $fileNode->getPath(),
                'out' => $out
            ]);
            return;
        }
        return $destFileName;
    }
    
    
    public function convertGltf($souceFile, $versionedFileName)
    {
        $ext = pathinfo($souceFile);
        if($versionedFileName==null){
            $versionedFileName=$ext['filename'];
        }
        $destFileName = $ext['dirname'].'/'.$versionedFileName.'.xkt';
        $command = '/usr/local/bin/gltf2xkt'; // Get this to config $this->config->getSystemValue('gltf2xkt_path', null);
        $commandParameters = ' -s ' . 
            escapeshellarg($ext['dirname'].'/'.$ext['filename'].'.'.$ext['extension']) . 
            ' -o ' . 
            escapeshellarg($destFileName);
        $exec = $command . $commandParameters;
        $exitCode = 0;
        exec($exec, $out, $exitCode);
        if ($exitCode !== 0) {
            $this->logger->error("could not convert {file}, reason: {out}", [
                'app' => 'workflow_ifc_converter',
                'file' => $fileNode->getPath(),
                'out' => $out
            ]);
            return;
        }
        return $destFileName;
    }

    /**
     *
     * @PublicPage
     * @NoCSRFRequired
     *
     * @param bool $minmode
     * @return TemplateResponse
     */
    public function ifclist(string $cloudid, string $projectid)
    {
        return new JSONResponse(json_decode("[{}]"));
    }
}
