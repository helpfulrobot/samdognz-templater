<?php

/**
 * TemplaterTest contains test cases for the module classes
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @package templater
 */
class TemplaterTest extends FunctionalTest
{
    protected static $fixture_file = 'TemplaterTest.yml';

    public function testTheme()
    {
        // Create page object
        $page = $this->createPage('blue');
        SiteConfig::current_site_config()->Theme = $page->Theme;

        // Assert content
        $this->assertContains('Theme Blue page content', $page->Content);

        // Assert page content
        $response = Director::test(Director::makeRelative($page->Link()));
        $this->assertContains('Theme Blue page content', $response->getBody());
        $this->assertContains('I\'m in the Blue Theme', $response->getBody());

        // Make theme chooser enabled to specific page type
        Config::inst()->remove('Templater', 'enabled_for_pagetypes');
        Config::inst()->update('Templater', 'enabled_for_pagetypes', ['ErrorPage']);
    }

    public function testTemplate()
    {
        // Create page object
        $page = $this->createPage('hello');

        // Assert content
        $this->assertContains('Hi Hello page', $page->Content);

        // Assert page content
        $response = Director::test(Director::makeRelative($page->Link()));
        $this->assertContains('hello', $response->getBody());
        $this->assertNotContains('Hi Hello page', $response->getBody());
    }

    protected function createPage($name)
    {
        $page = $this->objFromFixture('Page', $name);

        // Login admin
        $this->logInWithPermission('ADMIN');

        // Assert: Publish page
        $published = $page->doPublish();
        $this->assertTrue($published);
        Member::currentUser()->logOut();

        return $page;
    }
}
