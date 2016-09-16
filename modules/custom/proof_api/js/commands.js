 (function ($, Drupal) {

     'use strict';

     /*
     Attaches a custom AJAX callback command, "view," to the AjaxCommands object, which gets called
     by the "addView" controller function. Updates the DOM with new number of views.
      @todo Create a failure message in case the API is unreachable, based on the "status" that is returned
      */
     Drupal.AjaxCommands.prototype.view = function(ajax, response, status) {
         var viewTally = response.viewTally;
         var viewID = response.viewID;
         $('.' + viewID + '').text('Views: ' + viewTally);
     };

     /*
      Attaches a custom AJAX callback command, "vote," to the AjaxCommands object, which gets called
      by the "voteUp" and "voteDown" controller functions. Updates the DOM with new number of votes.
      @todo Create a failure message in case the API is unreachable, based on the "status" that is returned
      */
     Drupal.AjaxCommands.prototype.vote = function(ajax, response, status) {
         var voteTally = response.voteTally;
         var voteID = response.voteID;
         $('.' + voteID + '').text('Votes: ' + voteTally);
     };

     /*
     Attaches custom javascript and jQuery behaviors to the Drupal.behaviors object to build the DOM and update it.
     */
     Drupal.behaviors.proofAPI = {
         /*
         Build the initial DOM elements
         "Once" method ensures that this only happens on initial page load.
         "Context" is the entire page on initial load, but is restricted to only newly updated DOM elements on updates.
         "VideoArray" variable was attached to "Settings" in the controller function that built the page.
         @todo Find a better way to call the "newView," "voteUp," and "voteDown" controller functions, instead of the long urls.
          */
         attach: function (context, settings) {
             $('#video-container').once('proofAPIModifyDom').each(function () {
                 var videos = settings.videoArray;

                 for (var i = 0; i < videos.length; i++) {
                     var viewTally = videos[i].attributes.view_tally;
                     var voteTally = videos[i].attributes.vote_tally;
                     var videoID = videos[i].id;
                     var voteID = 'vote' + i;
                     var viewID = 'view' + i;

                     /*
                     "Overlay" is a solution for counting views on embedded videos. A transparent overlay is placed on the iFrame.
                     On a click event on the overlay:
                      - the "newView" function is called to update the view count
                      - the embed url is refactored to autoplay the video, which triggers playback, and
                      - the overlay is removed so the user can interact with the iFrame directly at that point.
                      */
                     $('#video-container').append(
                         '<div class="individual-container">' +
                         '<table>' +
                         '<a class="add-view use-ajax" href="http://aai-video-share.dd:8083/new_view/ajax/' + videoID + '/' + viewID + '"></a>' +
                         '<td class="votes-views ' + viewID + '">Views: ' + viewTally + '</td>' +
                         '<td class="votes-views ' + voteID + '">Votes: ' + voteTally + '</td>' +
                         '<td class="votes-views"><a class="vote-up-button use-ajax" href="http://aai-video-share.dd:8083/vote_up/ajax/' + videoID + '/' + voteID + '">Vote Up</a></td>' +
                         '<td class="votes-views"><a class="vote-down-button use-ajax" href="http://aai-video-share.dd:8083/vote_down/ajax/' + videoID + '/' + voteID + '">Vote Down</a><td>' +
                         '</table>' +
                         '<div class="overlay">' +
                         '<iframe id="player"' +
                         'width="640" height="480"' +
                         'src="https://' + videos[i].attributes.embedURL +
                         '?frameborder="0"' +
                         'style="border: solid 4px #37474F"' +
                         'allowfullscreen="allowfullscreen"' +
                         '></iframe>' +
                         '</div>' +
                         '</div>');
                         $('.overlay').on('click', function () {
                             $(this).children()[0].src += '&autoplay=1';
                             $(this).addClass('video-box');
                             $(this).removeClass('overlay');
                             $(this).parent().find('.add-view').trigger('click');
                         });
                 }

                 Drupal.attachBehaviors();
             });
         }
     };

 })(jQuery, Drupal);