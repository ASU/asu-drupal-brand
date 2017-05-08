/**
 * @file
 * Main js for the ASU brand module.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Update Log In link on ASU header to point to CAS endpoint.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches behavior on page load.
   */
  Drupal.behaviors.asuBrandSetupHeader = {
    attach: function (context, settings) {
      // $('body', context).once('asu_brand', function () {
        // ASUHeader should already be initialized by the provided header files
        // but we should verify.
        if (ASUHeader) {
          // Remember that values stored in Drupal.settings are strings.
          ASUHeader.user_signedin = (settings.asu_brand.asu_sso_signedin === 'true');
          ASUHeader.signin_url = settings.asu_brand.asu_sso_signinurl;
          ASUHeader.signout_url = settings.asu_brand.asu_sso_signouturl;
        }
      // });
    }
  };

})(jQuery, Drupal, ASUHeader);