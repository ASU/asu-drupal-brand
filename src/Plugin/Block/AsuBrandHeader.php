<?php

namespace Drupal\asu_brand\Plugin\Block;

use Drupal\Core\Url;

/**
 * Provides an ASU header block.
 *
 * @Block(
 *  id = "asu_brand_header",
 *  admin_label = @Translation("ASU Brand: Header"),
 * )
 */
class AsuBrandHeader extends AsuBrandBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $basepath = ASU_BRAND_HEADER_BASEPATH_DEFAULT;
    $version = ASU_BRAND_HEADER_VERSION_DEFAULT;
    $template_key = ASU_BRAND_HEADER_TEMPLATE_DEFAULT;

    $uri = "{$basepath}/{$version}/headers/{$template_key}.shtml";

    $build = [];
    $build['#attached']['library'][] = 'asu_brand/header';
    $build['#attached']['html_head'][] = [
      [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => 'var ASUHeader = ASUHeader || {}; ASUHeader.browser = false;',
      ],
      // A key, to make it possible to recognize this HTML  element when altering.
      'asu-brand-header-inlinejs',
    ];
    $build['#attached']['drupalSettings']['asu_brand'] = $this->getJsSettings();
    $build['header'] = [
      '#type' => 'inline_template',
      '#template' => '{{ html | raw }}',
      '#context' => [
        'html' => $this->fetchExternalMarkUp($uri),
      ]
    ];

    return $build;
  }

  /**
   * Get ASU brand block settings.
   */
  private function getJsSettings() {

    $is_user_logged_in = TRUE;
    $moduleHandler = \Drupal::service('module_handler');

    if (\Drupal::currentUser()->isAnonymous()) {
      $is_user_logged_in = FALSE;
    }

    // Set javascript settings.
    $js_settings = [
      'asu_sso_signedin' => ($is_user_logged_in ? 'true' : 'false'),
      'asu_sso_signinurl' => '',
      'asu_sso_signouturl' => '',
    ];

    // Alter the signin/signout URL if cas in enabled.
    // TODO: Add destination queries on log in and log out links.
    if ($moduleHandler->moduleExists('cas')){
      $cas_sign_in_path = \Drupal::config('cas.settings')->get('server.path');
      $js_settings['asu_sso_signinurl'] = Url::fromUserInput($cas_sign_in_path, ['absolute' => TRUE])->toString();
      $js_settings['asu_sso_signouturl'] = Url::fromUserInput('/caslogout', ['absolute' => TRUE])->toString();
    } else {
      $js_settings['asu_sso_signinurl'] = Url::fromUserInput('/user/login', ['absolute' => TRUE])->toString();
      $js_settings['asu_sso_signouturl'] = Url::fromUserInput('/user/logout', ['absolute' => TRUE])->toString();
    }

    return $js_settings;
  }

}
