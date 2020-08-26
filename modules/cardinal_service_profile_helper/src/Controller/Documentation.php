<?php

namespace Drupal\cardinal_service_profile_helper\Controller;

use Drupal\Core\Controller\ControllerBase;
use Michelf\MarkdownExtra;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Documentation extends ControllerBase {

  /**
   * @param null $document
   *
   * @return array
   */
  public function getDocumentation($document) {
    // Invalid path.
    if (!file_exists(self::getProfilePath() . "/help/$document.md")) {
      throw new NotFoundHttpException($this->t('Document @document not found', ['@document' => $document]));
    }
    $html = MarkdownExtra::defaultTransform(file_get_contents($this->getProfilePath() . "/help/$document.md"));
    $h1 = preg_grep('/<h1>.*?<\/h1>/', explode("\n", $html));

    return [
      '#markup' => preg_replace('/<h1>.*?<\/h1>/', '', $html),
      '#title' => !empty($h1[0]) ? strip_tags($h1[0]) : NULL,
    ];
  }

  /**
   * Page title callback.
   *
   * @param string $document
   *   File name of the help document.
   *
   * @return string
   *   Page title.
   */
  public function getTitle($document) {
    $html = $this->getDocumentation($document);
    return $html['#title'];
  }

  /**
   * Get the path to the CS profile.
   *
   * @return string
   *   Path to the CS profile
   */
  protected static function getProfilePath() {
    return drupal_get_path('profile', 'cardinal_service_profile');
  }

}

