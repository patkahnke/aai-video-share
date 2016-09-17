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
Front page top ten links still need to be hooked up to play internally and count the views.
Deployment: Coming

Author/Maintainer
======================
- Pat Kahnke patkahnke@gmail.com


README Created On:
======================
September 7, 2016

README Updated On:
======================
September 16, 2016
