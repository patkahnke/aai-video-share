 (function ($, Drupal) {

     'use strict';

     Drupal.AjaxCommands.prototype.vote = function(ajax, response, status) {
         var voteTally = response.voteTally;
         var voteID = response.voteID;
         $('.' + voteID + '').text('Votes: ' + voteTally);
         console.log('vote is firing');
         console.log('voteID:', voteID);
     };

     Drupal.behaviors.proofAPI = {
         attach: function (context, settings) {
             $('#video-container').once('proofAPIModifyDom').each(function () {
                 var videos = settings.videoArray;
                 console.log('response:', videos);

                 for (var i = 0; i < videos.length; i++) {
                     var viewTally = videos[i].attributes.view_tally;
                     var voteTally = videos[i].attributes.vote_tally;
                     var videoID = videos[i].id;
                     var voteID = 'vote' + i;
                     var viewID = 'view' + i;
                     $('#video-container').append(
                         '<table>' +
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
                         '<div>');
                     $('.overlay').on('click', function (ev) {
                         $(this).children()[0].src += '&autoplay=1';
                         $(this).addClass('video-box');
                         $(this).removeClass('overlay');
                         ev.preventDefault();
                     });
                 }

                 Drupal.attachBehaviors();
             });
         }
     };

 })(jQuery, Drupal);