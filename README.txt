
Video Share
==================
Video Share enables authenticated users to share videos with one another, vote on the videos,
and view the videos in lists that are ordered along various parameters. Written for Drupal 8, it contains:

 - A custom module, "Proof API," that consumes the Proof API for CRUD commands and displays the videos.
 - A custom theme, "Videos."

Please see the README.txt file in the Proof API module for a detailed description of Proof API functionality.

Video Share Features
======================
Authentication:
 - Because the app is designed for the employees of a specific company, all users must be pre-registered
    by a site administrator
 - To access the development version of this site, go to:
    - http://videoshare1gryybmkx6r.devcloud.acquia-sites.com/
    - username: johnsmith
    - password: Iamjohnsmith13

All pages include:
 - Three lists of links:
    - Most Recent Videos
    - Highest Voted Videos
    - Most Viewed Videos
 - Menu links
    - Home Page
    - Three pages with embedded videos corresponding to the video link lists
    - Add a Video
    - Log In/Log Out
 - Information above embedded videos regarding the number of views and votes
 - The ability to vote on embedded videos

Home Page also includes:
 - An embedded player pre-loaded with the most recently added video
 - Clicked links play in this player, allowing user to stay on this page for much of the experience

Add Video Form returns user-friendly error responses if validation shows the following are not true:
 - A title, url, and slug are entered
 - The url is in a valid format, and originates from YouTube or Vimeo
 - The video is being posted on a weekday
 - This is the first time the video has been posted to the site
 - The slug is in the proper format

Caching:
 - The three pages with lists of embedded videos are cached for a maximum of five minutes, otherwise pages are
    not cached, in order to provide the most up-to-date information available.

Voting:
 - Each user is allowed to vote once per day per video
 - A user-friendly error message is returned if multiple attempts are made

Smartphone Compatible:
 - All elements are responsive and able to be viewed on a smartphone
 - All interactions (voting, adding videos, logging in and out) are possible on a smartphone

Author/Maintainer
======================
Pat Kahnke (patkahnke@gmail.com)

ReadMe Created On
=======================
September 7, 2016

ReadMe Updated On
=======================
September 22, 2016