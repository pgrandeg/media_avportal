<?php

declare(strict_types = 1);

namespace Drupal\Tests\media_avportal\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Base class for Media AV Portal functional JavaScript tests.
 */
class MediaAvPortalCreateContentTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'node',
    'field_ui',
    'media_avportal',
    'media_avportal_mock',
    'image',
    'responsive_image',
    'media_avportal_responsive_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->getEditable('media.settings');
    $config->set('standalone_url', TRUE);
    $config->save();

    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Tests the AV Portal video media entity.
   */
  public function testAvPortalVideoMediaEntity(): void {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    // Log in as an administrator.
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    // Create the Media AV portal media video bundle.
    $this->drupalGet('admin/structure/media/add');
    $page->fillField('label', 'Media AV Portal video');
    $this->assertNotEmpty(
      $assert_session->waitForElementVisible('css', '.machine-name-value')
    );
    $assert_session->selectExists('Media source')->selectOption('media_avportal_video');
    $this->assertNotEmpty(
      $assert_session->waitForElementVisible('css', 'fieldset[data-drupal-selector="edit-source-configuration"]')
    );
    $assert_session->selectExists('Resource title')->selectOption('name');
    $page->pressButton('Save');
    $page->hasContent('The media type Media AV Portal video Test has been added.');

    // Set the formatter so that we can view Media of this type.
    $config = $this->config('core.entity_view_display.media.media_av_portal_video.default');
    $config->set('content.field_media_media_avportal_video.type', 'avportal_video');
    $config->set('content.field_media_media_avportal_video.settings', []);
    $content = $config->get('content');
    $content['thumbnail'] = [
      'type' => 'image',
      'weight' => 1,
      'region' => 'content',
      'label' => 'visually_hidden',
      'settings' => [],
      'third_party_settings' => [],
    ];
    $config->set('content', $content);
    $config->save();

    // Create a media content with a valid reference.
    $this->drupalGet('media/add/media_av_portal_video');
    $page->fillField('Media AV Portal Video', 'https://audiovisual.ec.europa.eu/en/video/I-162747');
    $page->pressButton('Save');

    // Visit the new media content.
    $page->clickLink('Midday press briefing from 25/10/2018');

    // Check the iframe URL.
    $iframe_url = $assert_session->elementExists('css', 'iframe')->getAttribute('src');
    $this->assertContains('ec.europa.eu/avservices/play.cfm', $iframe_url);
    $this->assertContains('ref=I-162747', $iframe_url);

    // @todo assert the width and height of the iframe.
    // Edit the newly created media.
    $this->drupalGet('media/1/edit');

    // Update the field.
    $page->fillField('Media AV Portal Video', 'https://audiovisual.ec.europa.eu/en/video/I-163162');
    $page->pressButton('Save');

    // Visit the updated media content.
    $page->clickLink('Economic and Financial Affairs Council - Arrivals');

    // Check the iframe URL.
    $iframe_url = $assert_session->elementExists('css', 'iframe')->getAttribute('src');
    $this->assertContains('ec.europa.eu/avservices/play.cfm', $iframe_url);
    $this->assertContains('ref=I-163162', $iframe_url);

    // Create a media content with an invalid reference.
    $this->drupalGet('media/add/media_av_portal_video');
    $page->fillField('Media AV Portal Video', 'https://audiovisual.ec.europa.eu/en/video/I-12345678987654321');
    $page->pressButton('Save');

    $assert_session->pageTextContains('The given URL does not match an AV Portal URL.');

    // Create a media content with an invalid resource URL.
    $this->drupalGet('media/add/media_av_portal_video');
    $page->fillField('Media AV Portal Video', 'https://example.com/en/video/I-12345678987654321');
    $page->pressButton('Save');

    $assert_session->pageTextContains('Invalid URL format specified.');

    // Test creating a media content from different URLs and check field values.
    foreach ($this->getVideoFixtures() as $test) {
      foreach ($test['input']['urls'] as $url) {
        // Create a media content with a valid reference.
        $this->drupalGet('media/add/media_av_portal_video');
        $page->fillField('Media AV Portal Video', $url);
        $page->pressButton('Save');

        $page->clickLink($test['expect']['title']);
        $image_url = $assert_session->elementExists('css', '.field--name-thumbnail img')->getAttribute('src');
        // Make sure that we have a thumbnail.
        $this->assertNotContains('generic/no-thumbnail.png', $image_url);
      }
    }
  }

  /**
   * Tests the AV Portal photo media entity.
   */
  public function testAvPortalPhotoMediaEntity(): void {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    // Log in as an administrator.
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    // Create the Media AV portal media photo bundle.
    $this->drupalGet('admin/structure/media/add');
    $page->fillField('label', 'Media AV Portal Photo');
    $this->assertNotEmpty(
      $assert_session->waitForElementVisible('css', '.machine-name-value')
    );
    $assert_session->selectExists('Media source')->selectOption('media_avportal_photo');
    $this->assertNotEmpty(
      $assert_session->waitForElementVisible('css', 'fieldset[data-drupal-selector="edit-source-configuration"]')
    );
    $assert_session->selectExists('Resource title')->selectOption('name');
    $page->pressButton('Save');
    $page->hasContent('The media type Media AV Portal photo Test has been added.');

    // Set the formatter so that we can view Media of this type.
    $config = $this->config('core.entity_view_display.media.media_av_portal_photo.default');
    $config->set('content.field_media_media_avportal_photo.type', 'avportal_photo');
    $config->set('content.field_media_media_avportal_photo.settings', [
      'image_style' => '',
    ]);
    $config->save();

    // Create a media content with a valid reference.
    $this->drupalGet('media/add/media_av_portal_photo');
    $page->hasContent('You can link to media from AV Portal by entering a URL in the formats: https://audiovisual.ec.europa.eu/en/photo/[REF], https://audiovisual.ec.europa.eu/en/album/[album-id]/[REF]');
    $page->fillField('Media AV Portal Photo', 'https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15');
    $page->pressButton('Save');

    // Visit the new media content.
    $page->clickLink('Euro with miniature figurines');

    // Check the image URL.
    $image_url = $assert_session->elementExists('css', 'img.avportal-photo')->getAttribute('src');
    $this->assertContains('ec.europa.eu/avservices/avs/files/video6/repository/prod/photo/store/', $image_url);
    $this->assertContains('P038924-352937.jpg', $image_url);

    // Make sure that the media URL is normalized back to the correct format.
    $this->drupalGet('media/1/edit');
    $this->assertSession()->fieldValueEquals('Media AV Portal Photo', 'https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15');

    // We need to support both individual photos and photos inside albums.
    $photo_urls = [
      'https://audiovisual.ec.europa.eu/en/photo/P-039162~2F00-12',
      'https://audiovisual.ec.europa.eu/en/album/M-090909/P-039162~2F00-12',
    ];

    foreach ($photo_urls as $photo_url) {
      // Edit the newly created media.
      $this->drupalGet('media/1/edit');

      // Update the field.
      $page->fillField('Media AV Portal Photo', $photo_url);
      $page->pressButton('Save');

      // Visit the updated media content.
      $page->clickLink('Andrus Ansip Vice-President of the EC addresses the Plenary of the European Parliament on the beginning of the Romanian Presidency of the Council of the EU');

      // Check the image URL.
      $image_url = $assert_session->elementExists('css', 'img.avportal-photo')->getAttribute('src');
      $this->assertContains('ec.europa.eu/avservices/avs/files/video6/repository/prod/photo/store/', $image_url);
      $this->assertContains('P039162-137797.jpg', $image_url);
    }

    // Make sure that the media URL is normalized back to the correct format.
    $this->drupalGet('media/1/edit');
    $this->assertSession()->fieldValueEquals('Media AV Portal Photo', reset($photo_urls));

    // Create a media content with an invalid reference.
    $this->drupalGet('media/add/media_av_portal_photo');
    $page->fillField('Media AV Portal Photo', 'https://audiovisual.ec.europa.eu/en/photo/P-0391620~2F00-12');
    $page->pressButton('Save');

    $assert_session->pageTextContains('The given URL does not match an AV Portal URL.');

    // Create a media content with an invalid resource URL.
    $this->drupalGet('media/add/media_av_portal_photo');
    $page->fillField('Media AV Portal Photo', 'https://example.com/en/photo/P-0391620~2F00-12');
    $page->pressButton('Save');

    $assert_session->pageTextContains('Invalid URL format specified.');

    // Test that the formatter works with the image styles.
    $config = $this->config('core.entity_view_display.media.media_av_portal_photo.default');
    $config->set('content.field_media_media_avportal_photo.settings', [
      'image_style' => 'large',
    ]);
    $config->save();
    // We need to invalidate this cache tag because otherwise the change in
    // config does not show up. This is normally handled by the Entity display
    // form save which we are not reproducing here.
    $this->container->get('cache_tags.invalidator')->invalidateTags(['media_view']);
    $this->drupalGet('media/1');
    $image_url = $assert_session->elementExists('css', 'img.avportal-photo')->getAttribute('src');
    $this->assertContains('files/styles/large/avportal/P-039162/00-12.jpg', $image_url);
  }

  /**
   * Tests the AV Portal photo media entity with the responsive image formatter.
   */
  public function testAvPortalPhotoMediaEntityResponsive(): void {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    // Log in as an administrator.
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    // Create the Media AV portal media photo bundle.
    $this->drupalGet('admin/structure/media/add');
    $page->fillField('label', 'Media AV Portal Photo');
    $this->assertNotEmpty(
      $assert_session->waitForElementVisible('css', '.machine-name-value')
    );
    $assert_session->selectExists('Media source')->selectOption('media_avportal_photo');
    $this->assertNotEmpty(
      $assert_session->waitForElementVisible('css', 'fieldset[data-drupal-selector="edit-source-configuration"]')
    );
    $assert_session->selectExists('Resource title')->selectOption('name');
    $page->pressButton('Save');
    $page->hasContent('The media type Media AV Portal photo Test has been added.');

    // Set the formatter so that we can view Media of this type.
    $config = $this->config('core.entity_view_display.media.media_av_portal_photo.default');
    $config->set('content.field_media_media_avportal_photo.type', 'avportal_photo_responsive');
    $config->set('content.field_media_media_avportal_photo.settings', [
      'responsive_image_style' => 'test',
    ]);
    $config->save();

    // Create a media content with a valid reference.
    $this->drupalGet('media/add/media_av_portal_photo');
    $page->fillField('Media AV Portal Photo', 'https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15');
    $page->pressButton('Save');

    // Visit the new media content.
    $page->clickLink('Euro with miniature figurines');

    // Check the responsive source sets.
    $picture = $assert_session->elementExists('css', 'picture');
    $this->assertContains('styles/large/avportal/P-038924/00-15', $picture->find('css', 'source[media="(min-width: 851px)"]')->getAttribute('srcset'));
    $this->assertContains('styles/medium/avportal/P-038924/00-15', $picture->find('css', 'source[media="(min-width: 560px)"]')->getAttribute('srcset'));
    $this->assertContains('styles/thumbnail/avportal/P-038924/00-15', $picture->find('css', 'source[media="(min-width: 0px)"]')->getAttribute('srcset'));

    // Check the image fallback URL.
    $image_url = $picture->find('css', 'img.avportal-photo')->getAttribute('src');
    $this->assertContains('ec.europa.eu/avservices/avs/files/video6/repository/prod/photo/store/', $image_url);
    $this->assertContains('P038924-352937.jpg', $image_url);
  }

  /**
   * Fixtures of data for testing rendering of creating and rendering of.
   */
  protected function getVideoFixtures(): array {
    return [
      'Video without thumbnail and title' => [
        'input' => [
          'urls' => [
            'https://audiovisual.ec.europa.eu/en/video/I-056847',
          ],
        ],
        'expect' => [
          'title' => 'Space and You (short version)',
        ],
      ],
      'Video with no title' => [
        'input' => [
          'urls' => [
            'https://audiovisual.ec.europa.eu/en/video/I-053547',
          ],
        ],
        'expect' => [
          'title' => 'Launch of the F&P7: Clip 1 "Ensemble"',
        ],
      ],
      'Video with no thumbnail' => [
        'input' => [
          'urls' => [
            'https://audiovisual.ec.europa.eu/en/video/I-129872',
          ],
        ],
        'expect' => [
          'title' => 'European Solidarity Corps - Teaser 2',
        ],
      ],
    ];
  }

}
