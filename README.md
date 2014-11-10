canvas-ara-custom-report-php
============================

Custom Academic Related Activities Weekly Email Report for Canvas

This is a simple script that will allow you to send a weekly email of participations for users in online courses.

Here's the Canvas definition of participations:
http://guides.instructure.com/m/4152/l/66793-what-will-analytics-tell-me-about-my-student

To use this, you'll need to generate a CSV from your SIS and place that file in a place that the PHP server running this script can access. We house ours in a local Samba share. Then, it's just a matter of the following configs.

- Set your Canvas token in functions.php
- Set your Samba share and file name in ara_weekly_email.php
- Set the email addresses in ara_weekly_email.php

Then, make any other tweaks to fit your organization, and you're good to go.
