 (function ($, Drupal) {

     'use strict';

     /*@todo Figure out why this method of using a callback function to redirect to the new video form isn't working. Would prefer
      this method rather than have the user go all the way to the form before finding out it doesn't work on weekends.
      Attaches a custom AJAX callback command, "newVideoForm," to the AjaxCommands object, which gets called
      by the "newVideo" controller function. Triggers a link to the new video form.
      */
     /*Drupal.AjaxCommands.prototype.newVideoForm = function(ajax, response, status) {
         $(this).closest('#video--container').find('.new-video-form').trigger('click');
     };*/

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
                     var overlay = videos[i].attributes.overlay;
                         console.log(videos[i].attributes.embedURL);

                     $('#video-container').append(
                         '<div class="individual-container">' +
                         '<table>' +

                         //video stats info and voting buttons
                         '<a class="add-view use-ajax" href="http://aai-video-share.dd:8083/new_view/ajax/' + videoID + '/' + viewID + '"></a>' +
                         '<td class="votes-views ' + viewID + '">Views: ' + viewTally + '</td>' +
                         '<td class="votes-views ' + voteID + '">Votes: ' + voteTally + '</td>' +
                         '<td class="votes-views"><a class="vote-up-button use-ajax" href="http://aai-video-share.dd:8083/vote_up/ajax/'
                            + videoID + '/' + voteID + '">Vote Up</a></td>' +
                         '<td class="votes-views"><a class="vote-down-button use-ajax" href="http://aai-video-share.dd:8083/vote_down/ajax/'
                            + videoID + '/' + voteID + '">Vote Down</a><td>' +
                         // Saving this link for reference to the failed newVideoForm callback. '<td class="votes-views"><a class="add-video-button use-ajax" href="http://aai-video-share.dd:8083/new_video/ajax/">Add a Video!</a>' +
                         '</table>' +

                         //the overlay variable allows for the page to be built without an overlay when sidebar video links are clicked
                         '<div class="' + overlay + '">' +

                         //video wrapper is part of a css solution to make the iFrame responsive
                         '<div class="video-wrapper">' +
                         '<iframe id="player"' +
                          'width="640" height="360"' +
                         'src="https://' + videos[i].attributes.embedURL +
                         '></iframe>' +
                         '</div>' +
                             '</div' +
                            '</div>');
                     /*
                      "Overlay" is a solution for counting views on embedded videos. A transparent overlay is placed on the iFrame.
                      On a one-time click event on the overlay:
                      - the "newView" function is called to update the view count
                      - an autoplay script is appended to the embed url, which triggers playback, and
                      - the overlay class is removed so the user can interact with the iFrame directly at that point.
                      - the video-box class replaces the overlay class to provide a slightly larger viewing window
                      */
                         $('.overlay').one('click', function () {
                             $(this).children().children()[0].src += '&autoplay=1';
                             $(this).removeClass('overlay');
                             $(this).addClass('video-box');
                             $(this).parent().find('.add-view').trigger('click');
                         });
                 }

                 Drupal.attachBehaviors();
             });
         }
     };

 })(jQuery, Drupal);