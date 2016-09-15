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
Need to limit votes to one per video per day per employee: Coming
    - In order to maintain persistence of a record of the vote, a table will be created in the Drupal database
        and accessed on each vote
Deployment: Coming
Styling: Coming
UX Overhaul: Coming
Better error responses needed in a couple cases, such as when a user tries to create a video on a weekend.
    - Most likely approach will be to use a modal so the user can stay on the same page.

Author/Maintainer
======================
- Pat Kahnke patkahnke@gmail.com


README Created On:
======================
September 7, 2016

README Updated On:
======================
September 15, 2016
