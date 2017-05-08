<?php

namespace Drupal\asu_brand\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides an ASU header block.
 *
 * @Block(
 *  id = "asu_brand_header",
 *  admin_label = @Translation("ASU Brand: Header"),
 * )
 */
class AsuBrandHeader extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'header_basepath' => ASU_BRAND_HEADER_BASEPATH_DEFAULT,
      'header_version' => ASU_BRAND_HEADER_VERSION_DEFAULT,
      'header_template' => ASU_BRAND_HEADER_TEMPLATE_DEFAULT,
//      'sitemenu_injection_flag' => 1,
//      'sitemenu_name' => ASU_BRAND_SITE_MENU_NAME_DEFAULT,
//      'gtm_override' => '',
    ] + parent::defaultConfiguration();

 }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    // TODO: Implement ajax function to force user to reselect header version when basepath changes
    // Per https://brandguide.asu.edu/web-standards/enterprise/asu-global-header#quicktabs-global_asu_header=2
    // ASU Header files won't be hosted on AFS anymore.
    $form['header_basepath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ASU theme basepath'),
      '#description' => $this->t('This setting is shared between all ASU Brand blocks. The default is %default. To use a local path, use %local_path',
        ['%default' => ASU_BRAND_HEADER_BASEPATH_DEFAULT, '%local_path' => '/afs/asu.edu/www/asuthemes']),
      '#default_value' => $this->configuration['header_basepath'],
      '#required' => TRUE,
      '#weight' => 1,
      '#ajax' => [
        'event' => 'change',
        'callback' => [$this, 'resetHeaderVersionCallback'],
        'wrapper' => 'asu-brand--header-version--wrapper',
      ],
    ];

    // TODO: If external XML file is provided, build options array by parsing file.

    $form['header_version'] = [
      '#type' => 'select',
      '#title' => $this->t('ASU header version'),
      '#description' => $this->t('Select the version of the ASU header. <strong>This setting is shared between all ASU Brand blocks.</strong>'),
      '#empty_option' => $this->t('-select-'),
      '#options' => [
        '4.5' => '4.5',
        '4.6' => '4.6'
      ],
      '#default_value' => $this->configuration['header_version'],
      '#required' => TRUE,
      '#prefix' => '<div id="asu-brand--header-version--wrapper">',
      '#suffix' => '</div>',
      '#weight' => 2,
    ];

    $form['header_template'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ASU header template key'),
      '#default_value' => $this->configuration['header_template'],
      '#description' => $this->t('The default is %default. For a list of template keys, visit <a href="@template_path">@template_path</a>.', ['%default' => ASU_BRAND_HEADER_TEMPLATE_DEFAULT, '@template_path' => 'https://asu.edu/asuthemes/' . $this->configuration['header_version'] . '/heads/']),
      '#required' => TRUE,
      '#weight' => 3,
    ];
//
//    $form['asu_brand']['site_menu'] = [
//      '#type' => 'fieldset',
//      '#title' => $this->t('Site menu injection'),
//      '#collapsed' => FALSE,
//      '#weight' => 4,
//    ];
//
//    $form['asu_brand']['site_menu']['asu_brand_sitemenu_injection_flag'] = [
//      '#type' => 'checkbox',
//      '#title' => t('Append local site menu into ASU header menu and display in responsive state.'),
//      '#default_value' =>  $this->configuration['asu_brand_sitemenu_injection_flag'],
//    ];
//
//    // TODO: Populate menu options
//    $form['asu_brand']['site_menu']['asu_brand_sitemenu_name'] = [
//      '#type' => 'select',
//      '#title' => t('Menu to inject'),
//      '#description' => t('Select the site menu to inject.'),
////      '#options' => asu_brand_get_site_menu_options(),
//      '#options' => ['secondary-menu' => 'Secondary Menu', 'main-menu' => "Main Menu"],
//      '#default_value' => $this->configuration['asu_brand_sitemenu_name'],
//      '#states' => [
//        'visible' => [
//            ':input[name="settings[asu_brand][site_menu][asu_brand_sitemenu_injection_flag]"]' => ['checked' => TRUE],
//        ],
//      ],
//    ];
//
//    $form['asu_brand']['gtm'] = [
//      '#type' => 'fieldset',
//      '#title' => $this->t('Override Google Tag Manager (GTM) settings'),
//      '#description' => $this->t('ASU Universal GTM settings are automatically set. Overriding is not common.'),
//      '#collapsible' => TRUE,
//      '#collapsed' => TRUE,
//      '#weight' => 5,
//    ];
//
//    $form['asu_brand']['gtm']['asu_brand_gtm_override'] = [
//      '#type' => 'textarea',
//      '#title' => $this->t('Custom GTM Script'),
//      '#default_value' => $this->configuration['asu_brand_gtm_override'],
//      '#description' => $this->t('This script should be provided by the ASU google Analytics Administrator.'),
//    ];

    // Disable caching on this form.
    $form_state->setCached(FALSE);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['header_basepath'] = $form_state->getValue(['header_basepath']);
    $this->configuration['header_version'] = $form_state->getValue(['header_version']);
    $this->configuration['header_template'] = $form_state->getValue(['header_template']);
//    $this->configuration['asu_brand_sitemenu_injection_flag'] = $form_state->getValue(['asu_brand', 'asu_brand_sitemenu_injection_flag']);
//    $this->configuration['asu_brand_sitemenu_name'] = $form_state->getValue(['asu_brand', 'asu_brand_sitemenu_name']);
//    $this->configuration['asu_brand_gtm_override'] = $form_state->getValue(['asu_brand', 'asu_brand_gtm_override']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $basepath = rtrim($this->configuration['header_basepath'],"/");
    $version = $this->configuration['header_version'];
    $template_key = $this->configuration['header_template'];

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
   * Selects just the asu_brand_header_version to be returned for re-rendering
   *
   * @param array $form
   * @param array $form_state
   * @return renderable array (asu_brand_header_version)
   */
  function resetHeaderVersionCallback(array &$form, FormStateInterface $form_state) {
    // Force user to select header version again as basepath has changed.
    // available header versions can differ between DEV, QA and PROD.

    $element = $form['settings']['header_version'];
    $element['#default_value'] = '';
    $element['#value'] = '';

    return $element;
  }

  /**
   * Load external file from URL
   *
   * @param $uri
   * @return bool|string
   */
  private function fetchExternalMarkUp($url) {
    $data = '';

    try {
      $response = \Drupal::httpClient()->get($url, array('headers' => array('Accept' => 'text/plain')));
      if ($response->getStatusCode() == 200) {
        $data = (string)$response->getBody();
      }
    }
    catch (RequestException $e) {
      // TODO: Log error message
    }

    return $data;
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
//      $js_settings['asu_sso_signinurl'] = url('cas', array('https' => TRUE, 'query' => drupal_get_destination()));
//      $js_settings['asu_sso_signinurl'] = Url::fromRoute('/cas', ['query' => \Drupal::destination()->getAsArray()]);
      $js_settings['asu_sso_signinurl'] = Url::fromUserInput($cas_sign_in_path, ['absolute' => TRUE])->toString();
//      $js_settings['asu_sso_signouturl'] = url('caslogout', array('https' => TRUE));
      $js_settings['asu_sso_signouturl'] = Url::fromUserInput('/caslogout', ['absolute' => TRUE])->toString();
    } else {
//      $js_settings['asu_sso_signinurl'] = url('user/login', array('query' => user_login_destination()));
      $js_settings['asu_sso_signinurl'] = Url::fromUserInput('user/login', ['absolute' => TRUE])->toString();
      $js_settings['asu_sso_signouturl'] = Url::fromUserInput('user/logout', ['absolute' => TRUE])->toString();
    }

    return $js_settings;
  }

}
