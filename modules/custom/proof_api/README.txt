Proof API
=====================
Proof API module contains classes for consuming the Proof API and displaying the results:

 - Proof API Controller: A class responsible for organizing and rendering all requests within the Proof API module
 - Proof API Requests: A service for performing CRUD requests on the Proof API
 - Proof API Utilities: A service containing pre-render functions
 - New Video Form: A class for taking in new video data from the user, validating the data by several criteria,
    and posting it through the Proof API Requests service
 - View/Vote Commands: Classes that define AJAX callback commands for updating the view and vote counts on a page.
 - NowPlayingBlock, TopTenViewsBlock, and TopTenVotesBlock display lists of videos that can be added to various pages
 - Proof API module uses a custom theme, "Videos," which extends the Bootstrap theme

KNOWN ISSUES (IN PROCESS):
======================
 - It would be preferable to refactor the path to the New Video Form such that the "No Posting on Weekends" error message is
    displayed without leaving the current page, rather than after the user goes to the form and inputs video data.
    Research needed on why the AJAX callback function to do so is not working.
 - It would be preferable if the videos that are played by clicking a link in one of the Video List blocks would play
    automatically in the Now Playing window, rather than requiring another mouse click. Currently, the video is being
    counted as "played" as soon as the link is clicked, which is the desired result, and the overlay is removed from
    the video when it is displayed, but a second click is still required to actually play the video. The addition of
    "&autoplay=1" to the embed url is not producing the desired result.

Author/Maintainer
======================
- Pat Kahnke (patkahnke@gmail.com)

README Created On:
======================
September 7, 2016

README Updated On:
======================
September 22, 2016
