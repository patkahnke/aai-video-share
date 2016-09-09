/**
 * Created by patrickkahnke on 9/5/16.
 */

(function($, Drupal) {

            Drupal.AjaxCommands.prototype.buildIFrames = function(ajax, response, status){
                console.log('buildIFrames is being called');
                $('#movies').append(
                '<div class="col-sm-8">' +
                    '<span class="video-labels">Testing connection to jQuery</span>' +
                    '</div>'
                );
            }
})(jQuery, Drupal);