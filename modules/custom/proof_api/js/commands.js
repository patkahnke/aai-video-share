 (function ($, Drupal) {

     'use strict';

     Drupal.behaviors.proofAPI = {
         attach: function (context, settings) {
             $('#video-container').once('proofAPIModifyDom').each(function () {
                 var videos = settings.videoArray;
                 var redirect = settings.redirectTo;
                 console.log('response:', videos);
                 console.log('redirect:', redirect);

                 for (var i = 0; i < videos.length; i++) {
                     var viewTally = videos[i].attributes.view_tally;
                     var voteTally = videos[i].attributes.vote_tally;
                     var videoID = videos[i].id;
                     $('#video-container').append(
                         '<table class="votes-views">' +
                         '<td>Views: ' + viewTally + '</td>' +
                         '<td class="votes">Votes: ' + voteTally + '</td>' +
                         '<td><a class="vote-up use-ajax" href="' + '">Vote Up</a><td>' +
                         '<td><a class= "vote-down use-ajax" href="http://aai-video-share.dd:8083/vote_down/ajax/' + videoID + '/' + voteTally + '">Vote Down</a><td>' +
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

     Drupal.AjaxCommands.prototype.voteUp = function(ajax, response, status) {
         var voteTally = response.voteTally;
         $('.votes').append('Votes: ' + voteTally);
         console.log('voteUp is firing');
     }

 })(jQuery, Drupal);