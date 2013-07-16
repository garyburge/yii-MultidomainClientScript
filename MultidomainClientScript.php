<?php

/**
 * @author Borales <bordun.alexandr@gmail.com>, modified by Gary Burge
 *
 * @property string $assetsBaseUrl Use it (Yii::app()->clientScript->assetsBaseUrl) instead of Yii::app()->request->baseUrl
 *
 */
class MultidomainClientScript extends CClientScript
{

    /**
     * GLB: Add this because it is referenced in code, but not defined here
     */
    public $enableStaticAssets = true;

    /**
     * GLB: add array of other subdomains to remove before prepending $assetsSubdomain
     * e.g. 'admin.mydomain.com' => 'mydomain.com' => 'assetsSubdomain.mydomain.com'
     */
    public $subdomainsToRemove = false;

    /**
     * @var bool Whether the multidomain assets are enabled
     */
    public $enableMultidomainAssets = true;

    /**
     * @var string Subdomain name
     */
    public $assetsSubdomain = "assets";

    /**
     * @var bool Whether to use multiple assets subdomains
     */
    public $indexedAssetsSubdomain = false;

    /**
     * @param string $subDomainIndex
     * @return string
     */
    public function getAssetsBaseUrl($subDomainIndex = "")
    {
        // get base url
        $baseUrl = Yii::app()->request->baseUrl;

        // if no multi-domain assets requested, return base url
        if ($this->enableMultidomainAssets === false) {
            return $baseUrl;
        }

        // get host portion of current url
        $serverName = Yii::app()->request->serverName;

        // remove other subdomains?
        if ($this->subdomainsToRemove && is_array($this->subdomainsToRemove) && count($this->subdomainsToRemove)) {
            // get hostname without subdomain
            $serverName = str_replace($this->subdomainsToRemove, '', $serverName);
            // strip leading dot
            if (0 === strpos($serverName, '.')) {
                $serverName = substr($serverName, 1);
            }
        }

        // get protocol
        $schema = Yii::app()->request->isSecureConnection ? "https://" : "http://";

        // index requested?
        $subDomainIndex = $this->indexedAssetsSubdomain ? $subDomainIndex : "";

        // format asset domain url
        $baseUrl = $schema . $this->assetsSubdomain . $subDomainIndex . '.' . $serverName . $baseUrl;

        // and return it
        return $baseUrl;
    }

    /**
     * Renders the registered scripts.
     * This method is called in {@link CController::render} when it finishes
     * rendering content. CClientScript thus gets a chance to insert script tags
     * at <code>head</code> and <code>body</code> sections in the HTML output.
     * @param string $output the existing output that needs to be inserted with script tags
     */
    public function render(&$output)
    {
        if ($this->enableStaticAssets && $this->hasScripts) {
            $this->renderCoreScripts();
            $this->coreScripts = null;
            $this->processAssetsUrl();
        }

        parent::render($output);
    }

    protected function processAssetsUrl()
    {
        foreach ($this->cssFiles as $file=> $media) {
            if (strpos($file, '/') === 0) {
                unset($this->cssFiles[$file]);
                $this->cssFiles[$this->assetsBaseUrl . $file] = $media;
            }
        }

        foreach ($this->scriptFiles as $pos=> $scripts) {
            foreach ($scripts as $scriptName=> $script) {
                if (strpos($script, '//') !== 0) {
                    if (strpos($script, '/') === 0) {
                        $this->scriptFiles[$pos][$scriptName] = $this->getAssetsBaseUrl($pos) . $script;
                    }
                }
            }
        }
    }

}
