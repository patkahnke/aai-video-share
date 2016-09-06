/**
 * Created by patrickkahnke on 9/5/16.
 */

(function ($, Drupal) {
    Drupal.behaviors.proofAPI = {
        attach: function (context, settings) {

            Drupal.AjaxCommands.prototype.readMessage = function(ajax, response, status) {
                $('#movies').append(
                '<div class="col-sm-8">' +
                    '<span class="video-labels">Testing connection to jQuery' +
                    '</div>'
                )
            }
        }
    }
})(jQuery, Drupal);