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
         * @return TemplateResponse      
         */

	public function showViewer() {
	    $params = [];
	    $response = new TemplateResponse($this->appName, 'viewer');
        $policy = new ContentSecurityPolicy();
        $policy->addAllowedFrameDomain('\'self\'');
        $policy->addAllowedFontDomain('data:');
        $policy->addAllowedImageDomain('*');
	    $policy->addAllowedScriptDomain('\'self\'');
	    $policy->addAllowedStyleDomain('\'self\'');
	    $policy->addAllowedFrameAncestorDomain('\'self\'');
        $response->setContentSecurityPolicy($policy);
		
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
	public function showIFrame() {
	    $params = [
	        'urlGenerator' => $this->urlGenerator,
	        'minmode' => false
	    ];
	    $response = new TemplateResponse($this->appName, 'iframe', $params,'blank');
	    
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
