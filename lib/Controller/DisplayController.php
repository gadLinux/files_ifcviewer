<?php
namespace OCA\Files_Ifcviewer\Controller;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\IURLGenerator;
use OCP\IRequest;

class DisplayController extends Controller {
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
	 * @param bool $minmode
	 * @return TemplateResponse	 
	 */
	public function showIFCViewer() {
	    $params = [
	        'urlGenerator' => $this->urlGenerator,
	        'minmode' => $minmode
	    ];
	    $response = new TemplateResponse($this->appName, 'viewer', $params, 'blank');
	    
	    $policy = new ContentSecurityPolicy();
	    $policy->addAllowedFrameDomain('\'self\'');
	    $policy->addAllowedFontDomain('data:');
	    $policy->addAllowedImageDomain('*');
//	    $policy->allowEvalScript(false);
	    $response->setContentSecurityPolicy($policy);
	    
	    return $response;
	}

}
