Proof API
=====================
Proof API module contains classes for consuming the Proof API and displaying the results:

 - Proof API Requests: A service for performing CRUD requests on the Proof API
 - Proof API Controller: A class responsible for making requests via the Proof API Requests service
    and rendering the results
 - Proof API Utilities: A service with useful helper functions
 - New Video Form: A class for taking in new video data from the user, validating the data by several criteria,
    and posting it through the Proof API Requests service
 - View/Vote Commands: Classes that define AJAX callback commands for updating the view and vote counts on a page.

KNOWN ISSUES (IN PROCESS):
======================
Link to add a new video is broken, since ajax was added. It works to return a modal error response "Can't post video on
weekends", but the good response to redirect to the new video form no longer works. Must be an issue with ajax responses and redirects.

Author/Maintainer
======================
- Pat Kahnke patkahnke@gmail.com


README Created On:
======================
September 7, 2016

README Updated On:
======================
September 19, 2016
