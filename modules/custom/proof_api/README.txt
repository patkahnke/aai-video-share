Proof API
=====================
Proof API module contains classes for consuming the Proof API and displaying the results:

 - Proof API Requests: A service for performing CRUD requests on the Proof API
 - Proof API Controller: A class responsible for making requests via the Proof API Requests service, and rendering the results
 - Proof API Utilities: A class with useful helper functions
 - New Video Form: A class for taking in new video data from the user, validating the data by several criteria, and posting it through the Proof API Requests service
 - BuildIFramesCommand: IN PROCESS! A class that will construct an object link each video IFrame to the Youtube API, using jQuery

KNOWN ISSUES:
======================
Documentation: Coming
Limiting votes to one per video per day per employee: Coming
    - In order to maintain persistence of a record of the vote, a table will be created in the Drupal database and accessed on each vote
Counting views: The function to count views has to be triggered when an embedded video is played: Coming
    - Most likely fix is to attach an event listener to the IFrame Player and subscribe to onPlayerStateChange. IN PROCESS!
Deployment: Coming
Styling: Coming

Author/Maintainer
======================
- Pat Kahnke patkahnke@gmail.com


README Created On:
======================
September 7, 2016
