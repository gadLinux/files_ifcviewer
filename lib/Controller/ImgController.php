<?php
namespace OCA\Files_Ifcviewer\Controller;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Controller;
use OCP\IURLGenerator;
use OCP\IRequest;

class ImgController extends Controller {
	private $userId;

	/** @var IURLGenerator */
	private $urlGenerator;

	
	public function __construct($AppName, 
	                            IRequest $request,
				    IURLGenerator $urlGenerator,
	                            $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->urlGenerator = $urlGenerator;
	}
	
        /**
         *
         * @PublicPage
         * @NoCSRFRequired
         * 
         * @return TemplateResponse      
         */

	public function show($id) {
		$params = [];
		$file = $folder->getFile($id);
                $content_type = 'image/png';
	    $response = new FileDisplayResponse($file);
            $policy = new ContentSecurityPolicy();
            $policy->addAllowedFrameDomain('\'self\'');
            $policy->addAllowedFontDomain('data:');
            $policy->addAllowedImageDomain('*');
	    $policy->allowEvalScript(false);
	    $policy->addAllowedScriptDomain('\'self\'');
	    $policy->addAllowedStyleDomain('\'self\'');
	    $policy->addAllowedFrameAncestorDomain('\'self\'');
            $response->setContentSecurityPolicy($policy);
		
            return $response;
	}
}
