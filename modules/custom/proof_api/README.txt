Proof API
=====================
Proof API module contains classes for consuming the Proof API and displaying the results:

 - Proof API Requests: A service for performing CRUD requests on the Proof API
 - Proof API Controller: A class responsible for making requests via the Proof API Requests service,
    and rendering the results
 - Proof API Utilities: A service with useful helper functions
 - New Video Form: A class for taking in new video data from the user, validating the data by several criteria,
    and posting it through the Proof API Requests service
 - BuildIFramesCommand: IN PROCESS! A class that will construct an object to link each video IFrame to the Youtube API,
    using jQuery

KNOWN ISSUES:
======================
Documentation: Coming
Limiting votes to one per video per day per employee: Coming
    - In order to maintain persistence of a record of the vote, a table will be created in the Drupal database
        and accessed on each vote
Counting views: The function to count views has to be triggered when an embedded video is played: Coming
    - Most likely fix is to attach an event listener to the IFrame Player and subscribe to onPlayerStateChange. IN PROCESS!
Deployment: Coming
Styling: Coming
UX Overhaul: Coming
Better page refreshes needed: For instance, when voting for a video, only the "vote tally" should be refreshed on the DOM
    - This will be part of the jQuery functionality once its added
Better error responses needed in a couple cases, such as not creating a video on a weekend.
    - Most likely approach will be to use a modal so the user can stay on the same page.

Author/Maintainer
======================
- Pat Kahnke patkahnke@gmail.com


README Created On:
======================
September 7, 2016
