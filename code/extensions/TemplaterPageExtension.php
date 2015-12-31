<?php

/**
 * TemplaterPageExtension is an extension class for adding new CMSFields for changing a page theme or/and template
 *
 * @author  Mohamed Alsharaf <mohamed.alsharaf@samdog.nz>
 * @author  Vinnie Watson <vinnie.watson@samdog.nz>
 * @package templater
 */
class TemplaterPageExtension extends DataExtension
{
    /**
     * Page field for page specific theme
     *
     * @var array
     */
    private static $db = array(
        'Theme' => 'Varchar(255)',
        'PageTemplate' => 'Varchar(255)'
    );

    /**
     * Set theme based on the defined value in the page type
     *
     * @param ContentController $controller
     *
     * @return void
     */
    public function contentcontrollerInit($controller)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $themes = $this->getAvailableThemes();
        if (array_key_exists($controller->Theme, $themes)) {
            SiteConfig::current_site_config()->Theme = $controller->Theme;
        }
    }

    /**
     * Add Theme CMSField to page type form
     *
     * @param FieldList $fields
     *
     * @return void
     */
    public function updateCMSFields(FieldList $fields)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $fields->addFieldsToTab(
            'Root.Template',
            FieldList::create(
                DropdownField::create('Theme', 'Choose Theme', $this->getAvailableThemes())->setEmptyString(_t('SiteConfig.DEFAULTTHEME', '(Use default theme)')),
                DropdownField::create('PageTemplate', 'Choose Template', $this->getAllTemplates())->setEmptyString('(use default template)')
            )
        );
    }

    /**
     * Get all active themes
     *
     * @param string $baseDir
     *
     * @return array
     */
    public function getAvailableThemes($baseDir = null)
    {
        $themes = SSViewer::get_themes($baseDir);
        $disabled = (array)Config::inst()->forClass('SiteConfig')->disabled_themes;

        return array_filter($themes, function ($theme) use ($disabled) {
            return !array_key_exists($theme, $disabled);
        });
    }

    /**
     * Whether or not the module is enabled in the current page type
     *
     * @return bool
     */
    protected function isEnabled()
    {
        $enabledForPages = Config::inst()->get('Templater', 'enabled_for_pagetypes');

        return ((is_string($enabledForPages) && strtolower($enabledForPages) === 'all')
            || in_array($this->owner->ClassName, $enabledForPages, true));
    }

    /**
     * iterate through the current theme Layout directory to find all templates.
     *
     * @return array
     */
    public function getAllTemplates()
    {
        $themeName = $this->owner->Theme;
        if (empty($theme)) {
            $themeName = Config::inst()->get('SSViewer', 'theme');
        }

        $dir = new DirectoryIterator(Director::baseFolder() . '/themes/' . $themeName . '/templates/Layout');
        $currentTemplates = array();
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isFile()
                && $fileinfo->getExtension() === 'ss'
                && !stristr($fileinfo->getFilename(), '._')
            ) {
                $currentTemplates[$fileinfo->getBasename('.ss')] = $fileinfo->getBasename('.ss');
            }
        }
        return $currentTemplates;
    }

    /**
     *
     * @return string
     */
    public function index()
    {
        return $this->owner->renderWith(array($this->owner->PageTemplate, 'Page'));
    }
}
